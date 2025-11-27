<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_create_task()
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $resp = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret']);
        $resp->assertStatus(200)->assertJsonStructure(['token']);

        $token = $resp->json('token');

        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/tasks', ['title' => 'Test Task', 'description' => 'desc', 'priority' => 'Medium']);

        $create->assertStatus(201)->assertJsonFragment(['title' => 'Test Task']);
    }

    public function test_update_only_when_draft()
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $token = $user->createToken('test')->plainTextToken;

        $task = Task::create(['user_id' => $user->id, 'title' => 'T', 'description' => '', 'status' => Task::STATUS_DRAFT]);

        // successful update while draft
        $resp = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/tasks/'.$task->id, ['title' => 'Updated']);
        $resp->assertStatus(200)->assertJsonFragment(['title' => 'Updated']);

        // change to in_process using action payload
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/tasks/'.$task->id.'/in-process')
            ->assertStatus(200)->assertJsonFragment(['status' => Task::STATUS_IN_PROCESS]);

        // now update should be rejected (unprocessable)
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/tasks/'.$task->id, ['title' => 'Will Fail'])
            ->assertStatus(422);
    }

    public function test_status_transitions()
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $token = $user->createToken('test2')->plainTextToken;

        $task = Task::create(['user_id' => $user->id, 'title' => 'T2', 'description' => '', 'status' => Task::STATUS_DRAFT]);

        // draft -> in_process
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/tasks/'.$task->id.'/in-process')
            ->assertStatus(200)->assertJsonFragment(['status' => Task::STATUS_IN_PROCESS]);

        // in_process -> completed
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/tasks/'.$task->id.'/complete')
            ->assertStatus(200)->assertJsonFragment(['status' => Task::STATUS_COMPLETED]);

        // completed -> draft not supported by controller; expect 422 if tried
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/tasks/'.$task->id.'/status', ['action' => 'reset'])
            ->assertStatus(400);
    }
}
