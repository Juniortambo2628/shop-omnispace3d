<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'catalog_password_solarandstorage' => 'ssl2026',
            'gmail_address' => 'solarandstoragelive@omnispace3d.com',
            'admin_notification_email' => 'solarandstoragelive@omnispace3d.com',
            'smtp_host' => 'smtp.gmail.com',
            'company_name' => 'OmniSpace 3D Events Ltd',
            'company_address' => 'Eldama Office Park, Nairobi, Kenya',
            'company_email' => 'solarandstorage@omnispace3d.com',
            'company_phone' => '+254 731 001 723',
            'company_website' => 'www.omnispace3d.com',
            'company_whatsapp' => '+254 731 001 723',
        ];

        foreach ($settings as $key => $value) {
            $payload = ['value' => $value];

            if (Schema::hasColumn('settings', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            if (Schema::hasColumn('settings', 'created_at')) {
                $payload['created_at'] = now();
            }

            DB::table('settings')->updateOrInsert(['key' => $key], $payload);
        }
    }
}
