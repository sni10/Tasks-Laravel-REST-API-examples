<?php

namespace App\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentServiceInterface
{
    public function createComment(array $data): ?Comment;

    public function updateComment($commentId, array $data): ?Comment;

    public function deleteComment($commentId): ?bool;

    public function getAllComment(): Collection;

    public function getCommentById($commentId): ?Comment;
}
