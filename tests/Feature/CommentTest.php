<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class CommentTest extends TestCase
{

    public function testStoreComment()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', '/api/v1/tasks/' . $this->task->id . '/comments', [
            'content' => 'New additional comment',
            'task_id' => $this->task->id,
            'user_id' => $this->user->id
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'content', 'task_id', 'user_id']);
    }

    public function testDeleteComment()
    {
        $comment = Comment::create([
            'content' => 'Comment to delete',
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('DELETE', '/api/v1/comments/' . $comment->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
