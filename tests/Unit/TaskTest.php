<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function testTaskCreation()
    {
        $task = new Task([
            'title' => 'Task Title',
            'description' => 'Task Description',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'team_id' => $this->team->id
        ]);

        $this->assertEquals('Task Title', $task->title);
        $this->assertEquals('Task Description', $task->description);
        $this->assertEquals('pending', $task->status);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals($this->team->id, $task->team_id);
    }

    public function testTaskRelations()
    {
        $task = Task::factory()->make(['user_id' => $this->user->id]);
        $this->assertInstanceOf(User::class, $task->user);
    }
}
