<?php

namespace App\Services;

use App\Contracts\TeamRepositoryInterface;
use App\Contracts\TeamServiceInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Team;

class TeamService implements TeamServiceInterface
{
    protected TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function createTeam(array $data): ?Team
    {
        return $this->teamRepository->create($data);
    }

    public function updateTeam($teamId, array $data): ?Team
    {
        $team = $this->teamRepository->findById($teamId);
        return $this->teamRepository->update($team, $data);
    }

    public function deleteTeam($teamId): ?bool
    {
        $team = $this->teamRepository->findById($teamId);
        return $this->teamRepository->delete($team);
    }

    public function getAllTeam(): Collection
    {
        return $this->teamRepository->all();
    }

    public function getTeamById($teamId): ?Team
    {
        return $this->teamRepository->findById($teamId);
    }

    public function addUserToTeam(Team $team, $userId): BelongsToMany
    {
        return $this->teamRepository->addUserToTeam($team, $userId);
    }

    public function removeUserFromTeam(Team $team, $userId): BelongsToMany
    {
        return $this->teamRepository->removeUserFromTeam($team, $userId);
    }
}
