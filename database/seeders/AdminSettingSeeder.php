<?php

namespace Database\Seeders;

use App\Models\AdminSettings;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminSettings = [
            [
                'key'            =>         'favourite_client_limit',
                'value'          =>         5,
            ],
            [
                'key'            =>         'copyright_text',
                'value'          =>         '<h4>&copy; [year] Copyright all Rights reserved.</h4>',
            ],
        ];

        AdminSettings::insert($adminSettings);
    }
}
