<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    protected CommentService $commentsService;

    public function __construct(CommentService $commentsService)
    {
        $this->commentsService = $commentsService;
    }


    public function index(): JsonResponse
    {
        try {
            $comments = $this->commentsService->getAllComment();
            return response()->json($comments);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'task_id' => 'required|integer|exists:tasks,id',
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $comment = $this->commentsService->createComment($validated);
            return response()->json($comment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $comment = $this->commentsService->getCommentById($id);
            return response()->json($comment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string',
            ]);
            $comment = $this->commentsService->updateComment($id, $validated);
            return response()->json($comment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $comment = $this->commentsService->getCommentById($id);
            $this->commentsService->deleteComment($comment->id);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}
