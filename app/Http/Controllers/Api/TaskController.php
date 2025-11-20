<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(): JsonResponse
    {
        try {
            $tasks = $this->taskService->getAllTasks();
            if ($tasks === null) {
                return response()->json(['error' => 'Task not found'], 404);
            }
            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }

    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:pending,in progress,completed',
                'user_id' => 'required|integer|exists:users,id',
                'team_id' => 'nullable|integer|exists:teams,id'
            ]);
            $task = $this->taskService->createTask($validated);

            if ($task === null) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if ($task === null) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            return response()->json($task);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if ($task === null) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:pending,in progress,completed',
            ]);

            $updatedTask = $this->taskService->updateTask($id, $validated);

            return response()->json($updatedTask, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }

    }

    public function destroy($id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->taskService->deleteTask($task->id);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}
