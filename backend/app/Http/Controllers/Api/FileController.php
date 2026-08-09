<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(private FileService $files) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', File::class);

        return response()->json($this->files->list($request->only(['folder_id', 'department_id', 'q'])));
    }

    public function store(StoreFileRequest $request): JsonResponse
    {
        $this->authorize('create', File::class);

        $file = $this->files->store($request->validated(), $request->user());

        return response()->json($file, 201);
    }

    public function show(File $file): JsonResponse
    {
        $this->authorize('view', $file);

        return response()->json($this->files->show($file));
    }

    public function update(UpdateFileRequest $request, File $file): JsonResponse
    {
        $this->authorize('update', $file);

        return response()->json($this->files->update($file, $request->validated(), $request->user()));
    }

    public function destroy(Request $request, File $file): JsonResponse
    {
        $this->authorize('delete', $file);

        $this->files->delete($file, $request->user());

        return response()->json(null, 204);
    }

    public function download(Request $request, File $file): StreamedResponse
    {
        $this->authorize('view', $file);

        return $this->files->download($file, $request->user());
    }
}
