<?php

use App\Contracts\Interface\AWSChimeInterface;
use App\Enum\ProjectStatus;
use App\Enum\UserRolesEnum;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('list of projects gotten successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.list', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])
                ->etc()
        );
});

test('specific project gotten successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.specific', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])
                ->etc()
        );
});

test('specific project not found', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');
    $invalidProjectId = '00000000-0000-0000-0000-000000000000';
    $response = $this->get(route('projects.specific', ['tenant' => $tenant->id, 'project' => $invalidProjectId]));
    $response->assertStatus(404)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])
                ->etc()
        );
});

test('project created successfully', function () {

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'role' => UserRolesEnum::TENANT_ADMIN->value,
            'subscription_plan' => 'pro',
        ]
    );

    $this->actingAs($user, 'sanctum');

    $projectData = [
        'name' => 'New Project',
        'description' => 'This is a new project',
        'deadline' => fake()->date(),
        'status' => ProjectStatus::IN_PROGRESS->value,
    ];

    $response = $this->post(route('projects.create', ['tenant' => $tenant->id]), $projectData);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'status' => 'string',
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->etc()
        );

    $this->assertDatabaseHas('projects', [
        'name' => 'New Project',
        'description' => 'This is a new project',
        'tenant_id' => $tenant->id,
    ]);
});

test('project creation failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );
    $this->actingAs($user, 'sanctum');

    $projectData = [
        'name' => fake()->sentence(10),
        'description' => fake()->paragraph(3),
        'deadline' => fake()->date(),
        'status' => ProjectStatus::IN_PROGRESS->value,
    ];

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->post(route('projects.create', ['tenant' => $tenant->id, 'project' => $project->id]), $projectData);

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('project creation failed due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'role' => UserRolesEnum::TENANT_ADMIN->value,
        ]
    );

    $projectData = [
        'name' => fake()->sentence(10),
        'description' => fake()->paragraph(3),
        'deadline' => fake()->date(),
        'status' => ProjectStatus::IN_PROGRESS->value,
    ];

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->post(route('projects.create', ['tenant' => $tenant->id, 'project' => $project->id]), $projectData);

    $response->assertStatus(401)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('project update successful', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::PROJECT_MANAGER->value,
        'subscription_plan' => 'pro',
    ]);
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.update', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'name' => 'Updated Project Name',
        'description' => 'Updated description',
        'deadline' => fake()->date(),
        'status' => ProjectStatus::COMPLETED->value,
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])
                ->where('data.project.tenant_id', $tenant->id)->etc()
        );

    $this->assertDatabaseHas('projects', [
        'name' => 'Updated Project Name',
        'description' => 'Updated description',
    ]);
});

test('project update failed due to unauthorized user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route(
        'projects.update',
        ['tenant' => $tenant->id, 'project' => $project->id],
        [
            'name' => 'Update Project Name 2',
        ]
    ));

    $response->assertStatus(403)
        ->assertJson(fn (AssertableJson $json) => $json->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    $this->assertDatabaseMissing('projects', [
        'name' => 'Update Project Name 2',
    ]);
});

test('project update failed due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::PROJECT_MANAGER->value,
    ]);
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->post(route(
        'projects.update',
        ['tenant' => $tenant->id, 'project' => $project->id],
        [
            'name' => 'Update Project Name 3',
        ]
    ));

    $response->assertStatus(401)
        ->assertJson(fn (AssertableJson $json) => $json->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    $this->assertDatabaseMissing('projects', [
        'name' => 'Update Project Name 3',
    ]);
});

test('project status list gotten successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );
    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.statusList', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])
                ->etc()
        );
});

test('project deleted successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'role' => UserRolesEnum::TENANT_ADMIN->value,
            'subscription_plan' => 'pro',
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('projects.delete', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'status' => 'string',
                ])
                ->etc()
        );

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('delete project failed due to unauthorized user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('projects.delete', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
    ]);
});

test('add user to project successfully', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'role' => UserRolesEnum::TENANT_ADMIN->value,
            'subscription_plan' => 'pro',
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $newUser = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('projects.addUser', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $newUser->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'status' => 'string',
                ])
                ->etc()
        );

    $this->assertDatabaseHas('users', [
        'project_id' => $project->id,
    ]);
});

test('add user failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $newUser = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.addUser', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $newUser->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );

    $this->assertDatabaseMissing('users', [
        'project_id' => $project->id,
    ]);
});

