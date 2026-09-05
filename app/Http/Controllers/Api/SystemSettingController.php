<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        $group = $request->query('group');
        $query = SystemSetting::query();

        if ($group) {
            $query->where('setting_group', $group);
        }

        $settings = $query->get();

        return $this->successResponse(SystemSettingResource::collection($settings), 'System settings retrieved.');
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:100',
            'settings.*.value' => 'nullable|string',
            'settings.*.group' => 'nullable|string|max:50',
            'settings.*.description' => 'nullable|string|max:255',
        ]);

        foreach ($validated['settings'] as $item) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $item['key']],
                [
                    'setting_value' => $item['value'] ?? null,
                    'setting_group' => $item['group'] ?? 'general',
                    'description' => $item['description'] ?? null,
                ]
            );
        }

        $settings = SystemSetting::all();

        return $this->successResponse(SystemSettingResource::collection($settings), 'System settings updated successfully.');
    }
}
