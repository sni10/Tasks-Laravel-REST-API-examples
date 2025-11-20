<?php

namespace Tests\Unit;

use App\Models\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    public function testTeamCreation()
    {
        $team = new Team([
            'name' => 'Team Alpha'
        ]);

        $this->assertEquals('Team Alpha', $team->name);
    }

    public function testTeamNameCanBeChanged()
    {
        $team = new Team(['name' => 'Old Name']);
        $team->name = 'New Name';

        $this->assertEquals('New Name', $team->name);
    }
}
