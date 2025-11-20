<?php

namespace App\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): ?Comment;
    public function findById($id): ?Comment;
    public function update(Comment $comment, array $data): ?Comment;
    public function delete(Comment $comment) : ?bool;
}