test('successfully removed user', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $userToRemove = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('projects.removeUser', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $userToRemove->id]));
    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'status' => 'string',
                ])->etc()
        );

    $this->assertDatabaseMissing('users', [
        'project_id' => $project->id,
    ]);
});

test('failed to remove user due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $userToRemove = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.removeUser', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $userToRemove->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );

    $this->assertDatabaseHas('users', [
        'project_id' => $project->id,
    ]);
});

test('assign role to user successful', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'role' => UserRolesEnum::TENANT_ADMIN->value,
            'subscription_plan' => 'pro',
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $userToAssignRole = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
        ]
    );

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('projects.assignRole', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $userToAssignRole->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'status' => 'string',
                ])
                ->etc()
        );

    $this->assertDatabaseHas('users', [
        'id' => $userToAssignRole->id,
        'role' => UserRolesEnum::PROJECT_MANAGER->value,
    ]);
});

test('assign role to user failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $project = Project::factory()->create(
        [
            'tenant_id' => $tenant->id,
        ]
    );

    $userToAssignRole = User::factory()->create(
        [
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.assignRole', ['tenant' => $tenant->id, 'project' => $project->id, 'user' => $userToAssignRole->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );

    $this->assertDatabaseMissing('users', [
        'id' => $userToAssignRole->id,
        'role' => UserRolesEnum::PROJECT_MANAGER->value,
    ]);
});

test('successfully created a meeting for the project', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
        'project_id' => $project->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);
    $mockAwsInterface->shouldReceive('createMeeting')
        ->once()
        ->andReturn(response()->json([
            'message' => 'Meeting created successfully',
            'data' => ['meeting' => ['MeetingId' => 'test-meeting-id'], 'attendee' => ['AttendeeId' => 'test-attendee-id']],
            'status' => 'success',
        ]));

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('projects.createMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));
    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])->etc()
        );
});

test('failed to create meeting due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);
    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.createMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('user joined meeting successfully', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldReceive('createAttendee')
        ->once()
        ->andReturn(response()->json([
            'message' => 'Attendee created successfully',
            'data' => ['AttendeeId' => 'test-attendee-id'],
            'status' => 'success',
        ]));

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.joinMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])->etc()
        );
});

test('failed to join meeting due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.joinMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('successfully got meeting details', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldReceive('getMeeting')
        ->once()
        ->andReturn(response()->json([
            'message' => 'Meeting retrieved successfully',
            'data' => ['MeetingId' => 'test-meeting-id'],
            'status' => 'success',
        ]));

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.getMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])->etc()
        );
});

test('failed to get meeting details due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldNotHaveReceived('getMeeting');
    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);
    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.getMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('successfully gotten list of attendees', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldReceive('listAttendees')
        ->once()
        ->andReturn(response()->json([
            'message' => 'Attendees retrieved successfully',
            'data' => [['AttendeeId' => 'test-attendee-id-1'], ['AttendeeId' => 'test-attendee-id-2']],
            'status' => 'success',
        ]));

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('projects.listAttendees', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])->etc()
        );
});

test('failed to get attendees due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldNotHaveReceived('listAttendees');

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($user, 'sanctum');
    $response = $this->get(route('projects.listAttendees', ['tenant' => $tenant->id, 'project' => $project->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType([
                    'message' => 'string',
                ])->etc()
        );
});

test('delete meeting successful', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $mockAwsInterface = Mockery::mock(AWSChimeInterface::class);

    $mockAwsInterface->shouldReceive('deleteMeeting')
        ->once()
        ->andReturn(response()->json([
            'message' => 'Meeting deleted successfully',
            'data' => ['MeetingId' => 'test-meeting-id', 'delete_data' => []],
            'status' => 'success',
        ]));

    $this->app->instance(AWSChimeInterface::class, $mockAwsInterface);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('projects.deleteMeeting', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])->etc()
        );
});

test('update project status successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::PROJECT_MANAGER->value,
        'subscription_plan' => 'pro',
    ]);
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.updateStatus', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'status' => ProjectStatus::COMPLETED->value,
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'data', 'status')
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                    'status' => 'string',
                ])
                ->where('data.project.tenant_id', $tenant->id)->etc()
        );

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::COMPLETED->value,
    ]);
});

test('project status update failed due to unauthorized user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route(
        'projects.updateStatus',
        ['tenant' => $tenant->id, 'project' => $project->id],
        [
            'status' => ProjectStatus::COMPLETED->value,
        ]
    ));

    $response->assertStatus(403)
        ->assertJson(fn (AssertableJson $json) => $json->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::COMPLETED->value,
    ]);
});
