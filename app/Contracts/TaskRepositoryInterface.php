<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): ?Task;
    public function findById($id): ?Task;
    public function update(Task $task, array $data): ?Task;
    public function delete(Task $task): ?bool;
}
