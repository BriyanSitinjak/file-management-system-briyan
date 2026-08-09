<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    /**
     * @return Collection<int, Department>
     */
    public function list(): Collection
    {
        return Department::query()->orderBy('name')->get();
    }

    /**
     * @param  array{name: string}  $data
     */
    public function store(array $data): Department
    {
        return Department::query()->create($data);
    }

    /**
     * @param  array{name: string}  $data
     */
    public function update(Department $department, array $data): Department
    {
        $department->update($data);

        return $department->fresh();
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}
