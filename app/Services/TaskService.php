<?php

namespace App\Services;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;

class TaskService implements TaskServiceInterface
{
    protected TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function createTask(array $data): ?Task
    {
        return $this->taskRepository->create($data);
    }

    public function updateTask($taskId, array $data): ?Task
    {
        $task = $this->taskRepository->findById($taskId);
        return $this->taskRepository->update($task, $data);
    }

    public function deleteTask($taskId): ?bool
    {
        $task = $this->taskRepository->findById($taskId);
        return $this->taskRepository->delete($task);
    }

    public function getAllTasks(): Collection
    {
        return $this->taskRepository->all();
    }

    public function getTaskById($taskId): ?Task
    {
        return $this->taskRepository->findById($taskId);
    }
}
