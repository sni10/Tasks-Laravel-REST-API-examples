<?php

namespace Tests;

use App\Models\Comment;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $team;
    protected $task;
    protected $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password123')
        ]);

        $this->token = $this->user->createToken('auth_token')->plainTextToken;

        $this->team = Team::create([
            'name' => 'Initial Team',
        ]);

        $this->team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $this->task = Task::create([
            'title' => 'Initial Task',
            'description' => 'Initial description',
            'status' => 'pending',
            'user_id' => $this->user->id,
        ]);

        $this->comment = Comment::create([
            'content' => 'Initial comment',
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);
    }
}
