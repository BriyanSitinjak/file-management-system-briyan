<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * Administrator and Viewer may list departments so both can browse scope.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator and Viewer may view a department; Viewers stay read-only.
     */
    public function view(User $user, Department $department): bool
    {
        return in_array($user->role, ['Administrator', 'Viewer'], true);
    }

    /**
     * Administrator only; Viewers cannot create departments.
     */
    public function create(User $user): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot change department details.
     */
    public function update(User $user, Department $department): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; Viewers cannot delete departments.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; restoring departments is a write action.
     */
    public function restore(User $user, Department $department): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Administrator only; permanent removal is restricted to admins.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return $user->role === 'Administrator';
    }
}
