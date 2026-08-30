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
            'appName',
            'site_name',
            'siteName',
            'organization_name',
            'organizationName',
            'site_description',
            'siteDescription',
            'support_email',
            'contactEmail',
            'support_phone',
            'contactPhone',
            'emergency_police',
            'emergencyPolice',
            'emergency_tourist_police',
            'emergencyTouristPolice',
            'emergency_ambulance',
            'emergencyAmbulance',
            'emergency_fire',
            'emergencyFire',
            'maintenance_mode',
            'maintenanceMode',
            'maintenance_message',
            'maintenanceMessage',
            'app_version',
            'privacy_policy_url',
            'privacyPolicyUrl',
            'terms_of_service_url',
            'termsOfServiceUrl',
            'default_currency',
            'defaultCurrency',
            'default_language',
            'defaultLanguage',
            'timezone',
            'logo_url',
            'logoUrl',
            'favicon_url',
            'faviconUrl',
            'google_maps_api_key',
            'googleMapsApiKey',
            'mapbox_api_key',
            'mapboxApiKey',
            'weather_api_key',
            'weatherApiKey',
        ];

        $dbSettings = SystemSetting::whereIn('setting_key', $safeKeys)
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        $appName = $dbSettings['app_name'] ?? $dbSettings['appName'] ?? $dbSettings['site_name'] ?? $dbSettings['siteName'] ?? 'AngkorVerses';
        $isMaintenance = filter_var($dbSettings['maintenance_mode'] ?? $dbSettings['maintenanceMode'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $publicSettings = [
            'app_name' => $appName,
            'site_name' => $dbSettings['site_name'] ?? $dbSettings['siteName'] ?? $appName,
            'organization_name' => $dbSettings['organization_name'] ?? $dbSettings['organizationName'] ?? 'Ministry of Tourism & Culture Cambodia',
            'site_description' => $dbSettings['site_description'] ?? $dbSettings['siteDescription'] ?? 'Official AngkorVerses Smart Tourism Portal',
            'app_version' => $dbSettings['app_version'] ?? '1.0.0',
            'maintenance_mode' => $isMaintenance,
            'maintenance_message' => $dbSettings['maintenance_message'] ?? $dbSettings['maintenanceMessage'] ?? 'The AngkorVerses portal is undergoing scheduled maintenance. Please check back shortly.',
            'support_email' => $dbSettings['support_email'] ?? $dbSettings['contactEmail'] ?? 'support@tourism.gov.kh',
            'support_phone' => $dbSettings['support_phone'] ?? $dbSettings['contactPhone'] ?? '+855 23 888 999',
            'logo_url' => $dbSettings['logo_url'] ?? $dbSettings['logoUrl'] ?? null,
            'favicon_url' => $dbSettings['favicon_url'] ?? $dbSettings['faviconUrl'] ?? null,
            'emergency_contacts' => [
                'police' => $dbSettings['emergency_police'] ?? $dbSettings['emergencyPolice'] ?? '117',
                'tourist_police' => $dbSettings['emergency_tourist_police'] ?? $dbSettings['emergencyTouristPolice'] ?? '+855 31 322 2117',
                'ambulance' => $dbSettings['emergency_ambulance'] ?? $dbSettings['emergencyAmbulance'] ?? '119',
                'fire' => $dbSettings['emergency_fire'] ?? $dbSettings['emergencyFire'] ?? '118',
            ],
            'legal' => [
                'terms_url' => $dbSettings['terms_of_service_url'] ?? $dbSettings['termsOfServiceUrl'] ?? 'https://tourism.gov.kh/terms',
                'privacy_url' => $dbSettings['privacy_policy_url'] ?? $dbSettings['privacyPolicyUrl'] ?? 'https://tourism.gov.kh/privacy',
            ],
            'localization' => [
                'default_language' => $dbSettings['default_language'] ?? $dbSettings['defaultLanguage'] ?? 'km',
                'supported_languages' => ['km', 'en', 'fr', 'zh'],
                'default_currency' => $dbSettings['default_currency'] ?? $dbSettings['defaultCurrency'] ?? 'USD',
                'timezone' => $dbSettings['timezone'] ?? 'Asia/Phnom_Penh',
            ],
            'integrations' => [
                'google_maps_api_key' => $dbSettings['google_maps_api_key'] ?? $dbSettings['googleMapsApiKey'] ?? '',
                'mapbox_api_key' => $dbSettings['mapbox_api_key'] ?? $dbSettings['mapboxApiKey'] ?? '',
                'weather_api_key' => $dbSettings['weather_api_key'] ?? $dbSettings['weatherApiKey'] ?? '',
            ],
        ];

        return $this->successResponse(
            $publicSettings,
            'Public application settings retrieved successfully.'
        );
    }
}
