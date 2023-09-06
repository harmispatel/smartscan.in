<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\CategoryProductTags;
use App\Models\Languages;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagsController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['tags'] = Tags::where('shop_id',$shop_id)->get();
        return view('client.tags.tags',$data);
    }


    // Show the form for creating a new resource.
    public function create()
    {
        //
    }


    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $tag_name_key = $lang_code."_name";

        $request->validate([
            'tag_name' => 'required|unique:tags,'.$tag_name_key.',NULL,id,shop_id,'.$shop_id,
        ]);

        $max_tag_order_key = Tags::max('order');
        $tag_order = (isset($max_tag_order_key) && !empty($max_tag_order_key)) ? ($max_tag_order_key + 1) : 1;

        try
        {
            $tag = new Tags();
            $tag->name = $request->tag_name;
            $tag->shop_id = $shop_id;
            $tag->order = $tag_order;
            $tag->$tag_name_key = $request->tag_name;
            $tag->save();

            return response()->json([
                'success' => 1,
                'message' => "Tag has been Inserted SuccessFully...",
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


    // Sorting Tags.
    public function sorting(Request $request)
    {
        $sort_array = $request->sortArr;

        foreach ($sort_array as $key => $value)
        {
    		$key = $key+1;
    		Tags::where('id',$value)->update(['order'=>$key]);
    	}

        return response()->json([
            'success' => 1,
            'message' => "Tags has been Sorted SuccessFully....",
        ]);

    }


    // Function for edit Tag Language Wise
    public function edit(Request $request)
    {
        $tag_id = $request->id;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        try
        {
            // Get Language Settings
            $language_settings = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

            // Primary Language Details
            $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
            $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
            $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';
            $tag_name_key = $primary_lang_code."_name";

            // Additional Languages
            $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

            // Tag Details
            $tag_details = Tags::where('id',$tag_id)->first();
            $tag_name = (isset($tag_details[$tag_name_key])) ? $tag_details[$tag_name_key] : '';

            // Dynamic Language Bar
            if(count($additional_languages) > 0)
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                    // Additional Language
                    foreach($additional_languages as $value)
                    {
                        // Additional Language Details
                        $add_lang_detail = Languages::where('id',$value->language_id)->first();
                        $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                        $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                        $html .= '<a class="text-uppercase" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                    }
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editTagForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="tag_id" id="tag_id" value="'.$tag_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-3">';
                                    $html .= '<label for="name" class="form-label">'. __('Tag Name') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-9">';
                                    $html .= '<input type="text" name="name" id="name" class="form-control" value="'.$tag_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
            }
            else
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editTagForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="tag_id" id="tag_id" value="'.$tag_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-3">';
                                    $html .= '<label for="name" class="form-label">'. __('Tag Name') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-9">';
                                    $html .= '<input type="text" name="name" id="name" class="form-control" value="'.$tag_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            return response()->json([
                'success' => 1,
                'message' => 'Data has been Fetched SuccessFully..',
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


    // Function for Update Tag By Language Code
    public function updateByLangCode(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $tag_id = $request->tag_id;
        $name = $request->name;
        $active_lang_code = $request->active_lang_code;
        $next_lang_code = $request->next_lang_code;
        $act_lang_name_key = $active_lang_code."_name";

        $request->validate([
            'name' => 'required|unique:tags,'.$act_lang_name_key.','.$tag_id.',id,shop_id,'.$shop_id,
        ]);

        try
        {
            // Update Tag
            $tag = Tags::find($tag_id);
            $tag->name = $name;
            $tag->$act_lang_name_key = $name;
            $tag->update();

            // Get HTML Data
            $html_data = $this->getEditTagData($next_lang_code,$tag_id);

            return response()->json([
                'success' => 1,
                'message' => 'Data has been Updated SuccessFully...',
                'data' => $html_data,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for Get Tag Data
    public function getEditTagData($current_lang_code,$tag_id)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
        $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
        $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();
        if(count($additional_languages) > 0)
        {
            $tag_name_key = $current_lang_code."_name";
        }
        else
        {
            $tag_name_key = $primary_lang_code."_name";
        }

        // Tag Details
        $tag_details = Tags::where('id',$tag_id)->first();
        $tag_name = isset($tag_details[$tag_name_key]) ? $tag_details[$tag_name_key] : '';

        // Primary Active Tab
        $primary_active_tab = ($primary_lang_code == $current_lang_code) ? 'active' : '';

        // Dynamic Language Bar
        if(count($additional_languages) > 0)
        {
            $html = '';
            $html .= '<div class="lang-tab">';
                // Primary Language
                $html .= '<a class="'.$primary_active_tab.' text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                // Additional Language
                foreach($additional_languages as $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                    // Additional Active Tab
                    $additional_active_tab = ($add_lang_code == $current_lang_code) ? 'active' : '';

                    $html .= '<a class="'.$additional_active_tab.' text-uppercase" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                }
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editTagForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$current_lang_code.'">';
                            $html .= '<input type="hidden" name="tag_id" id="tag_id" value="'.$tag_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-3">';
                                    $html .= '<label for="name" class="form-label">'. __('Tag Name') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-9">';
                                    $html .= '<input type="text" name="name" id="name" class="form-control" value="'.$tag_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
        }
        else
        {
            $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
                $html .= '</div>';
                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editTagForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="tag_id" id="tag_id" value="'.$tag_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-3">';
                                    $html .= '<label for="name" class="form-label">'. __('Tag Name') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-9">';
                                    $html .= '<input type="text" name="name" id="name" class="form-control" value="'.$tag_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
        }

        return $html;

    }


    // Function for edit Tag Language Wise
    public function update(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $tag_id = $request->tag_id;
        $name = $request->name;
        $active_lang_code = $request->active_lang_code;
        $act_lang_name_key = $active_lang_code."_name";

        $request->validate([
            'name' => 'required|unique:tags,'.$act_lang_name_key.','.$tag_id.',id,shop_id,'.$shop_id,
        ]);

        try
        {
            // Update Tag
            $tag = Tags::find($tag_id);
            $tag->name = $name;
            $tag->$act_lang_name_key = $name;
            $tag->update();

            return response()->json([
                'success' => 1,
                'message' => 'Tag has been Updated SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }

    }


    // Remove the specified resource from storage.
    public function destroy(Request $request)
    {
        try
        {
            $id = $request->id;

            // Delete Product Tags
            CategoryProductTags::where('tag_id',$id)->delete();

            // Delete Tag
            Tags::where('id',$id)->delete();

            return response()->json([
                'success' => 1,
                'message' => "Item has been Deleted SuccessFully....",
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
