<?php

use App\Enum\UserRolesEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

test('successfully gotten all tasks', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('tasks.list', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );
});

test('failed to get tasks due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->get(route('tasks.list', ['tenant' => $tenant->id, 'project' => $project->id]));

    $response->assertStatus(401)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('successfully gotten specific task', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('tasks.specific', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );
});

test('failed to get specific task due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->get(route('tasks.specific', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));

    $response->assertStatus(401)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('task created successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $taskData = [
        'name' => 'New Task',
        'description' => 'This is a new task',
        'deadline' => now()->addDays(7)->toDateString(),
    ];

    $response = $this->post(route('tasks.create', ['tenant' => $tenant->id, 'project' => $project->id]), $taskData);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('tasks', $taskData);
});

test('failed to create task due to unauthenticated access', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->post(route('tasks.create', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'name' => 'New Task',
        'description' => 'This is a new task',
        'deadline' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertStatus(401)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );

    $response->assertStatus(401);
});

test('failed to create task due to invalid input', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $taskData = [
        'name' => '',
        'description' => 'This is a new task',
        'deadline' => 'invalid-date',
    ];

    $response = $this->post(route('tasks.create', ['tenant' => $tenant->id, 'project' => $project->id]), $taskData);

    $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'errors')
                ->whereAllType(
                    [
                        'message' => 'string',
                        'errors' => 'array',
                    ]
                )
                ->etc()
        );
});

test('failed to create task due to unauthorized user', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $taskData = [
        'name' => 'New Task',
        'description' => 'This is a new task',
        'deadline' => now()->addDays(7)->toDateString(),
    ];

    $response = $this->post(route('tasks.create', ['tenant' => $tenant->id, 'project' => $project->id]), $taskData);
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('task update successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $updatedData = [
        'name' => 'Updated Task Name',
        'description' => 'Updated description',
        'deadline' => now()->addDays(10)->toDateString(),
    ];

    $response = $this->post(route('tasks.update', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), $updatedData);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('tasks', $updatedData);
});

test('task update failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $updatedData = [
        'name' => 'Updated Task Name',
        'description' => 'Updated description',
        'deadline' => now()->addDays(10)->toDateString(),
    ];

    $response = $this->post(route('tasks.update', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), $updatedData);

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('deleted task successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('tasks.delete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));
    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );
});

test('deleted task failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('tasks.delete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('task successfully marked as complete', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.markComplete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'completed' => true,
    ]);
});

test('mark task as complete failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.markComplete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'completed' => false,
    ]);
});

test('task submitted successfully', function () {
    Storage::fake('public');
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $files = [
        UploadedFile::fake()->create('submission1.pdf', 1024),
        UploadedFile::fake()->create('submission2.docx', 2048),
    ];

    $taskData = [
        'files' => $files,
        'comment' => fake()->sentence(),
    ];

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.submitTask', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), $taskData);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    foreach ($files as $file) {
        Storage::disk('public')->assertExists('task_submissions/'.$file->hashName());
    }

    $this->assertDatabaseHas('task_submissions', [
        'submission_files' => json_encode([
            config('filesystems.disks.public.url').'/'.'task_submissions/'.$files[0]->hashName(),
            config('filesystems.disks.public.url').'/'.'task_submissions/'.$files[1]->hashName(),
        ]),
        'comments' => $taskData['comment'],
        'user_id' => $user->id,
        'task_id' => $task->id,
    ]);
});

test('unable to submit task because file exceeds maximum size', function () {
    Storage::fake('public');
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $files = [
        UploadedFile::fake()->create('large_file.pdf', 15000000),
    ];

    $taskData = [
        'files' => $files,
        'comment' => fake()->sentence(),
    ];

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.submitTask', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), $taskData);

    $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message', 'errors')
                ->whereAllType(
                    [
                        'message' => 'string',
                        'errors' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseMissing('task_submissions', [
        'submission_files' => json_encode([
            config('filesystems.disks.public.url').'/'.'task_submissions/'.$files[0]->hashName(),
        ]),
        'comments' => $taskData['comment'],
        'user_id' => $user->id,
        'task_id' => $task->id,
    ]);
});

test('test view all submissions', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');
    $response = $this->post(route('tasks.viewSubmissions', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));
    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );
});

