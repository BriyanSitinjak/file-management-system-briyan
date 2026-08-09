<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(User $user, string $action, Model $subject, string $description): void
    {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->id,
            'description' => $description,
        ]);
    }
}
