<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{

    protected TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function index(): JsonResponse
    {
        try {
            $teams = $this->teamService->getAllTeam();
            return response()->json($teams);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['name' => 'required|string|max:255']);
            $team = $this->teamService->createTeam($validated);
            return response()->json($team, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($id);
            return response()->json($team);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($id);

            $validated = $request->validate(['name' => 'required|string|max:255']);

            $updatedTeam = $this->teamService->updateTeam($team->id, $validated);

            return response()->json($updatedTeam);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($id);
            $this->teamService->deleteTeam($team->id);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function addUser(Request $request, $teamId): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($teamId);
            $validated = $request->validate(['user_id' => 'required|integer|exists:users,id']);
            $this->teamService->addUserToTeam($team, $validated['user_id']);

            return response()->json($team->load('users'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function removeUser($teamId, $userId): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($teamId);
            $this->teamService->removeUserFromTeam($team, $userId);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}
