<?php

namespace Tests;

use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $user;
    protected $token;
    protected $team;
    protected $task;
    protected $comment;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('comments')->truncate();
        DB::table('password_resets')->truncate();
        DB::table('personal_access_tokens')->truncate();
        DB::table('sessions')->truncate();
        DB::table('tasks')->truncate();
        DB::table('team_user')->truncate();
        DB::table('teams')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->user = User::firstOrCreate([
            'email' => 'test@test.com'
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password123')
        ]);

        $this->token = $this->user->createToken('auth_token')->plainTextToken;

        $this->team = Team::firstOrCreate([
            'name' => 'Initial Team',
        ]);

        $this->team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $this->task = Task::firstOrCreate([
            'title' => 'Initial Task',
            'description' => 'Initial description',
            'status' => 'pending',
            'user_id' => $this->user->id,
        ]);

        $this->comment = Comment::firstOrCreate([
            'content' => 'Initial comment',
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

    }
}
