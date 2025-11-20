<?php

namespace App\Contracts;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface TeamServiceInterface
{
    public function createTeam(array $data): ?Team;

    public function updateTeam($teamId, array $data): ?Team;

    public function deleteTeam($teamId): ?bool;

    public function getAllTeam(): Collection;

    public function getTeamById($teamId): ?Team;

    public function addUserToTeam(Team $team, $userId): BelongsToMany;

    public function removeUserFromTeam(Team $team, $userId): BelongsToMany;
}
