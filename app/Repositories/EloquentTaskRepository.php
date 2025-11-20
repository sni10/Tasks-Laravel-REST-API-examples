<?php

namespace App\Repositories;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function all(): Collection
    {
        return Task::all();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findById($id): ?Task
    {
        return Task::findOrFail($id);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): ?bool
    {
        return $task->delete();
    }
}
