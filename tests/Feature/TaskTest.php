<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function testStoreTask()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', '/api/v1/tasks', [
            'title' => 'New additional Task',
            'description' => 'Details of new task',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'description', 'status', 'user_id']);
    }

    public function testStoreAndUpdateTask()
    {
        $task = Task::create([
            'title' => 'Task to Update',
            'description' => 'Original description',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('PUT', '/api/v1/tasks/' . $task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated details',
            'status' => 'completed',
            'user_id' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'Updated Title',
            'description' => 'Updated details',
            'status' => 'completed',
            'user_id' => $this->user->id,
            'team_id' => $this->team->id
        ]);
    }

    public function testShowTask()
    {
        $task = Task::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', '/api/v1/tasks/' . $task->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'title', 'description', 'status', 'user_id']);
    }

    public function testIndexTasks()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', '/api/v1/tasks');

        $response->assertStatus(200);
        $response->assertJsonStructure([['id', 'title', 'description', 'status', 'user_id']]);
    }

    public function testDeleteTask()
    {
        $task = Task::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('DELETE', '/api/v1/tasks/' . $task->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }


}
