<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canBrowse();
    }

    public function view(User $user, Folder $folder): bool
    {
        return $user->canBrowse();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }
}
