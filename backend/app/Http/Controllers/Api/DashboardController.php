<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Return dashboard summary data.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'totals' => [
                'folders' => Folder::query()->count(),
                'files' => File::query()->count(),
                'departments' => Department::query()->count(),
                'users' => User::query()->count(),
            ],
            'latest_files' => File::query()
                ->with(['department', 'folder', 'user'])
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
