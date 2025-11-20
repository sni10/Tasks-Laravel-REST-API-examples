<?php

namespace Tests\Feature;

use App\Models\Team;
use Tests\TestCase;

class TeamTest extends TestCase
{
    public function testStoreTeam()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', '/api/v1/teams', [
                'name' => 'New add Team'
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name']);
    }

    public function testUpdateTeam()
    {
        $team = Team::create(['name' => 'Team to Update']);
        $team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('PUT', '/api/v1/teams/' . $team->id, [
                'name' => 'Updated Team Name'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['name' => 'Updated Team Name']);
    }

    public function testIndexTeams()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', '/api/v1/teams');

        $response->assertStatus(200);
        $response->assertJsonStructure([['id', 'name']]);
    }

    public function testAddUserToTeam()
    {
        $team = Team::create(['name' => 'Team for Adding User']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', '/api/v1/teams/' . $team->id . '/users', [
                'user_id' => $this->user->id
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'users']);
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $this->user->id]);
    }

    public function testRemoveUserFromTeam()
    {
        $team = Team::create(['name' => 'Team for Removing User']);
        $team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('DELETE', '/api/v1/teams/' . $team->id . '/users/' . $this->user->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $this->user->id]);
    }

    public function testShowTeam()
    {
        $team = Team::create(['name' => 'Team to Show']);
        $team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', '/api/v1/teams/' . $team->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name']);
    }

    public function testDeleteTeam()
    {
        $team = Team::create(['name' => 'Team to Delete']);
        $team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('DELETE', '/api/v1/teams/' . $team->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }
}