test('test specific users submission', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');
    $response = $this->post(route('tasks.viewUserSubmissions', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));
    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );
});

test('test download submission file', function () {
    Storage::fake('public');
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $file = 'submission1.pdf';

    Storage::disk('public')->put('task_submissions/'.$file, 'fake content');

    $encoded_files = [config('filesystems.disks.public.url').'/'.'task_submissions/'.$file];

    $submission = TaskSubmission::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'submission_files' => $encoded_files,
    ]);

    $response = $this->get(route('tasks.downloadSubmissionFile', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'submittedTask' => $submission->id]));
    $response->assertStatus(200)
        ->assertDownload();
});

test('download failed due to unauthorized user', function () {
    Storage::fake('public');
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $file = 'submission1.pdf';

    Storage::disk('public')->put('task_submissions/'.$file, 'fake content');

    $encoded_files = [config('filesystems.disks.public.url').'/'.'task_submissions/'.$file];

    $submission = TaskSubmission::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'submission_files' => $encoded_files,
    ]);

    $response = $this->get(route('tasks.downloadSubmissionFile', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'submittedTask' => $submission->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('searched tasks successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $search = 'Task';

    Task::factory()->create([
        'name' => 'Task One',
        'description' => 'First task description',
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.searchTask', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'search' => $search,
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $assertTaskExist = Task::query()->where('project_id', $project->id)->where(function ($q) use ($search) {
        $q->where('name', 'LIKE', '%'.$search.'%')
            ->orWhere('description', 'LIKE', '%'.$search.'%')
            ->get();
    });

    $this->assertGreaterThan(0, $assertTaskExist->count());
});

test('search tasks failed due to invalid search', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    Task::factory()->create([
        'name' => 'Task One',
        'description' => 'First task description',
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $search = 'NonExistingTaskName';

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('projects.searchTask', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'search' => $search,
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $assertTaskExist = Task::query()->where('project_id', $project->id)->where(function ($q) use ($search) {
        $q->where('name', 'LIKE', '%'.$search.'%')
            ->orWhere('description', 'LIKE', '%'.$search.'%')
            ->get();
    });

    $this->assertEquals(0, $assertTaskExist->count());
});

test('search failed due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    Task::factory()->create([
        'name' => 'Task One',
        'description' => 'First task description',
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $search = 'Task';

    $response = $this->post(route('projects.searchTask', ['tenant' => $tenant->id, 'project' => $project->id]), [
        'search' => $search,
    ]);

    $response->assertStatus(401)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );
});

test('successfully assigned user to task', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('tasks.assignTask', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'user' => $user->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('task_user', [
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);
});

test('failed to assign user due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);
    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('tasks.assignTask', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'user' => $user->id]));
    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('message')
                ->whereAllType(
                    [
                        'message' => 'string',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseMissing('task_user', [
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);
});

test('searched user successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.searchUser', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), [
        'name' => $user->name,
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
    ]);
});

test('search failed due to invalid user', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tasks.searchUser', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), [
        'name' => 'NonExistingUserName',
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'name' => 'NonExistingUserName',
    ]);
});

test('successfully removed user from task', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->for($tenant)->create([
        'tenant_id' => $tenant->id,
    ]);

    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'subscription_plan' => 'pro',
        'role' => UserRolesEnum::TENANT_ADMIN->value,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'tenant_id' => $tenant->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $task->user()->attach($user->id);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('tasks.removeUser', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'user' => $user->id]));

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json->hasAll('status', 'data')
                ->whereAllType(
                    [
                        'status' => 'string',
                        'data' => 'array',
                    ]
                )
                ->etc()
        );

    $this->assertDatabaseMissing('task_user', [
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);
});

test('successfully gotten users assigned to task', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('tasks.getUsersAssignedToTask', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'user' => $user->id]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('status', 'data')
            ->whereAllType([
                'status' => 'string',
                'data' => 'array',
            ])->etc());
});
