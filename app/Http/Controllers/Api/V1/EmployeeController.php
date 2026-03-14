<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = User::where('tenant_id', $request->user()->tenant_id)
            ->where('id', '!=', $request->user()->id)
            ->with('roles')
            ->withTrashed()
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => UserResource::collection($employees),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        $user->assignRole($data['role']);
        $user->load('roles', 'permissions');

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $employee = User::where('tenant_id', $request->user()->tenant_id)
            ->withTrashed()
            ->with('roles', 'permissions')
            ->findOrFail($id);

        return response()->json([
            'data' => new UserResource($employee),
        ]);
    }

    public function update(int $id, UpdateEmployeeRequest $request): JsonResponse
    {
        $employee = User::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($id);

        $data = $request->validated();

        if (isset($data['role'])) {
            $employee->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $employee->update($data);
        $employee->load('roles', 'permissions');

        return response()->json([
            'data' => new UserResource($employee),
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $employee = User::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($id);

        $employee->update(['is_active' => false]);
        $employee->delete();

        return response()->json(null, 204);
    }

    public function restore(int $id, Request $request): JsonResponse
    {
        $employee = User::where('tenant_id', $request->user()->tenant_id)
            ->onlyTrashed()
            ->findOrFail($id);

        $employee->restore();
        $employee->update(['is_active' => true]);
        $employee->load('roles', 'permissions');

        return response()->json([
            'data' => new UserResource($employee),
        ]);
    }
}
