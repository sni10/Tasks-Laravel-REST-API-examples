<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class TeamTest extends TestCase
{
    public function testTeamCreationAndRelations()
    {
        // Создаем команду и сохраняем ее в базе данных
        $team = Team::create([
            'name' => 'Team Alpha'
        ]);

        // Прикрепляем пользователя к команде с указанием значения для поля created_by
        $team->users()->attach($this->user->id, ['created_by' => $this->user->id]);

        // Проверяем, что команда была создана правильно
        $this->assertEquals('Team Alpha', $team->name);

        // Проверяем, что пользователь связан с командой
        $this->assertTrue($team->users->contains($this->user));
    }
}
