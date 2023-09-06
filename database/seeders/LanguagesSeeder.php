<?php

namespace Database\Seeders;

use App\Models\Languages;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            [
                'name'            =>      'English',
                'code'            =>      "en",
                'status'          =>      1,
            ],
            [
                'name'            =>      'French',
                'code'            =>      "fr",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Greek',
                'code'            =>      "el",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Italian',
                'code'            =>      "it",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Spanish',
                'code'            =>      "es",
                'status'          =>      1,
            ],
            [
                'name'            =>      'German',
                'code'            =>      "de",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Bulgarian',
                'code'            =>      "bg",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Turkish',
                'code'            =>      "tr",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Romanian',
                'code'            =>      "ro",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Serbian',
                'code'            =>      "sr",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Chinese',
                'code'            =>      "zh",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Russian',
                'code'            =>      "ru",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Polish',
                'code'            =>      "pl",
                'status'          =>      1,
            ],
            [
                'name'            =>      'Georgian',
                'code'            =>      "ka",
                'status'          =>      1,
            ],
        ];

        Languages::insert($languages);
    }
}
