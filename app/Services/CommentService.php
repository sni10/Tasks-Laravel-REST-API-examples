<?php

namespace App\Services;

use App\Contracts\CommentRepositoryInterface;
use App\Contracts\CommentServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Comment;

class CommentService implements CommentServiceInterface
{
    protected CommentRepositoryInterface $commentRepository;

    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function createComment(array $data): ?Comment
    {
        return $this->commentRepository->create($data);
    }

    public function updateComment($commentId, array $data): ?Comment
    {
        $comment = $this->commentRepository->findById($commentId);
        return $this->commentRepository->update($comment, $data);
    }

    public function deleteComment($commentId): ?bool
    {
        $comment = $this->commentRepository->findById($commentId);
        return $this->commentRepository->delete($comment);
    }

    public function getAllComment(): Collection
    {
        return $this->commentRepository->all();
    }

    public function getCommentById($commentId): ?Comment
    {
        return $this->commentRepository->findById($commentId);
    }
}
