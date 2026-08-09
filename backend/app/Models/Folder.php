<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A nested folder used to organize files within a department hierarchy.
 */
class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'department_id',
        'parent_id',
        'user_id',
    ];

    // Links folders to departments (many-to-one).
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Links folders to users as the folder owner (many-to-one).
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Links folders to a parent folder via parent_id (many-to-one, self-referencing).
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Links folders to child folders via parent_id (one-to-many, self-referencing).
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Links folders to files (one-to-many).
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
