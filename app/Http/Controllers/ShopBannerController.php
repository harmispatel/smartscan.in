<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\Languages;
use App\Models\ShopBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopBannerController extends Controller
{

    public function index()
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];
        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);
        if(!isset($package_permissions['banner']) || empty($package_permissions['banner']) || $package_permissions['banner'] == 0)
        {
            return redirect()->route('client.dashboard')->with('error',"You have not access this Menu");
        }

        $data['banners'] = ShopBanner::where('key','shop_banner')->where('shop_id',$shop_id)->get();

        return view('client.design.banner',$data);
    }


    // Function for Store Newely Created Banner
    public function store(Request $request)
    {

        // Validation
        $request->validate([
            'image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $description = $request->description;
        $display = $request->display;
        $background_color = $request->background_color;
        $description_key = $lang_code."_description";
        $image_key = $lang_code."_image";

        try
        {
            $banner = new ShopBanner();
            $banner->shop_id = $shop_id;
            $banner->key = 'shop_banner';
            $banner->display = $display;
            $banner->background_color = $background_color;
            $banner->description = $description;
            $banner->$description_key = $description;

            if($request->hasFile('image'))
            {
                // Insert new Image
                $imgname = "banner_".time().".". $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/banners/'), $imgname);
                $banner->image = $imgname;
                $banner->$image_key = $imgname;
            }

            $banner->save();

            return response()->json([
                'success' => 1,
                'message' => 'Banner has been Inserted SuccessFully....',
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


    // Function for Edit Banner
    public function edit(Request $request)
    {
        $banner_id = $request->id;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $display_arr = [
            'both' => 'Both',
            'image' => 'Image',
            'description' => 'Description',
        ];

        try
        {
            // Get Language Settings
            $language_settings = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

            // Primary Language Details
            $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
            $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
            $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';
            $banner_image_key = $primary_lang_code."_image";
            $banner_description_key = $primary_lang_code."_description";

            // Additional Languages
            $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

            // Banner Details
            $banner_details = ShopBanner::where('id',$banner_id)->first();
            $default_image = asset('public/client_images/not-found/no_image_1.jpg');
            $banner_image = (isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key])) ? asset('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]) : $default_image;
            $banner_desc = isset($banner_details[$banner_description_key]) ? $banner_details[$banner_description_key] : '';
            $background_color = isset($banner_details['background_color']) ? $banner_details['background_color'] : '';

            // Dynamic Language Bar
            if(count($additional_languages) > 0)
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" title="'.$primary_lang_name.'" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                    // Additional Language
                    foreach($additional_languages as $value)
                    {
                        // Additional Language Details
                        $add_lang_detail = Languages::where('id',$value->language_id)->first();
                        $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                        $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                        $html .= '<a class="text-uppercase" title="'.$add_lang_name.'" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                    }
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editBannerForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="banner_id" id="banner_id" value="'.$banner_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="image" class="form-label">'. __('Image') .'</label>';
                                    $html .= '<input type="file" name="image" id="image" class="form-control">';
                                    $html .= '<code>'.__('Banner Dimensions (1140*300)').'</code>';
                                    $html .= '<div class="position-relative mt-2 banner-img">';
                                        $html .= '<img src='.$banner_image.' class="" width="160">';
                                        if(isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]))
                                        {
                                            $html .= '<a onclick="deleteBannerImage('.$banner_id.',\''.$primary_lang_code.'\')" class="btn btn-sm btn-danger position-absolute" style="top:0;left:0"><i class="bi bi-trash"></i></a>';
                                        }
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="display" class="form-label">'. __('Display').'</label>';
                                    $html .= '<select name="display" id="display" class="form-select">';
                                        foreach($display_arr as $key => $val)
                                        {
                                            $html .= '<option value="'.$key.'"';
                                            if($key == $banner_details['display'])
                                            {
                                                $html .= 'selected';
                                            }
                                            $html .='>'.$val.'</option>';
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="display" class="form-label">'.__('Background Color').'</label>';
                                    $html .= '<input type="color" name="background_color" id="background_color" value="'.$background_color.'" class="form-control">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="description" class="form-label">'. __('Description').'</label>';
                                    $html .= '<textarea name="description" id="description" class="form-control">'.$banner_desc.'</textarea>';
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
                    $html .= '<a class="active text-uppercase" title="'.$primary_lang_name.'" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="editBannerForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="banner_id" id="banner_id" value="'.$banner_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="image" class="form-label">'. __('Image') .'</label>';
                                    $html .= '<input type="file" name="image" id="image" class="form-control">';
                                    $html .= '<code>'.__('Banner Dimensions (1140*300)').'</code>';
                                    $html .= '<div class="position-relative mt-2 banner-img">';
                                        $html .= '<img src='.$banner_image.' class="" width="160">';
                                        if(isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]))
                                        {
                                            $html .= '<a onclick="deleteBannerImage('.$banner_id.',\''.$primary_lang_code.'\')" class="btn btn-sm btn-danger position-absolute" style="top:0;left:0"><i class="bi bi-trash"></i></a>';
                                        }
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="display" class="form-label">'. __('Display').'</label>';
                                    $html .= '<select name="display" id="display" class="form-select">';
                                        foreach($display_arr as $key => $val)
                                        {
                                            $html .= '<option value="'.$key.'"';
                                            if($key == $banner_details['display'])
                                            {
                                                $html .= 'selected';
                                            }
                                            $html .='>'.$val.'</option>';
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="display" class="form-label">'.__('Background Color').'</label>';
                                    $html .= '<input type="color" name="background_color" id="background_color" value="'.$background_color.'" class="form-control">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label for="description" class="form-label">'. __('Description').'</label>';
                                    $html .= '<textarea name="description" id="description" class="form-control">'.$banner_desc.'</textarea>';
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
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for Update Banner By Language Code
    public function updateByLangCode(Request $request)
    {
        // Shop ID & Slug
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $banner_id = $request->banner_id;
        $description = $request->description;
        $display = $request->display;
        $background_color = $request->background_color;
        $active_lang_code = $request->active_lang_code;
        $next_lang_code = $request->next_lang_code;

        $request->validate([
            'image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        try
        {
            $update_image_key = $active_lang_code."_image";
            $update_description_key = $active_lang_code."_description";

            // Update Banner
            $banner = ShopBanner::find($banner_id);
            $banner->display = $display;
            $banner->background_color = $background_color;
            $banner->description = $description;
            $banner->$update_description_key = $description;

            // Update Banner Image
            if($request->hasFile('image'))
            {
                // Remove Old Image
                $old_image = isset($banner[$update_image_key]) ? $banner[$update_image_key] : '';
                if(!empty($old_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$old_image))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/banners/'.$old_image);
                }

                // Insert new Image
                $imgname = "banner_".time().".". $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/banners/'), $imgname);
                $banner->image = $imgname;
                $banner->$update_image_key = $imgname;
            }

            $banner->update();

            // Get HTML Data
            $html_data = $this->getEditBannerData($next_lang_code,$banner_id);

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


    // Function for Get Banner Data
    public function getEditBannerData($current_lang_code,$banner_id)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $display_arr = [
            'both' => 'Both',
            'image' => 'Image',
            'description' => 'Description',
        ];

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
            $banner_image_key = $current_lang_code."_image";
            $banner_description_key = $current_lang_code."_description";
        }
        else
        {
            $banner_image_key = $primary_lang_code."_image";
            $banner_description_key = $primary_lang_code."_description";
        }

        // Banner Details
        $banner_details = ShopBanner::where('id',$banner_id)->first();
        $default_image = asset('public/client_images/not-found/no_image_1.jpg');
        $banner_image = (isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key])) ? asset('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]) : $default_image;
        $banner_desc = isset($banner_details[$banner_description_key]) ? $banner_details[$banner_description_key] : '';
        $background_color = isset($banner_details['background_color']) ? $banner_details['background_color'] : '';

        // Primary Active Tab
        $primary_active_tab = ($primary_lang_code == $current_lang_code) ? 'active' : '';

        // Dynamic Language Bar
        if(count($additional_languages) > 0)
        {
            $html = '';
            $html .= '<div class="lang-tab">';
                // Primary Language
                $html .= '<a class="'.$primary_active_tab.' text-uppercase" title="'.$primary_lang_name.'" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                // Additional Language
                foreach($additional_languages as $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                    // Additional Active Tab
                    $additional_active_tab = ($add_lang_code == $current_lang_code) ? 'active' : '';

                    $html .= '<a class="'.$additional_active_tab.' text-uppercase" title="'.$add_lang_name.'" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                }
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="editBannerForm" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$current_lang_code.'">';
                        $html .= '<input type="hidden" name="banner_id" id="banner_id" value="'.$banner_details['id'].'">';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="image" class="form-label">'. __('Image') .'</label>';
                                $html .= '<input type="file" name="image" id="image" class="form-control">';
                                $html .= '<code>'.__('Banner Dimensions (1140*300)').'</code>';
                                $html .= '<div class="position-relative mt-2 banner-img">';
                                    $html .= '<img src='.$banner_image.' class="" width="160">';
                                    if(isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]))
                                    {
                                        $html .= '<a onclick="deleteBannerImage('.$banner_id.',\''.$current_lang_code.'\')" class="btn btn-sm btn-danger position-absolute" style="top:0;left:0"><i class="bi bi-trash"></i></a>';
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="display" class="form-label">'. __('Display').'</label>';
                                $html .= '<select name="display" id="display" class="form-select">';
                                    foreach($display_arr as $key => $val)
                                    {
                                        $html .= '<option value="'.$key.'"';
                                        if($key == $banner_details['display'])
                                        {
                                            $html .= 'selected';
                                        }
                                        $html .='>'.$val.'</option>';
                                    }
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="display" class="form-label">'.__('Background Color').'</label>';
                                $html .= '<input type="color" name="background_color" id="background_color" value="'.$background_color.'" class="form-control">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="description" class="form-label">'. __('Description').'</label>';
                                $html .= '<textarea name="description" id="description" class="form-control">'.$banner_desc.'</textarea>';
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
                $html .= '<a class="active text-uppercase" title="'.$primary_lang_name.'" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="editBannerForm" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                        $html .= '<input type="hidden" name="banner_id" id="banner_id" value="'.$banner_details['id'].'">';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="image" class="form-label">'. __('Image') .'</label>';
                                $html .= '<input type="file" name="image" id="image" class="form-control">';
                                $html .= '<code>'.__('Banner Dimensions (1140*300)').'</code>';
                                $html .= '<div class="position-relative mt-2 banner-img">';
                                    $html .= '<img src='.$banner_image.' class="" width="160">';
                                    if(isset($banner_details[$banner_image_key]) && !empty($banner_details[$banner_image_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_details[$banner_image_key]))
                                    {
                                        $html .= '<a onclick="deleteBannerImage('.$banner_id.',\''.$current_lang_code.'\')" class="btn btn-sm btn-danger position-absolute" style="top:0;left:0"><i class="bi bi-trash"></i></a>';
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="display" class="form-label">'. __('Display').'</label>';
                                $html .= '<select name="display" id="display" class="form-select">';
                                    foreach($display_arr as $key => $val)
                                    {
                                        $html .= '<option value="'.$key.'"';
                                        if($key == $banner_details['display'])
                                        {
                                            $html .= 'selected';
                                        }
                                        $html .='>'.$val.'</option>';
                                    }
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="display" class="form-label">'.__('Background Color').'</label>';
                                $html .= '<input type="color" name="background_color" id="background_color" value="'.$background_color.'" class="form-control">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label for="description" class="form-label">'. __('Description').'</label>';
                                $html .= '<textarea name="description" id="description" class="form-control">'.$banner_desc.'</textarea>';
                            $html .= '</div>';
                        $html .= '</div>';

                    $html .= '</form>';
                $html .= '</div>';
            $html .= '</div>';
        }

        return $html;

    }


    // Function for Update Banners
    public function update(Request $request)
    {
        // Shop ID & Slug
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $banner_id = $request->banner_id;
        $display = $request->display;
        $background_color = $request->background_color;
        $description = $request->description;
        $active_lang_code = $request->active_lang_code;

        $request->validate([
            'image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        try
        {
            $update_description_key = $active_lang_code."_description";
            $update_image_key = $active_lang_code."_image";

            // Update Banner
            $banner = ShopBanner::find($banner_id);
            $banner->display = $display;
            $banner->background_color = $background_color;
            $banner->description = $description;
            $banner->$update_description_key = $description;

            // Update Banner Image
            if($request->hasFile('image'))
            {
                // Remove Old Image
                $old_image = isset($banner[$update_image_key]) ? $banner[$update_image_key] : '';
                if(!empty($old_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$old_image))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/banners/'.$old_image);
                }

                // Insert new Image
                $imgname = "banner_".time().".". $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/banners/'), $imgname);
                $banner->image = $imgname;
                $banner->$update_image_key = $imgname;
            }

            $banner->update();

            return response()->json([
                'success' => 1,
                'message' => 'Banner has been Updated SuccessFully...',
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


    // Delete Banner Image
    public function deleteBanner(Request $request)
    {
        $language_code = $request->lang_code;
        $banner_id = $request->banner_id;
        $shop_id = (isset(Auth::user()->hasOneShop['shop']->id)) ? Auth::user()->hasOneShop['shop']->id : '';
        $shop_slug = (isset(Auth::user()->hasOneShop['shop']->shop_slug)) ? Auth::user()->hasOneShop['shop']->shop_slug : '';
        $image_key = $language_code."_image";

        try
        {
            $banner = ShopBanner::find($banner_id);

            if($banner)
            {
                $lang_bannner = isset($banner[$image_key]) ? $banner[$image_key] : '';

                if(!empty($lang_bannner) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$lang_bannner))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/banners/'.$lang_bannner);
                }

                $banner->image = "";
                $banner->$image_key = "";
                $banner->update();
            }

            return response()->json([
                'success' => 1,
                'message' => 'Banner Image has been Removed SuccessFully...',
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


    // Function for Delete Banner
    public function destroy(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $image_key = $lang_code."_image";

        $banner_id = $request->id;

        try
        {
            $banner_dt = ShopBanner::where('id',$banner_id)->first();
            $banner_img = (isset($banner_dt[$image_key])) ? $banner_dt[$image_key] : '';

            // Delete Image
            if(!empty($banner_img) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_img))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_img);
            }

            // Delete Banner
            ShopBanner::where('id',$banner_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Banner has been Deleted SuccessFully..',
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
}
