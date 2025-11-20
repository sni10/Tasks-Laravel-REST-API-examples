<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class CommentTest extends TestCase
{

    public function testCommentCreation()
    {

        $comment = new Comment([
            'content' => 'This is a comment',
            'task_id' => $this->task->id,
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('This is a comment', $comment->content);
        $this->assertEquals($this->task->id, $comment->task_id);
        $this->assertEquals($this->user->id, $comment->user_id);
    }

    public function testCommentRelations()
    {

        $comment = Comment::factory()->make([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(Task::class, $comment->task);
        $this->assertInstanceOf(User::class, $comment->user);
    }
}
