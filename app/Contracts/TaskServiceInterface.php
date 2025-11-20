<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    public function createTask(array $data): ?Task;
    public function updateTask($taskId, array $data): ?Task;
    public function deleteTask($taskId): ?bool;
    public function getAllTasks(): Collection;
    public function getTaskById($taskId): ?Task;
}
