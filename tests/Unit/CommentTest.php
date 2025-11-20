<?php

namespace Tests\Unit;

use App\Models\Comment;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testCommentCreation()
    {
        $comment = new Comment([
            'content' => 'This is a comment',
            'task_id' => 1,
            'user_id' => 2
        ]);

        $this->assertEquals('This is a comment', $comment->content);
        $this->assertEquals(1, $comment->task_id);
        $this->assertEquals(2, $comment->user_id);
    }

    public function testCommentContentCanBeEmpty()
    {
        $comment = new Comment([
            'content' => '',
            'task_id' => 1,
            'user_id' => 2
        ]);

        $this->assertEquals('', $comment->content);
    }
}
