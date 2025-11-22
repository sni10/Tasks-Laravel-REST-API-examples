<?php

namespace Tests\Unit;

use App\Models\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskCreation()
    {
        $task = new Task([
            'title' => 'Task Title',
            'description' => 'Task Description',
            'status' => 'pending',
            'user_id' => 1,
            'team_id' => 2
        ]);

        $this->assertEquals('Task Title', $task->title);
        $this->assertEquals('Task Description', $task->description);
        $this->assertEquals('pending', $task->status);
        $this->assertEquals(1, $task->user_id);
        $this->assertEquals(2, $task->team_id);
    }

    public function testTaskAttributeDefaults()
    {
        $task = new Task(['title' => 'Test']);

        $this->assertEquals('Test', $task->title);
        $this->assertNull($task->description);
    }
}
