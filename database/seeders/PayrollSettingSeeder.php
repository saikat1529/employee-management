<?php

namespace Database\Seeders;

use App\Models\PayrollSetting;
use Illuminate\Database\Seeder;

class PayrollSettingSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate keeps re-runs safe and does not overwrite rules an admin has changed.
        foreach (PayrollSetting::defaults() as $key => $value) {
            PayrollSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
