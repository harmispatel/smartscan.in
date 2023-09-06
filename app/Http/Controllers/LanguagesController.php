<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguagesController extends Controller
{
    public function saveAjax(Request $request)
    {
        $lang_name = $request->name;
        $lang_code = strtolower($request->code);

        try
        {
            $lang_name_column = $lang_code."_name";
            $lang_description_column = $lang_code."_description";
            $lang_calories_column = $lang_code."_calories";
            $lang_label_column = $lang_code."_label";
            $lang_title_column = $lang_code."_title";
            $lang_image_column = $lang_code."_image";

            // Check Language Exists or Not
            $check_lang_name = Languages::where('name', $lang_name)->exists();
            $check_lang_code = Languages::where('code',$lang_code)->exists();

            if($check_lang_name == 1 || $check_lang_code == 1)
            {
                return response()->json([
                    'success' => 0,
                    'message' => "Language Name or Code Already Exists",
                ]);
            }

            // Insert New Language In Language Table
            $language = new Languages();
            $language->name = $lang_name;
            $language->code = $lang_code;
            $language->status = 1;
            $language->save();

            if(!empty($lang_code) && !empty($language->id))
            {
                // Add new Column in Categories Table
                if(!Schema::hasColumns('categories', [$lang_name_column,$lang_description_column]))
                {
                    $query = "ALTER TABLE `categories` ADD COLUMN $lang_description_column TEXT DEFAULT NULL AFTER `published`, ADD COLUMN $lang_name_column VARCHAR(255) DEFAULT NULL AFTER `published`";
                    DB::statement($query);
                }

                // Add new Coulmn in Items Table
                if(!Schema::hasColumns('items', [$lang_name_column,$lang_calories_column,$lang_description_column]))
                {
                    $query = "ALTER TABLE `items` ADD COLUMN $lang_description_column TEXT DEFAULT NULL AFTER `description`, ADD COLUMN $lang_calories_column VARCHAR(255) DEFAULT NULL AFTER `description`, ADD COLUMN $lang_name_column VARCHAR(255) DEFAULT NULL AFTER `description`";
                    DB::statement($query);
                }

                // Add new Column in Item Prices Table
                if(!Schema::hasColumns('item_prices', [$lang_label_column]))
                {
                    $query = "ALTER TABLE `item_prices` ADD COLUMN $lang_label_column VARCHAR(255) DEFAULT NULL AFTER `label`";
                    DB::statement($query);
                }

                // Add new Column in Options Table
                if(!Schema::hasColumns('options', [$lang_title_column]))
                {
                    $query = "ALTER TABLE `options` ADD COLUMN $lang_title_column VARCHAR(255) DEFAULT NULL AFTER `multiple_select`";
                    DB::statement($query);
                }

                // Add new Column in Option Prices Table
                if(!Schema::hasColumns('option_prices', [$lang_name_column]))
                {
                    $query = "ALTER TABLE `option_prices` ADD COLUMN $lang_name_column VARCHAR(255) DEFAULT NULL AFTER `name`";
                    DB::statement($query);
                }

                // Add new Column in Shop Banners Table
                if(!Schema::hasColumns('shop_banners', [$lang_image_column,$lang_description_column]))
                {
                    $query = "ALTER TABLE `shop_banners` ADD COLUMN $lang_image_column VARCHAR(255) DEFAULT NULL AFTER `image`, ADD COLUMN $lang_description_column LONGTEXT DEFAULT NULL AFTER `description`";
                    DB::statement($query);
                }

                // Add new Column in Tags Table
                if(!Schema::hasColumns('tags', [$lang_name_column]))
                {
                    $query = "ALTER TABLE `tags` ADD COLUMN $lang_name_column VARCHAR(255) DEFAULT NULL AFTER `name`";
                    DB::statement($query);
                }
            }

            return response()->json([
                'success' => 1,
                'message' => 'New Language has been Inserted SuccessFully..',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }
    }
}
