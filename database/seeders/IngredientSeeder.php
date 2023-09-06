<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ingredients = [
            [
                'name'            =>         'Vegan',
                'status'          =>         1,
            ],
            [
                'name'            =>         'Hot',
                'status'          =>         1,
            ],
            [
                'name'            =>         'Cold',
                'status'          =>         1,
            ],
        ];

        Ingredient::insert($ingredients);
    }
}
