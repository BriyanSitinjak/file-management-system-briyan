<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FolderService
{
    /**
     * @param  array{parent_id?: int|null, department_id?: int, q?: string}  $filters
     * @return Collection<int, Folder>
     */
    public function list(array $filters = []): Collection
    {
        return Folder::query()
            ->with(['department', 'user', 'parent'])
            ->when(array_key_exists('parent_id', $filters), function ($query) use ($filters) {
                $filters['parent_id'] === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $filters['parent_id']);
            })
            ->when(isset($filters['department_id']), fn ($query) => $query->where('department_id', $filters['department_id']))
            ->when(
                filled($filters['q'] ?? null),
                fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'),
            )
            ->latest()
            ->get();
    }

    /**
     * @return array{folder: Folder, breadcrumbs: list<array{id: int, name: string}>}
     */
    public function show(Folder $folder): array
    {
        $folder->load([
            'department',
            'user',
            'parent',
            'children.user',
            'files.user',
            'files.department',
        ]);

        return [
            'folder' => $folder,
            'breadcrumbs' => $this->ancestors($folder),
        ];
    }

    /**
     * @param  array{name: string, department_id: int, parent_id?: int|null}  $data
     */
    public function store(array $data, User $user): Folder
    {
        $folder = Folder::query()->create([
            'name' => $data['name'],
            'department_id' => $data['department_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'user_id' => $user->id,
        ]);

        $this->log($user, 'folder.created', $folder, "Created folder {$folder->name}");

        return $folder->load(['department', 'user', 'parent']);
    }

    /**
     * @param  array{name: string}  $data
     */
    public function update(Folder $folder, array $data, User $user): Folder
    {
        $folder->update([
            'name' => $data['name'],
        ]);

        $this->log($user, 'folder.updated', $folder, "Renamed folder to {$folder->name}");

        return $folder->fresh(['department', 'user', 'parent']);
    }

    public function delete(Folder $folder, User $user): void
    {
        $name = $folder->name;

        DB::transaction(function () use ($folder) {
            $folder->delete();
        });

        $this->log($user, 'folder.deleted', $folder, "Deleted folder {$name}");
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function ancestors(Folder $folder): array
    {
        $crumbs = [];
        $current = $folder->parent;

        while ($current !== null) {
            array_unshift($crumbs, [
                'id' => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }

        return $crumbs;
    }

    private function log(User $user, string $action, Folder $folder, string $description): void
    {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $folder->getMorphClass(),
            'subject_id' => $folder->id,
            'description' => $description,
        ]);
    }
}
