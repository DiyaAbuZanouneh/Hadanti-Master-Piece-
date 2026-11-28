<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgeGroupRequest;
use App\Models\AgeGroup;
use Illuminate\Http\JsonResponse;

class AgeGroupController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            AgeGroup::withCount('classrooms')->orderBy('min_months')->get()
        );
    }

    public function store(AgeGroupRequest $request): JsonResponse
    {
        $ageGroup = AgeGroup::create($request->validated());

        return response()->json([
            'message'   => 'Age group created',
            'age_group' => $ageGroup,
        ], 201);
    }

    public function update(AgeGroupRequest $request, AgeGroup $ageGroup): JsonResponse
    {
        $ageGroup->update($request->validated());

        return response()->json([
            'message'   => 'Age group updated',
            'age_group' => $ageGroup,
        ]);
    }

    public function destroy(AgeGroup $ageGroup): JsonResponse
    {
        if ($ageGroup->classrooms()->exists()) {
            return response()->json([
                'message' => 'This age group is used by classrooms, deactivate it instead',
            ], 409);
        }

        $ageGroup->delete();

        return response()->json([
            'message' => 'Age group deleted',
        ]);
    }
}