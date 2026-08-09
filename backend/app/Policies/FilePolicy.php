<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Administrator and Viewer may list files so both can browse contents.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator and Viewer may view a file; Viewers stay read-only.
     */
    public function view(User $user, File $file): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator only; Viewers cannot upload or create files.
     */
    public function create(User $user): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot rename or replace files.
     */
    public function update(User $user, File $file): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot soft-delete files.
     */
    public function delete(User $user, File $file): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; restoring deleted files is a write action.
     */
    public function restore(User $user, File $file): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; permanent removal is restricted to admins.
     */
    public function forceDelete(User $user, File $file): bool
    {
        return $user->role === 'Administrator';
    }
}
