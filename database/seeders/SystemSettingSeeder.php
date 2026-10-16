<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // =========================================================================
            // General Settings
            // =========================================================================
            [
                'setting_key' => 'siteName',
                'setting_value' => 'AngkorVerses Smart Tourism Portal',
                'setting_group' => 'general',
                'description' => 'Official application name for AngkorVerses admin and tourism platform'
            ],
            [
                'setting_key' => 'site_name',
                'setting_value' => 'AngkorVerses Smart Tourism Portal',
                'setting_group' => 'general',
                'description' => 'Official application name for AngkorVerses (snake_case alias)'
            ],
            [
                'setting_key' => 'site_title',
                'setting_value' => 'AngkorVerses Smart Tourism Portal',
                'setting_group' => 'general',
                'description' => 'Main portal header title'
            ],
            [
                'setting_key' => 'appName',
                'setting_value' => 'AngkorVerses',
                'setting_group' => 'general',
                'description' => 'Short application branding name'
            ],
            [
                'setting_key' => 'app_name',
                'setting_value' => 'AngkorVerses',
                'setting_group' => 'general',
                'description' => 'Short application branding name (snake_case alias)'
            ],
            [
                'setting_key' => 'organizationName',
                'setting_value' => 'Ministry of Tourism & Culture Cambodia',
                'setting_group' => 'general',
                'description' => 'Official governing organization or ministry'
            ],
            [
                'setting_key' => 'organization_name',
                'setting_value' => 'Ministry of Tourism & Culture Cambodia',
                'setting_group' => 'general',
                'description' => 'Official governing organization (snake_case alias)'
            ],
            [
                'setting_key' => 'siteDescription',
                'setting_value' => 'Official AngkorVerses Administration & Cultural Heritage Management Portal',
                'setting_group' => 'general',
                'description' => 'Overview and description of the tourism platform'
            ],
            [
                'setting_key' => 'site_description',
                'setting_value' => 'Official AngkorVerses Administration & Cultural Heritage Management Portal',
                'setting_group' => 'general',
                'description' => 'Overview and description (snake_case alias)'
            ],
            [
                'setting_key' => 'contactEmail',
                'setting_value' => 'support@tourism.gov.kh',
                'setting_group' => 'general',
                'description' => 'Official support and help desk email address'
            ],
            [
                'setting_key' => 'contact_email',
                'setting_value' => 'support@tourism.gov.kh',
                'setting_group' => 'general',
                'description' => 'Official support email address (snake_case alias)'
            ],
            [
                'setting_key' => 'support_email',
                'setting_value' => 'support@tourism.gov.kh',
                'setting_group' => 'general',
                'description' => 'Public support email address'
            ],
            [
                'setting_key' => 'contactPhone',
                'setting_value' => '+855 23 888 999',
                'setting_group' => 'general',
                'description' => 'Official tourism customer support telephone hotline'
            ],
            [
                'setting_key' => 'contact_phone',
                'setting_value' => '+855 23 888 999',
                'setting_group' => 'general',
                'description' => 'Official tourism hotline (snake_case alias)'
            ],
            [
                'setting_key' => 'emergencyPolice',
                'setting_value' => '117',
                'setting_group' => 'general',
                'description' => 'National Police emergency hotline in Cambodia'
            ],
            [
                'setting_key' => 'emergency_police',
                'setting_value' => '117',
                'setting_group' => 'general',
                'description' => 'National Police emergency hotline (snake_case alias)'
            ],
            [
                'setting_key' => 'emergencyTouristPolice',
                'setting_value' => '+855 31 322 2117',
                'setting_group' => 'general',
                'description' => 'Tourist Police specialized hotline for international travelers'
            ],
            [
                'setting_key' => 'emergency_tourist_police',
                'setting_value' => '+855 31 322 2117',
                'setting_group' => 'general',
                'description' => 'Tourist Police specialized hotline (snake_case alias)'
            ],
            [
                'setting_key' => 'emergencyAmbulance',
                'setting_value' => '119',
                'setting_group' => 'general',
                'description' => 'National Emergency Medical and Ambulance service hotline'
            ],
            [
                'setting_key' => 'emergency_ambulance',
                'setting_value' => '119',
                'setting_group' => 'general',
                'description' => 'National Ambulance service hotline (snake_case alias)'
            ],
            [
                'setting_key' => 'emergencyFire',
                'setting_value' => '118',
                'setting_group' => 'general',
                'description' => 'National Fire & Rescue emergency hotline in Cambodia'
            ],
            [
                'setting_key' => 'emergency_fire',
                'setting_value' => '118',
                'setting_group' => 'general',
                'description' => 'National Fire & Rescue emergency hotline (snake_case alias)'
            ],
            [
                'setting_key' => 'defaultLanguage',
                'setting_value' => 'en',
                'setting_group' => 'general',
                'description' => 'Default system localization language (km or en)'
            ],
            [
                'setting_key' => 'default_language',
                'setting_value' => 'en',
                'setting_group' => 'general',
                'description' => 'Default system localization language (snake_case alias)'
            ],
            [
                'setting_key' => 'defaultCurrency',
                'setting_value' => 'USD',
                'setting_group' => 'general',
                'description' => 'Default platform currency (USD or KHR)'
            ],
            [
                'setting_key' => 'default_currency',
                'setting_value' => 'USD',
                'setting_group' => 'general',
                'description' => 'Default platform currency (snake_case alias)'
            ],
            [
                'setting_key' => 'timezone',
                'setting_value' => 'Asia/Phnom_Penh',
                'setting_group' => 'general',
                'description' => 'Official standard operating timezone (Asia/Phnom_Penh - UTC+7)'
            ],
            [
                'setting_key' => 'dateFormat',
                'setting_value' => 'YYYY-MM-DD',
                'setting_group' => 'general',
                'description' => 'Default date presentation format across dashboards'
            ],
            [
                'setting_key' => 'date_format',
                'setting_value' => 'YYYY-MM-DD',
                'setting_group' => 'general',
                'description' => 'Default date presentation format (snake_case alias)'
            ],
            [
                'setting_key' => 'termsOfServiceUrl',
                'setting_value' => 'https://tourism.gov.kh/terms',
                'setting_group' => 'general',
                'description' => 'Official Terms of Service documentation link'
            ],
            [
                'setting_key' => 'terms_of_service_url',
                'setting_value' => 'https://tourism.gov.kh/terms',
                'setting_group' => 'general',
                'description' => 'Official Terms of Service link (snake_case alias)'
            ],
            [
                'setting_key' => 'privacyPolicyUrl',
                'setting_value' => 'https://tourism.gov.kh/privacy',
                'setting_group' => 'general',
                'description' => 'Official Privacy Policy and Data Rights documentation link'
            ],
            [
                'setting_key' => 'privacy_policy_url',
                'setting_value' => 'https://tourism.gov.kh/privacy',
                'setting_group' => 'general',
                'description' => 'Official Privacy Policy link (snake_case alias)'
            ],
            [
                'setting_key' => 'maintenanceMode',
                'setting_value' => 'false',
                'setting_group' => 'general',
                'description' => 'Global maintenance toggle pausing public client traffic'
            ],
            [
                'setting_key' => 'maintenance_mode',
                'setting_value' => 'false',
                'setting_group' => 'general',
                'description' => 'Global maintenance toggle (snake_case alias)'
            ],
            [
                'setting_key' => 'maintenanceMessage',
                'setting_value' => 'The AngkorVerses administrative portal is undergoing scheduled maintenance. Please check back shortly.',
                'setting_group' => 'general',
                'description' => 'Public banner text displayed during maintenance downtime'
            ],
            [
                'setting_key' => 'maintenance_message',
                'setting_value' => 'The AngkorVerses administrative portal is undergoing scheduled maintenance. Please check back shortly.',
                'setting_group' => 'general',
                'description' => 'Public banner text displayed during maintenance (snake_case alias)'
            ],

            // =========================================================================
            // Appearance Settings
            // =========================================================================
            [
                'setting_key' => 'theme',
                'setting_value' => 'system',
                'setting_group' => 'appearance',
                'description' => 'Default theme mode (light, dark, system)'
            ],
            [
                'setting_key' => 'sidebarStyle',
                'setting_value' => 'brand',
                'setting_group' => 'appearance',
                'description' => 'Sidebar style theme preset (brand, slate, light)'
            ],
            [
                'setting_key' => 'sidebar_style',
                'setting_value' => 'brand',
                'setting_group' => 'appearance',
                'description' => 'Sidebar style preset (snake_case alias)'
            ],

            // =========================================================================
            // Notification Preferences
            // =========================================================================
            [
                'setting_key' => 'pushNotifications',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Web push notifications enabled for browser clients'
            ],
            [
                'setting_key' => 'push_notifications',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Web push notifications (snake_case alias)'
            ],
            [
                'setting_key' => 'emailNotifications',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Transactional email alerts delivered to staff'
            ],
            [
                'setting_key' => 'email_notifications',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Transactional email alerts (snake_case alias)'
            ],
            [
                'setting_key' => 'smsNotifications',
                'setting_value' => 'false',
                'setting_group' => 'notifications',
                'description' => 'SMS text alert delivery for emergency notifications'
            ],
            [
                'setting_key' => 'sms_notifications',
                'setting_value' => 'false',
                'setting_group' => 'notifications',
                'description' => 'SMS text alert delivery (snake_case alias)'
            ],
            [
                'setting_key' => 'newUserAlert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Dispatch alert upon tourist account registration'
            ],
            [
                'setting_key' => 'new_user_alert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Dispatch alert upon tourist registration (snake_case alias)'
            ],
            [
                'setting_key' => 'newReviewAlert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Dispatch alert when new destination review is submitted'
            ],
            [
                'setting_key' => 'new_review_alert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Dispatch alert when new review is submitted (snake_case alias)'
            ],
            [
                'setting_key' => 'newEventAlert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Notify subscribers about newly published cultural festivals'
            ],
            [
                'setting_key' => 'new_event_alert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Notify subscribers about cultural festivals (snake_case alias)'
            ],
            [
                'setting_key' => 'deletionRequestAlert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Send urgent alert to Super Admin upon GDPR deletion request'
            ],
            [
                'setting_key' => 'deletion_request_alert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Send urgent alert upon GDPR deletion request (snake_case alias)'
            ],
            [
                'setting_key' => 'securityAlert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Send real-time alerts for suspicious access or brute-force attempts'
            ],
            [
                'setting_key' => 'security_alert',
                'setting_value' => 'true',
                'setting_group' => 'notifications',
                'description' => 'Send real-time alerts for security events (snake_case alias)'
            ],

            // =========================================================================
            // Security Policy Settings
            // =========================================================================
            [
                'setting_key' => 'twoFactorAuth',
                'setting_value' => 'true',
                'setting_group' => 'security',
                'description' => 'Two-Factor Authentication enforcement status'
            ],
            [
                'setting_key' => 'two_factor_auth',
                'setting_value' => 'true',
                'setting_group' => 'security',
                'description' => 'Two-Factor Authentication enforcement (snake_case alias)'
            ],
            [
                'setting_key' => 'sessionTimeout',
                'setting_value' => '30',
                'setting_group' => 'security',
                'description' => 'Administrative inactivity session timeout in minutes'
            ],
            [
                'setting_key' => 'session_timeout',
                'setting_value' => '30',
                'setting_group' => 'security',
                'description' => 'Inactivity session timeout in minutes (snake_case alias)'
            ],
            [
                'setting_key' => 'passwordPolicy',
                'setting_value' => 'strong',
                'setting_group' => 'security',
                'description' => 'Password strength rule policy (standard, strong, strict)'
            ],
            [
                'setting_key' => 'password_policy',
                'setting_value' => 'strong',
                'setting_group' => 'security',
                'description' => 'Password strength policy (snake_case alias)'
            ],
            [
                'setting_key' => 'loginAttempts',
                'setting_value' => '5',
                'setting_group' => 'security',
                'description' => 'Maximum failed login attempts before temporary IP throttle'
            ],
            [
                'setting_key' => 'login_attempts',
                'setting_value' => '5',
                'setting_group' => 'security',
                'description' => 'Maximum failed login attempts (snake_case alias)'
            ],

            // =========================================================================
            // External Integrations & APIs
            // =========================================================================
            [
                'setting_key' => 'googleMapsApiKey',
                'setting_value' => 'AIzaSyC_AngkorVersesMapsPlatform2026Key',
                'setting_group' => 'integrations',
                'description' => 'Google Maps Platform Javascript API Key for tourist pinpoints'
            ],
            [
                'setting_key' => 'google_maps_api_key',
                'setting_value' => 'AIzaSyC_AngkorVersesMapsPlatform2026Key',
                'setting_group' => 'integrations',
                'description' => 'Google Maps Platform API Key (snake_case alias)'
            ],
            [
                'setting_key' => 'mapboxApiKey',
                'setting_value' => 'pk.eyJ1IjoiYW5na29ydmVyc2VzIiwiYSI6ImNsdG91cmlzbTIwMjZtYXBib3hrZXkifQ.mockMapboxToken',
                'setting_group' => 'integrations',
                'description' => 'Mapbox GL Vector Tile public access token'
            ],
            [
                'setting_key' => 'mapbox_api_key',
                'setting_value' => 'pk.eyJ1IjoiYW5na29ydmVyc2VzIiwiYSI6ImNsdG91cmlzbTIwMjZtYXBib3hrZXkifQ.mockMapboxToken',
                'setting_group' => 'integrations',
                'description' => 'Mapbox public token (snake_case alias)'
            ],
            [
                'setting_key' => 'weatherApiKey',
                'setting_value' => 'owm_live_cambodia_climate_api_key_2026',
                'setting_group' => 'integrations',
                'description' => 'OpenWeatherMap One Call API key for live tourist climate'
            ],
            [
                'setting_key' => 'weather_api_key',
                'setting_value' => 'owm_live_cambodia_climate_api_key_2026',
                'setting_group' => 'integrations',
                'description' => 'OpenWeatherMap API key (snake_case alias)'
            ],
            [
                'setting_key' => 'aiProvider',
                'setting_value' => 'gemini',
                'setting_group' => 'integrations',
                'description' => 'AI Virtual Assistant intelligence provider (gemini, openai, anthropic)'
            ],
            [
                'setting_key' => 'ai_provider',
                'setting_value' => 'gemini',
                'setting_group' => 'integrations',
                'description' => 'AI Provider (snake_case alias)'
            ],
            [
                'setting_key' => 'aiApiKey',
                'setting_value' => 'AIzaSyD_Gemini20FlashAngkorVersesTourismKey',
                'setting_group' => 'integrations',
                'description' => 'Google Gemini 2.0 Flash API Key for AI travel planner assistant'
            ],
            [
                'setting_key' => 'ai_api_key',
                'setting_value' => 'AIzaSyD_Gemini20FlashAngkorVersesTourismKey',
                'setting_group' => 'integrations',
                'description' => 'Google Gemini API Key (snake_case alias)'
            ],
            [
                'setting_key' => 'smtpHost',
                'setting_value' => 'smtp.mailtrap.io',
                'setting_group' => 'integrations',
                'description' => 'SMTP mail relay server host'
            ],
            [
                'setting_key' => 'smtp_host',
                'setting_value' => 'smtp.mailtrap.io',
                'setting_group' => 'integrations',
                'description' => 'SMTP mail relay host (snake_case alias)'
            ],
            [
                'setting_key' => 'smtpPort',
                'setting_value' => '587',
                'setting_group' => 'integrations',
                'description' => 'SMTP mail relay port number'
            ],
            [
                'setting_key' => 'smtp_port',
                'setting_value' => '587',
                'setting_group' => 'integrations',
                'description' => 'SMTP mail relay port (snake_case alias)'
            ],
            [
                'setting_key' => 'smtpEncryption',
                'setting_value' => 'tls',
                'setting_group' => 'integrations',
                'description' => 'SMTP transport encryption protocol (tls, ssl, none)'
            ],
            [
                'setting_key' => 'smtp_encryption',
                'setting_value' => 'tls',
                'setting_group' => 'integrations',
                'description' => 'SMTP encryption protocol (snake_case alias)'
            ],
            [
                'setting_key' => 'smtpUsername',
                'setting_value' => 'mailer@tourism.gov.kh',
                'setting_group' => 'integrations',
                'description' => 'SMTP authentication username'
            ],
            [
                'setting_key' => 'smtp_username',
                'setting_value' => 'mailer@tourism.gov.kh',
                'setting_group' => 'integrations',
                'description' => 'SMTP authentication username (snake_case alias)'
            ],
            [
                'setting_key' => 'smtpPassword',
                'setting_value' => 'khmer_tourism_smtp_2026',
                'setting_group' => 'integrations',
                'description' => 'SMTP authentication password credentials'
            ],
            [
                'setting_key' => 'smtp_password',
                'setting_value' => 'khmer_tourism_smtp_2026',
                'setting_group' => 'integrations',
                'description' => 'SMTP password credentials (snake_case alias)'
            ],

            // =========================================================================
            // Backup & Database Maintenance
            // =========================================================================
            [
                'setting_key' => 'backupSchedule',
                'setting_value' => 'daily',
                'setting_group' => 'backup',
                'description' => 'Automated database snapshot frequency'
            ],
            [
                'setting_key' => 'backup_schedule',
                'setting_value' => 'daily',
                'setting_group' => 'backup',
                'description' => 'Automated snapshot frequency (snake_case alias)'
            ],
            [
                'setting_key' => 'backupRetention',
                'setting_value' => '30',
                'setting_group' => 'backup',
                'description' => 'Database snapshot retention in days'
            ],
            [
                'setting_key' => 'backup_retention',
                'setting_value' => '30',
                'setting_group' => 'backup',
                'description' => 'Snapshot retention days (snake_case alias)'
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_value' => $setting['setting_value'],
                    'setting_group' => $setting['setting_group'],
                    'description'   => $setting['description'],
                ]
            );
        }
    }
}
