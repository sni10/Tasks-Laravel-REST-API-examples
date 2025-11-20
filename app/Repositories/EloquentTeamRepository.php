<?php

namespace App\Repositories;

use App\Contracts\TeamRepositoryInterface;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function all(): Collection
    {
        return Team::all();
    }

    public function create(array $data): ?Team
    {
        return Team::create($data);
    }

    public function findById($id): ?Team
    {
        return Team::findOrFail($id);
    }

    public function update(Team $team, array $data): ?Team
    {
        $team->update($data);
        return $team;
    }

    public function delete(Team $team): ?bool
    {
        return $team->delete();
    }

    public function addUserToTeam(Team $team, $userId): BelongsToMany
    {
        $creatorId = Auth::id();
        $team->users()->attach($userId, ['created_by' => $creatorId]);
        return $team->users();
    }

    public function removeUserFromTeam(Team $team, $userId): BelongsToMany
    {
        $creatorId = Auth::id();
        $team->users()->detach($userId, ['created_by' => $creatorId]);
        return $team->users();
    }
}
