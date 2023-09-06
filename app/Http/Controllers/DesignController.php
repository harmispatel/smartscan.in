<?php

namespace App\Http\Controllers;

use App\Models\ClientSettings;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignController extends Controller
{
    // Frontend Topbar Logo View
    public function logo()
    {
        return view('client.design.logo');
    }



    // Upload new Frontend Topbar Logo
    public function logoUpload(Request $request)
    {

        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'shop_view_header_logo' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        try
        {
            if(!empty($clientID) && !empty($shop_id))
            {
                if($request->hasFile('shop_view_header_logo'))
                {
                    $get_logo_setting = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','shop_view_header_logo')->first();
                    $setting_id = isset($get_logo_setting->id) ? $get_logo_setting->id : '';

                    if(!empty($setting_id) || $setting_id != '')
                    {
                        // Delete old Logo
                        $logo = isset($get_logo_setting->value) ? $get_logo_setting->value : '';
                        if(!empty($logo) && file_exists('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo);
                        }

                        // Insert new Logo
                        $logo_name = "top_logo_".time().".". $request->file('shop_view_header_logo')->getClientOriginalExtension();
                        $request->file('shop_view_header_logo')->move(public_path('client_uploads/shops/'.$shop_slug.'/top_logos/'), $logo_name);
                        $new_logo = $logo_name;

                        $logo_setting = ClientSettings::find($setting_id);
                        $logo_setting->value = $new_logo;
                        $logo_setting->update();

                    }
                    else
                    {
                        // Insert new Logo
                        $logo_name = "top_logo_".time().".". $request->file('shop_view_header_logo')->getClientOriginalExtension();
                        $request->file('shop_view_header_logo')->move(public_path('client_uploads/shops/'.$shop_slug.'/top_logos/'), $logo_name);
                        $new_logo = $logo_name;

                        $logo_setting = new ClientSettings();
                        $logo_setting->client_id = $clientID;
                        $logo_setting->shop_id = $shop_id;
                        $logo_setting->key = 'shop_view_header_logo';
                        $logo_setting->value = $new_logo;
                        $logo_setting->save();
                    }

                }

                return response()->json([
                    'success' => 1,
                    'message' => 'Logo has been Uploaded SuccessFully....',
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Oops, Something Went Wrong !',
                ]);
            }
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error !',
            ]);
        }

    }


    // Delete Logo
    public function deleteLogo()
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $get_logo_setting = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','shop_view_header_logo')->first();
        $setting_id = isset($get_logo_setting->id) ? $get_logo_setting->id : '';
        $logo = isset($get_logo_setting->value) ? $get_logo_setting->value : '';

        if(!empty($logo) && file_exists('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo))
        {
            unlink('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo);
        }

        if(!empty($setting_id))
        {
            $logo_setting = ClientSettings::find($setting_id);
            $logo_setting->value = "";
            $logo_setting->update();
        }

        return redirect()->route('design.logo')->with('success','Logo has been Removed SuccessFully...');

    }


    // Change Intro Status
    public function introStatus(Request $request)
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        try
        {
            if(!empty($clientID) && !empty($shop_id))
            {
                $get_intro_status_setting = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','intro_icon_status')->first();
                $setting_id = isset($get_intro_status_setting->id) ? $get_intro_status_setting->id : '';

                if(!empty($setting_id) || $setting_id != '')
                {
                    $intro_status = ClientSettings::find($setting_id);
                    $intro_status->value = $request->status;
                    $intro_status->update();
                }
                else
                {
                    $intro_status = new ClientSettings();
                    $intro_status->client_id = $clientID;
                    $intro_status->shop_id = $shop_id;
                    $intro_status->key = 'intro_icon_status';
                    $intro_status->value = $request->status;
                    $intro_status->save();
                }

                if($request->status == 1)
                {
                    $message = "Intro has been Enabled SuccessFully....";
                }
                else
                {
                    $message = "Intro has been Disabled SuccessFully....";
                }

                return response()->json([
                    'success' => 1,
                    'message' => $message,
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Oops, Something Went Wrong !',
                ]);
            }
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error !',
            ]);
        }
    }

    // Change Intro Duration
    public function introDuration(Request $request)
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        try
        {
            if(!empty($clientID) && !empty($shop_id))
            {
                $get_intro_duration_setting = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','intro_icon_duration')->first();
                $setting_id = isset($get_intro_duration_setting->id) ? $get_intro_duration_setting->id : '';

                if(!empty($setting_id) || $setting_id != '')
                {
                    $intro_duration = ClientSettings::find($setting_id);
                    $intro_duration->value = $request->duration;
                    $intro_duration->update();
                }
                else
                {
                    $intro_duration = new ClientSettings();
                    $intro_duration->client_id = $clientID;
                    $intro_duration->shop_id = $shop_id;
                    $intro_duration->key = 'intro_icon_duration';
                    $intro_duration->value = $request->duration;
                    $intro_duration->save();
                }

                return response()->json([
                    'success' => 1,
                    'message' => "Duration has been Updated SuccessFully...",
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Oops, Something Went Wrong !',
                ]);
            }
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error !',
            ]);
        }
    }


    // Upload Intro Icon
    public function introIconUpload(Request $request)
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'shop_intro_icon' => 'mimes:png,jpg,svg,gif,jpeg,PNG,SVG,JPG,JPEG,GIF,mp4,mov|max:2000',
        ]);

        try
        {
            if(!empty($clientID) && !empty($shop_id))
            {
                if($request->hasFile('shop_intro_icon'))
                {
                    $get_intro_icon_setting = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','shop_intro_icon')->first();
                    $setting_id = isset($get_intro_icon_setting->id) ? $get_intro_icon_setting->id : '';

                    if(!empty($setting_id) || $setting_id != '')
                    {
                        // Delete old Logo
                        $icon = isset($get_intro_icon_setting->value) ? $get_intro_icon_setting->value : '';
                        if(!empty($icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$icon))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$icon);
                        }

                        // Insert new Logo
                        $icon_name = "intro_icon_".time().".". $request->file('shop_intro_icon')->getClientOriginalExtension();
                        $request->file('shop_intro_icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/intro_icons/'), $icon_name);
                        $new_icon = $icon_name;

                        $logo_setting = ClientSettings::find($setting_id);
                        $logo_setting->value = $new_icon;
                        $logo_setting->update();

                    }
                    else
                    {
                        // Insert new Logo
                        $icon_name = "intro_icon_".time().".". $request->file('shop_intro_icon')->getClientOriginalExtension();
                        $request->file('shop_intro_icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/intro_icons/'), $icon_name);
                        $new_icon = $icon_name;

                        $logo_setting = new ClientSettings();
                        $logo_setting->client_id = $clientID;
                        $logo_setting->shop_id = $shop_id;
                        $logo_setting->key = 'shop_intro_icon';
                        $logo_setting->value = $new_icon;
                        $logo_setting->save();
                    }

                }

                return response()->json([
                    'success' => 1,
                    'message' => 'Icon has been Uploaded SuccessFully....',
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Oops, Something Went Wrong !',
                ]);
            }
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error !',
            ]);
        }
    }



    // Function for Cover
    public function cover()
    {
        return view('client.design.cover');
    }


    // Delete Cover
    public function deleteCover()
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $get_intro_icon = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key','shop_intro_icon')->first();
        $setting_id = isset($get_intro_icon->id) ? $get_intro_icon->id : '';
        $shop_intro_icon = isset($get_intro_icon->value) ? $get_intro_icon->value : '';

        if(!empty($shop_intro_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_intro_icon))
        {
            unlink('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_intro_icon);
        }

        if(!empty($setting_id))
        {
            $logo_setting = ClientSettings::find($setting_id);
            $logo_setting->value = "";
            $logo_setting->update();
        }

        return redirect()->route('design.cover')->with('success','Cover has been Removed SuccessFully...');

    }



    // Generel Info View
    public function generalInfo()
    {
        return view('client.design.general_info');
    }


    // Mail Forms View
    public function MailForms()
    {
        return view('client.design.mail_forms');
    }


    // Update General General Info Settings
    public function generalInfoUpdate(Request $request)
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $request->validate([
            'business_name' => 'required',
            'default_currency' => 'required',
        ]);

        $all_data['business_name'] = $request->business_name;
        $all_data['default_currency'] = $request->default_currency;
        $all_data['business_telephone'] = $request->business_telephone;
        $all_data['instagram_link'] = $request->instagram_link;
        $all_data['twitter_link'] = $request->twitter_link;
        $all_data['facebook_link'] = $request->facebook_link;
        $all_data['foursquare_link'] = $request->foursquare_link;
        $all_data['tripadvisor_link'] = $request->tripadvisor_link;
        $all_data['homepage_intro'] = $request->homepage_intro;
        $all_data['map_url'] = $request->map_url;
        $all_data['website_url'] = $request->website_url;
        $all_data['pinterest_link'] = $request->pinterest_link;
        $all_data['delivery_message'] = $request->delivery_message;

        // Update Shop Name
        $shop = Shop::find($shop_id);
        $shop->name = $request->business_name;
        $shop->update();

        // Insert or Update Settings
        foreach($all_data as $key => $value)
        {
            $query = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key',$key)->first();
            $setting_id = isset($query->id) ? $query->id : '';

            if (!empty($setting_id) || $setting_id != '')  // Update
            {
                $settings = ClientSettings::find($setting_id);
                $settings->value = $value;
                $settings->update();
            }
            else // Insert
            {
                $settings = new ClientSettings();
                $settings->client_id = $clientID;
                $settings->shop_id = $shop_id;
                $settings->key = $key;
                $settings->value = $value;
                $settings->save();
            }
        }
        return redirect()->route('design.general-info')->with('success','General Information has been Updated SuccessFully..');
    }


    // Update Mail Form Settings
    public function mailFormUpdate(Request $request)
    {
        $clientID = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $all_data['orders_mail_form_client'] = $request->orders_mail_form_client;
        $all_data['orders_mail_form_customer'] = $request->orders_mail_form_customer;
        $all_data['check_in_mail_form'] = $request->check_in_mail_form;

        // Insert or Update Settings
        foreach($all_data as $key => $value)
        {
            $query = ClientSettings::where('client_id',$clientID)->where('shop_id',$shop_id)->where('key',$key)->first();
            $setting_id = isset($query->id) ? $query->id : '';

            if (!empty($setting_id) || $setting_id != '')  // Update
            {
                $settings = ClientSettings::find($setting_id);
                $settings->value = $value;
                $settings->update();
            }
            else // Insert
            {
                $settings = new ClientSettings();
                $settings->client_id = $clientID;
                $settings->shop_id = $shop_id;
                $settings->key = $key;
                $settings->value = $value;
                $settings->save();
            }
        }
        return redirect()->route('design.mail.forms')->with('success','Mail Forms has been Updated SuccessFully..');
    }
}
