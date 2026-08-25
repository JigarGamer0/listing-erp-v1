<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Listing ERP', 'type' => 'text'],
            ['group' => 'general', 'key' => 'company_logo', 'value' => null, 'type' => 'file'],
            ['group' => 'general', 'key' => 'currency', 'value' => '₹', 'type' => 'text'],
            ['group' => 'general', 'key' => 'currency_code', 'value' => 'INR', 'type' => 'text'],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Asia/Kolkata', 'type' => 'text'],
            ['group' => 'general', 'key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'text'],
            ['group' => 'general', 'key' => 'default_theme', 'value' => 'light', 'type' => 'select'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
