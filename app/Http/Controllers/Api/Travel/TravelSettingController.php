<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelSettingController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        // Safe list of public setting keys
        $safeKeys = [
            'app_name',
            'site_name',
            'support_email',
            'support_phone',
            'emergency_contact',
            'maintenance_mode',
            'app_version',
            'privacy_policy_url',
            'terms_of_service_url',
            'default_currency',
            'default_language',
        ];

        $dbSettings = SystemSetting::whereIn('setting_key', $safeKeys)
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        $publicSettings = [
            'app_name' => $dbSettings['app_name'] ?? $dbSettings['site_name'] ?? 'AngkorVerses',
            'app_version' => $dbSettings['app_version'] ?? '1.0.0',
            'maintenance_mode' => filter_var($dbSettings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'support_email' => $dbSettings['support_email'] ?? 'support@tourism.gov.kh',
            'support_phone' => $dbSettings['support_phone'] ?? '+855 23 123 456',
            'emergency_contacts' => [
                'police' => '117',
                'tourist_police' => '+855 31 322 2117',
                'ambulance' => '119',
                'fire' => '118',
            ],
            'legal' => [
                'terms_url' => $dbSettings['terms_of_service_url'] ?? 'https://tourism.gov.kh/terms',
                'privacy_url' => $dbSettings['privacy_policy_url'] ?? 'https://tourism.gov.kh/privacy',
            ],
            'localization' => [
                'default_language' => $dbSettings['default_language'] ?? 'km',
                'supported_languages' => ['km', 'en', 'fr', 'zh'],
                'default_currency' => $dbSettings['default_currency'] ?? 'USD',
            ],
        ];

        return $this->successResponse(
            $publicSettings,
            'Public application settings retrieved successfully.'
        );
    }
}
