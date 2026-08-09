<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $departments) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        return response()->json($this->departments->list());
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $department = $this->departments->store($request->validated());

        return response()->json($department, 201);
    }

    public function show(Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        return response()->json($department);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $this->authorize('update', $department);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        return response()->json($this->departments->update($department, $data));
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        $this->departments->delete($department);

        return response()->json(null, 204);
    }
}
