<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\Category;
use App\Models\ItemPrice;
use App\Models\Items;
use App\Models\Languages;
use App\Models\LanguageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function index()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $data['lang_code'] = isset($language_detail->code) ? $language_detail->code : '';

        $data['categories'] = Category::with(['items'])->where('shop_id',$shop_id)->get();
        $data['languages'] = Languages::get();
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->get();
        return view('client.language.language',$data);
    }


    // On - Off Google Translate
    public function setGoogleTranslate(Request $request)
    {
        try
        {
            $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
            $status = $request->status;

            $setting = LanguageSettings::where('shop_id',$shop_id)->where('key','google_translate')->first();
            $setting_id = (isset($setting->id)) ? $setting->id : '';

            // Insert or Update Default Key
            if(!empty($setting_id) || $setting_id != '')
            {
                $primary_lang = LanguageSettings::find($setting_id);
                $primary_lang->value = $status;
                $primary_lang->update();
            }
            else
            {
                $primary_lang = new LanguageSettings();
                $primary_lang->shop_id = $shop_id;
                $primary_lang->key = "google_translate";
                $primary_lang->value = $status;
                $primary_lang->save();
            }

            $custom_message = ($status == 1) ? 'Enabled' : 'Disabled';

            return response()->json([
                'success' => 1,
                'message' => "Google Translate has been $custom_message SucessFully....",
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


    // Set Primary Language
    public function setPrimaryLanguage(Request $request)
    {
        // try
        // {
            $shop_id = $request->shop_id;
            $language_id = $request->language_id;

            $setting = LanguageSettings::where('shop_id',$shop_id)->where('key','primary_language')->first();
            $setting_id = (isset($setting->id)) ? $setting->id : '';

            // Insert or Update Default Key
            if(!empty($setting_id) || $setting_id != '')
            {
                $primary_lang = LanguageSettings::find($setting_id);
                $primary_lang->value = $language_id;
                $primary_lang->update();
            }
            else
            {
                $primary_lang = new LanguageSettings();
                $primary_lang->shop_id = $shop_id;
                $primary_lang->key = "primary_language";
                $primary_lang->value = $language_id;
                $primary_lang->save();
            }

            // Language Details
            $language_detail = Languages::where('id',$language_id)->first();
            $lang_code = isset($language_detail->code) ? $language_detail->code : '';


            // Enter All Categories Data into Primary Language if is empty
            $categories = Category::where('shop_id',$shop_id)->get();
            if(count($categories) > 0)
            {
                foreach($categories as $category)
                {
                    $def_name = $category->name;
                    $def_description = $category->description;

                    if(!empty($lang_code))
                    {
                        // Insert Category Name into Primary Language.
                        $name_key = $lang_code."_name";
                        $lang_cat_name = $category[$name_key];
                        if(empty($lang_cat_name) || $lang_cat_name == '')
                        {
                            $cat = Category::find($category->id);
                            $cat->$name_key = $def_name;
                            $cat->update();
                        }

                        // Insert Category Description into Primary Language.
                        $description_key = $lang_code."_description";
                        $lang_cat_description = $category[$description_key];
                        if(empty($lang_cat_description))
                        {
                            $cat = Category::find($category->id);
                            $cat->$description_key = $def_description;
                            $cat->update();
                        }
                    }
                }
            }

            // Enter All Items Data into Primary Language if is empty
            $items = Items::where('shop_id',$shop_id)->get();
            if(count($items) > 0)
            {
                foreach($items as $item)
                {
                    $def_item_name = $item->name;
                    $def_item_calories = $item->calories;
                    $def_item_description = $item->description;

                    if(!empty($lang_code))
                    {
                        // Insert Item Name into Primary Language.
                        $item_name_key = $lang_code."_name";
                        $lang_item_name = $item[$item_name_key];
                        if(empty($lang_item_name))
                        {
                            $item = Items::find($item->id);
                            $item->$item_name_key = $def_item_name;
                            $item->update();
                        }

                        // Insert Item Price into Primary Language.
                        $price_arr = ItemPrice::where('item_id',$item->id)->where('shop_id',$shop_id)->get();

                        if(count($price_arr) > 0)
                        {
                            foreach($price_arr as $item_price)
                            {
                                $price_id = $item_price->id;
                                $price = $item_price->price;
                                $price_def_label = $item_price->label;

                                $item_label_price_key = $lang_code."_label";
                                $lang_item_price_label = $item_price[$item_label_price_key];
                                if(empty($lang_item_price_label))
                                {
                                    $it_price = ItemPrice::find($price_id);
                                    $it_price->$item_label_price_key = $price_def_label;
                                    $it_price->price = $price;
                                    $it_price->update();
                                }
                            }
                        }

                        // Insert Item Calories into Primary Language.
                        $item_calories_key = $lang_code."_calories";
                        $lang_item_calories = $item[$item_calories_key];
                        if(empty($lang_item_calories))
                        {
                            $item = Items::find($item->id);
                            $item->$item_calories_key = $def_item_calories;
                            $item->update();
                        }

                        // Insert Item Description into Primary Language.
                        $item_description_key = $lang_code."_description";
                        $lang_item_description = $item[$item_description_key];
                        if(empty($lang_item_description))
                        {
                            $item = Items::find($item->id);
                            $item->$item_description_key = $def_item_description;
                            $item->update();
                        }
                    }

                }
            }

            // Remove Primary Language From Additional Language
            AdditionalLanguage::where('shop_id',$shop_id)->where('language_id',$language_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => "Primary Language has been changed SuccessFully...",
            ]);

        // }
        // catch (\Throwable $th)
        // {
        //     return response()->json([
        //         'success' => 0,
        //         'message' => "Internal Server Error!",
        //     ]);
        // }
    }


    // Set Additional Languages
    public function setAdditionalLanguages(Request $request)
    {
        try
        {
            $shop_id = $request->shop_id;
            $language_ids = $request->language_ids;

            if(count($language_ids) > 0)
            {
                foreach($language_ids as $key => $val)
                {
                    $language = AdditionalLanguage::where('shop_id',$shop_id)->where('language_id',$val)->first();
                    $additional_language_id = isset($language->id) ? $language->id : '';

                    if(empty($additional_language_id) || $additional_language_id == '')
                    {
                        $additional_language = new AdditionalLanguage();
                        $additional_language->shop_id = $shop_id;
                        $additional_language->language_id = $val;
                        $additional_language->save();
                    }
                }
            }

            // Get All Additional Languages
            $additional_languages = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->get();
            $html = "";

            if(count($additional_languages) > 0)
            {
                foreach ($additional_languages as $key => $value)
                {
                    $isChecked = ($value->published == 1) ? 'checked' : '';

                    $html .= '<div class="col-md-3 mb-2 language_'.$value->language_id.'">';
                        $html .= '<div class="select_lang_inr">';
                            $html .= '<div class="">';
                                $html .= '<label class="form-check-label">'.$value->language["name"].'</label>';
                            $html .= '</div>';
                            $html .= '<label class="switch">';
                                $html .= '<input type="checkbox" id="publish_'.$value->id.'" '.$isChecked.' onchange="changeLanguageStatus('.$value->id.')">';
                                $html .= '<span class="slider round">';
                                    $html .= '<span class="check_icon">Publish</span>';
                                    $html .= '<span class="uncheck_icon">Unpublish</span>';
                                $html .= '</span>';
                            $html .= '</label>';
                        $html .= '</div>';
                    $html .= '</div>';
                }
            }


            return response()->json([
                'success' => 1,
                'message' => "New Additional Language has been Selected SuccessFully...",
                'data' => $html,
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


    // Delete Additional Language
    public function deleteAdditionalLanguage(Request $request)
    {
        try
        {
            $shop_id = $request->shop_id;
            $language_id = $request->language_id;

            AdditionalLanguage::where('shop_id',$shop_id)->where('language_id',$language_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => "Additional Language has been Removed SuccessFully...",
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


    // Change Status of Additional Language
    public function changeLanguageStatus(Request $request)
    {
        $id = $request->id;
        $published = $request->isChecked;

        try
        {
            $additional_language = AdditionalLanguage::find($id);
            $additional_language->published = $published;
            $additional_language->update();

            return response()->json([
                'success' => 1,
                'message' => "Additional Language Status has been Changed SuccessFully...",
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



    // Get Language Wise Category Details
    public function getCategoryDetails(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
        $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
        $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

        try
        {
            $category_detail = Category::where('id',$request->id)->first();

            $html = '';
            $html .= '<div class="row">';

            // Primary Lang Category Details
            $primary_cat_name = (isset($category_detail[$primary_lang_code.'_name'])) ? $category_detail[$primary_lang_code.'_name'] : "";
            $primary_cat_desc = (isset($category_detail[$primary_lang_code.'_description'])) ? $category_detail[$primary_lang_code.'_description'] : "";

            $html .= '<div class="col-md-4">';
                $html .= '<div class="card">';
                    $html .= '<div class="card-header"><h3>'.$primary_lang_name.'</h3></div>';
                    $html .= '<div class="card-body">';
                        $html .= '<div class="form-group mb-3">';
                            $html .= '<label for="category_name" class="form-label">Name</label>';
                            $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$primary_cat_name.'" disabled>';
                        $html .= '</div>';
                        $html .= '<div class="form-group mb-3">';
                            $html .= '<label for="category_desc" class="form-label">Description</label>';
                            $html .= '<textarea name="category_desc" id="category_desc" rows="3" class="form-control" disabled>'.$primary_cat_desc.'</textarea>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';



            // Additional Language Category Details
            if(count($additional_languages) > 0)
            {
                foreach ($additional_languages as $key => $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';
                    $input_lang_code = "'$add_lang_code'";

                    // Additional Lang Category Details
                    $add_cat_name = (isset($category_detail[$add_lang_code.'_name'])) ? $category_detail[$add_lang_code.'_name'] : "";
                    $add_cat_desc = (isset($category_detail[$add_lang_code.'_description'])) ? $category_detail[$add_lang_code.'_description'] : "";

                    $html .= '<div class="col-md-4">';
                        $html .= '<div class="card">';
                            $html .= '<div class="card-header"><h3>'.$add_lang_name.'</h3></div>';
                            $html .= '<div class="card-body">';
                                $html .= '<form id="'.$add_lang_code.'_cat_form" enctype="multipart/form-data">';
                                    $html .= csrf_field();
                                    $html .= '<input type="hidden" name="category_id" id="category_id" value="'.$category_detail['id'].'">';
                                    $html .= '<input type="hidden" name="lang_code" id="lang_code" value="'.$add_lang_code.'">';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<label for="category_name" class="form-label">Name</label>';
                                        $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$add_cat_name.'">';
                                    $html .= '</div>';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<label for="category_desc" class="form-label">Description</label>';
                                        $html .= '<textarea name="category_desc" id="category_desc" rows="3" class="form-control">'.$add_cat_desc.'</textarea>';
                                    $html .= '</div>';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<a class="btn btn-sm btn-success" onclick="updateCategoryDetail('.$input_lang_code.')">Update</a>';
                                    $html .= '</div>';
                                $html .= "</form>";
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';

                }
            }

            $html .= '</div>';

            return response()->json([
                'success' => 1,
                'message' => "Category Details has been Fetched SuccessFully...",
                'data'    => $html,
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


    // Update Language Wise Category Details
    public function updateCategoryDetails(Request $request)
    {
        $category_id = $request->category_id;
        $lang_code = $request->lang_code;
        $category_name = $request->category_name;
        $category_desc = $request->category_desc;

        $name_key = $lang_code."_name";
        $description_key = $lang_code."_description";

        $request->validate([
            'category_name' => 'required',
            'category_desc' => 'required',
        ]);

        try
        {
            $category = Category::find($category_id);

            if($category)
            {
                $category->$name_key = $category_name;
                $category->$description_key = $category_desc;
                $category->update();
            }

            return response()->json([
                'success' => 1,
                'message' => "Category has been Updated SuccessFully...",
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


    // Get Language Wise Item Details
    public function getItemDetails(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
        $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
        $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

        try
        {
            $item_detail = Items::where('id',$request->id)->first();

            $html = '';
            $html .= '<div class="row">';

            // Primary Lang Item Details
            $primary_item_name = (isset($item_detail[$primary_lang_code.'_name'])) ? $item_detail[$primary_lang_code.'_name'] : "";
            $primary_item_desc = (isset($item_detail[$primary_lang_code.'_description'])) ? $item_detail[$primary_lang_code.'_description'] : "";

            $html .= '<div class="col-md-4">';
                $html .= '<div class="card">';
                    $html .= '<div class="card-header"><h3>'.$primary_lang_name.'</h3></div>';
                    $html .= '<div class="card-body">';
                        $html .= '<div class="form-group mb-3">';
                            $html .= '<label for="item_name" class="form-label">Name</label>';
                            $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$primary_item_name.'" disabled>';
                        $html .= '</div>';
                        $html .= '<div class="form-group mb-3">';
                            $html .= '<label for="item_desc" class="form-label">Description</label>';
                            $html .= '<textarea name="item_desc" id="item_desc" rows="3" class="form-control" disabled>'.$primary_item_desc.'</textarea>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';



            // Additional Language Item Details
            if(count($additional_languages) > 0)
            {
                foreach ($additional_languages as $key => $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';
                    $input_lang_code = "'$add_lang_code'";

                    // Additional Lang Item Details
                    $add_item_name = (isset($item_detail[$add_lang_code.'_name'])) ? $item_detail[$add_lang_code.'_name'] : "";
                    $add_item_desc = (isset($item_detail[$add_lang_code.'_description'])) ? $item_detail[$add_lang_code.'_description'] : "";

                    $html .= '<div class="col-md-4">';
                        $html .= '<div class="card">';
                            $html .= '<div class="card-header"><h3>'.$add_lang_name.'</h3></div>';
                            $html .= '<div class="card-body">';
                                $html .= '<form id="'.$add_lang_code.'_item_form" enctype="multipart/form-data">';
                                    $html .= csrf_field();
                                    $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item_detail['id'].'">';
                                    $html .= '<input type="hidden" name="lang_code" id="lang_code" value="'.$add_lang_code.'">';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<label for="item_name" class="form-label">Name</label>';
                                        $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$add_item_name.'">';
                                    $html .= '</div>';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<label for="item_desc" class="form-label">Description</label>';
                                        $html .= '<textarea name="item_desc" id="item_desc" rows="3" class="form-control">'.$add_item_desc.'</textarea>';
                                    $html .= '</div>';
                                    $html .= '<div class="form-group mb-3">';
                                        $html .= '<a class="btn btn-sm btn-success" onclick="updateItemDetail('.$input_lang_code.')">Update</a>';
                                    $html .= '</div>';
                                $html .= "</form>";
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';

                }
            }

            $html .= '</div>';

            return response()->json([
                'success' => 1,
                'message' => "Item Details has been Fetched SuccessFully...",
                'data'    => $html,
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


    // Update Language Wise Item Details
    public function updateItemDetails(Request $request)
    {
        $item_id = $request->item_id;
        $lang_code = $request->lang_code;
        $item_name = $request->item_name;
        $item_desc = $request->item_desc;

        $name_key = $lang_code."_name";
        $description_key = $lang_code."_description";

        $request->validate([
            'item_name' => 'required',
            'item_desc' => 'required',
        ]);

        try
        {
            $item = Items::find($item_id);

            if($item)
            {
                $item->$name_key = $item_name;
                $item->$description_key = $item_desc;
                $item->update();
            }

            return response()->json([
                'success' => 1,
                'message' => "Item has been Updated SuccessFully...",
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
