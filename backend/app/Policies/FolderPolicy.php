<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    /**
     * Administrator and Viewer may list folders so both can browse the tree.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator and Viewer may view a folder; Viewers stay read-only.
     */
    public function view(User $user, Folder $folder): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator only; Viewers cannot create folders.
     */
    public function create(User $user): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot rename or move folders.
     */
    public function update(User $user, Folder $folder): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot soft-delete folders.
     */
    public function delete(User $user, Folder $folder): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; restoring deleted folders is a write action.
     */
    public function restore(User $user, Folder $folder): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; permanent removal is restricted to admins.
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        return $user->role === 'Administrator';
    }
}
