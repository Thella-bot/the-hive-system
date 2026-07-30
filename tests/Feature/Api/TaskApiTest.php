<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Hive\HiveTestCase;

class TaskApiTest extends HiveTestCase
{
    use RefreshDatabase;

    public function test_get_tasks_requires_authentication(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertUnauthorized();
    }

    public function test_get_tasks_returns_empty_array_when_no_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_get_tasks_returns_users_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        \App\Models\StudentTask::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_get_tasks_does_not_return_other_users_tasks(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        \App\Models\StudentTask::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_create_task_requires_authentication(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'New task',
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_task_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => '',
        ]);

        $response->assertUnprocessable();
    }

    public function test_create_task_creates_task(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'API Test Task',
            'due_date' => '2026-12-31',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('student_tasks', [
            'user_id' => $user->id,
            'title' => 'API Test Task',
            'completed' => false,
        ]);
    }

    public function test_update_task_requires_authentication(): void
    {
        $task = \App\Models\StudentTask::factory()->create();

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'completed' => true,
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_task_returns_403_for_wrong_user(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        $task = \App\Models\StudentTask::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'completed' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_update_task_marks_completed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $task = \App\Models\StudentTask::factory()->create([
            'user_id' => $user->id,
            'completed' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'completed' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('student_tasks', [
            'id' => $task->id,
            'completed' => true,
        ]);
    }

    public function test_delete_task_requires_authentication(): void
    {
        $task = \App\Models\StudentTask::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertUnauthorized();
    }

    public function test_delete_task_returns_403_for_wrong_user(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');
        $task = \App\Models\StudentTask::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_delete_task_removes_task(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $task = \App\Models\StudentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('student_tasks', ['id' => $task->id]);
    }
}