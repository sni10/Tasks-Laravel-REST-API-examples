<?php

namespace App\Contracts;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface TeamRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): ?Team;
    public function findById($id): ?Team;
    public function update(Team $team, array $data): ?Team;
    public function delete(Team $team) : ?bool;
    public function addUserToTeam(Team $team, $userId): BelongsToMany;
    public function removeUserFromTeam(Team $team, $userId): BelongsToMany;
}
