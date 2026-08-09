<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FolderService
{
    public function __construct(private ActivityLogger $activity) {}

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
     * @return array{folder: Folder, breadcrumbs: list<array{id: int|null, name: string}>}
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
            'breadcrumbs' => $this->breadcrumbsFor($folder),
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

        $this->activity->log($user, 'folder.created', $folder, "Created folder {$folder->name}");

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

        $this->activity->log($user, 'folder.updated', $folder, "Renamed folder to {$folder->name}");

        return $folder->fresh(['department', 'user', 'parent']);
    }

    public function delete(Folder $folder, User $user): void
    {
        $name = $folder->name;
        $folder->delete();
        $this->activity->log($user, 'folder.deleted', $folder, "Deleted folder {$name}");
    }

    public function restore(Folder $folder, User $user): Folder
    {
        $folder->restore();
        $this->activity->log($user, 'folder.restored', $folder, "Restored folder {$folder->name}");

        return $folder->fresh(['department', 'user', 'parent']);
    }

    /**
     * @return list<array{id: int|null, name: string}>
     */
    public function breadcrumbsFor(Folder $folder): array
    {
        $trail = [];
        $current = $folder;

        while ($current !== null) {
            array_unshift($trail, [
                'id' => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }

        array_unshift($trail, [
            'id' => null,
            'name' => 'Root',
        ]);

        return $trail;
    }
}
