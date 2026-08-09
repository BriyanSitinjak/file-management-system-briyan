<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Services\FileService;
use App\Services\FolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function __construct(
        private FolderService $folders,
        private FileService $files,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'Administrator', 403);

        return response()->json([
            'folders' => Folder::onlyTrashed()
                ->with(['department', 'user'])
                ->latest('deleted_at')
                ->get(),
            'files' => File::onlyTrashed()
                ->with(['department', 'folder', 'user'])
                ->latest('deleted_at')
                ->get(),
        ]);
    }

    public function restoreFolder(Request $request, int $folder): JsonResponse
    {
        $model = Folder::onlyTrashed()->findOrFail($folder);
        $this->authorize('restore', $model);

        return response()->json($this->folders->restore($model, $request->user()));
    }

    public function restoreFile(Request $request, int $file): JsonResponse
    {
        $model = File::onlyTrashed()->findOrFail($file);
        $this->authorize('restore', $model);

        return response()->json($this->files->restore($model, $request->user()));
    }
}
