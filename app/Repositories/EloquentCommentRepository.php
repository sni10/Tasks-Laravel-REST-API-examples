<?php

namespace App\Repositories;

use App\Contracts\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function all(): Collection
    {
        return Comment::all();
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function findById($id): Comment
    {
        return Comment::findOrFail($id);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment;
    }

    public function delete(Comment $comment): ?bool
    {
        return $comment->delete();
    }
}
