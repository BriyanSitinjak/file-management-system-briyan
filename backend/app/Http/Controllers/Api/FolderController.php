<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(private FolderService $folders) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Folder::class);

        $filters = $request->only(['department_id', 'q']);

        if ($request->has('parent_id')) {
            $filters['parent_id'] = $request->filled('parent_id')
                ? (int) $request->input('parent_id')
                : null;
        }

        return response()->json($this->folders->list($filters));
    }

    public function store(StoreFolderRequest $request): JsonResponse
    {
        $this->authorize('create', Folder::class);

        $folder = $this->folders->store($request->validated(), $request->user());

        return response()->json($folder, 201);
    }

    public function show(Folder $folder): JsonResponse
    {
        $this->authorize('view', $folder);

        return response()->json($this->folders->show($folder));
    }

    public function update(Request $request, Folder $folder): JsonResponse
    {
        $this->authorize('update', $folder);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        return response()->json($this->folders->update($folder, $data, $request->user()));
    }

    public function destroy(Request $request, Folder $folder): JsonResponse
    {
        $this->authorize('delete', $folder);

        $this->folders->delete($folder, $request->user());

        return response()->json(null, 204);
    }
}
