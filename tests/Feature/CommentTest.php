<?php

use App\Enum\UserRolesEnum;
use App\Models\Task;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Testing\Fluent\AssertableJson;

test('gotten all comments', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('comments.get', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]));
    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('failed to get all comments due to unauthenticated user', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);

    $response = $this->get(route('comments.get', [
        'tenant' => $tenant->id,
        'project' => $project->id,
        'task' => $task->id
    ]));

    $response->assertStatus(401)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string'
            ]));
});

test('got specific comment successfully', function () {
    $tenant = Tenant::factory()->create();

    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);

    $comment = Comment::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'user_id' => $user->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('comments.getSpecific', [
        'tenant' => $tenant->id,
        'project' => $project->id,
        'task' => $task->id,
        'comment' => $comment->id
    ]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('comment created successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('comments.create', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id]), [
        'comment' => 'new comment'
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));

    $this->assertDatabaseHas('comments', [
        'comment' => 'new comment',
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id
    ]);
});


test('create comment failed due to invalid input', function () {
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
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('comments.create', [
        'tenant' => $tenant->id,
        'project' => $project->id,
        'task' => $task->id
    ]));

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string'
            ])->etc());


    $this->assertDatabaseMissing('comments', [
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id
    ]);
});

test('update comment successful', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $comment = Comment::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'user_id' => $user->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('comments.update', [
        'tenant' => $tenant->id,
        'project' => $project->id,
        'task' => $task->id,
        'comment' => $comment->id
    ]), [
        'comment' => 'updated comment'
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));

    $this->assertDatabaseHas('comments', [
        'comment' => 'updated comment',
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'user_id' => $user->id
    ]);
});


test('deleted comment successfully', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,

    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => 'pro'
    ]);

    $comment = Comment::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'user_id' => $user->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('comments.delete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'comment' => $comment->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));

    $this->assertDatabaseMissing('comments', [
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id
    ]);
});

test('delete comment failed due to unauthorized access', function () {
    $tenant = Tenant::factory()->create();
    $project = Project::factory()->create([
        'tenant_id' => $tenant->id
    ]);
    $task = Task::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
    ]);

    $wrongUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id
    ]);

    $comment = Comment::factory()->create([
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
        'user_id' => $wrongUser
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('comments.delete', ['tenant' => $tenant->id, 'project' => $project->id, 'task' => $task->id, 'comment' => $comment->id]));

    $response->assertStatus(403)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string'
            ])->etc());

    $this->assertDatabaseHas('comments', [
        'tenant_id' => $tenant->id,
        'project_id' => $project->id,
        'task_id' => $task->id
    ]);
});
