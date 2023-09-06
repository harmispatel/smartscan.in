<?php

namespace App\Http\Controllers;

use App\Models\ClientSettings;
use App\Models\Theme;
use App\Models\ThemeSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $data['shop_id'] = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $data['themes'] = Theme::where('shop_id',$data['shop_id'])->get();
        return view('client.design.theme',$data);
    }


    // Display a listing of the resource.
    public function themePrview($id)
    {

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(!isset($package_permissions['add_edit_clone_theme']) || empty($package_permissions['add_edit_clone_theme']) || $package_permissions['add_edit_clone_theme'] == 0)
        {
            return redirect()->route('client.dashboard')->with('error',"You have not access this Menu");
        }

        // Theme Details
        $theme = Theme::where('id',$id)->first();

        // Keys
        $keys = ([
            'header_color',
            'sticky_header',
            'language_bar_position',
            'logo_position',
            'search_box_position',
            'banner_position',
            'banner_type',
            'banner_slide_button',
            'banner_delay_time',
            'background_color',
            'font_color',
            'label_color',
            'social_media_icon_color',
            'categories_bar_color',
            'menu_bar_font_color',
            'category_title_and_description_color',
            'price_color',
            'item_box_shadow',
            'item_box_shadow_color',
            'item_box_shadow_thickness',
            'item_divider',
            'item_divider_color',
            'item_divider_thickness',
            'item_divider_type',
            'item_divider_position',
            'item_divider_font_color',
            'tag_font_color',
            'tag_label_color',
            'category_bar_type',
            'today_special_icon',
            'theme_preview_image',
            'search_box_icon_color',
            'read_more_link_color',
            'read_more_link_label',
            'banner_height',
            'label_color_transparency',
            'item_box_background_color',
            'item_title_color',
            'item_description_color',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = ThemeSettings::select('value')->where('key',$key)->where('theme_id',$id)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return view('client.design.theme_preview',compact(['settings','theme']));
    }



    // Show the form for creating a new resource.
    public function create()
    {
        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(!isset($package_permissions['add_edit_clone_theme']) || empty($package_permissions['add_edit_clone_theme']) || $package_permissions['add_edit_clone_theme'] == 0)
        {
            return redirect()->route('client.dashboard')->with('error',"You have not access this Menu");
        }

        return view('client.design.new-theme');
    }


    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'theme_name' => 'required',
            'today_special_icon' => 'mimes:png,jpg,svg,gif,jpeg,PNG,SVG,JPG,JPEG,GIF',
            'theme_preview_image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $theme_name = $request->theme_name;

        // Insert New Theme
        $theme = new Theme();
        $theme->shop_id = $shop_id;
        $theme->name = $theme_name;
        $theme->is_default = false;
        $theme->save();

        $setting_keys = [
            'header_color' => $request->header_color,
            'sticky_header' => isset($request->sticky_header) ? $request->sticky_header : 0,
            'language_bar_position' => $request->language_bar_position,
            'logo_position' => $request->logo_position,
            'search_box_position' => $request->search_box_position,
            'banner_position' => $request->banner_position,
            'banner_type' => $request->banner_type,
            'banner_slide_button' => isset($request->banner_slide_button) ? $request->banner_slide_button : 0,
            'banner_delay_time' => $request->banner_delay_time,
            'background_color' => $request->background_color,
            'font_color' => $request->font_color,
            'label_color' => $request->label_color,
            'social_media_icon_color' => $request->social_media_icon_color,
            'categories_bar_color' => $request->categories_bar_color,
            'menu_bar_font_color' => $request->menu_bar_font_color,
            'category_title_and_description_color' => $request->category_title_and_description_color,
            'price_color' => $request->price_color,
            'item_box_shadow' => isset($request->item_box_shadow) ? $request->item_box_shadow : 0,
            'item_box_shadow_color' => $request->item_box_shadow_color,
            'item_box_shadow_thickness' => $request->item_box_shadow_thickness,
            'item_divider' => $request->item_divider,
            'item_divider_color' => $request->item_divider_color,
            'item_divider_thickness' => $request->item_divider_thickness,
            'item_divider_type' => $request->item_divider_type,
            'item_divider_position' => $request->item_divider_position,
            'item_divider_font_color' => $request->item_divider_font_color,
            'tag_font_color' => $request->tag_font_color,
            'tag_label_color' => $request->tag_label_color,
            'category_bar_type' => $request->category_bar_type,
            'search_box_icon_color' => $request->search_box_icon_color,
            'read_more_link_color' => $request->read_more_link_color,
            'read_more_link_label' => $request->read_more_link_label,
            'banner_height' => $request->banner_height,
            'label_color_transparency' => $request->label_color_transparency,
            'item_box_background_color' => $request->item_box_background_color,
            'item_title_color' => $request->item_title_color,
            'item_description_color' => $request->item_description_color,
        ];

        if($request->hasFile('today_special_icon'))
        {
            $imgname = "today_special_icon_".time().".". $request->file('today_special_icon')->getClientOriginalExtension();
            $request->file('today_special_icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/today_special_icon/'), $imgname);
            $setting_keys['today_special_icon'] = $imgname;
        }

        if($request->hasFile('theme_preview_image'))
        {
            $imgname = "theme_preview_image_".time().".". $request->file('theme_preview_image')->getClientOriginalExtension();
            $request->file('theme_preview_image')->move(public_path('client_uploads/shops/'.$shop_slug.'/theme_preview_image/'), $imgname);
            $setting_keys['theme_preview_image'] = $imgname;
        }

        if($theme->id)
        {
            foreach($setting_keys as $key => $val)
            {
                $theme_setting = new ThemeSettings();
                $theme_setting->theme_id = $theme->id;
                $theme_setting->key = $key;
                $theme_setting->value = $val;
                $theme_setting->save();
            }
        }

        return redirect()->route('design.theme')->with('success','New Theme has been Inserted SuccessFully...');

    }



    // Change Current Theme
    public function changeTheme(Request $request)
    {
        $client_id = isset(Auth::user()->id) ? Auth::user()->id : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $theme_id = $request->theme_id;

        $query = ClientSettings::where('shop_id',$shop_id)->where('client_id',$client_id)->where('key','shop_active_theme')->first();
        $setting_id = isset($query->id) ? $query->id : '';

        if(!empty($setting_id))
        {
            // Client's Active Theme
            $active_theme = ClientSettings::find($setting_id);
            $active_theme->value = $theme_id;
            $active_theme->update();
        }
        else
        {
            $active_theme = new ClientSettings();
            $active_theme->client_id = $client_id;
            $active_theme->shop_id = $shop_id;
            $active_theme->key = 'shop_active_theme';
            $active_theme->value = $theme_id;
            $active_theme->save();
        }


        return response()->json([
            'success' => 1,
            'message' => 'Theme has been Activated SuccessFully...',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Theme  $theme
     * @return \Illuminate\Http\Response
     */
    public function edit(Theme $theme)
    {
        //
    }


    // Update the specified resource in storage.
    public function update(Request $request)
    {
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'theme_name' => 'required',
            'today_special_icon' => 'mimes:png,jpg,svg,gif,jpeg,PNG,SVG,JPG,JPEG,GIF',
            'theme_preview_image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        // Theme ID
        $theme_id = $request->theme_id;

        // Update Theme Name
        $theme = Theme::find($theme_id);
        $theme->name = $request->theme_name;
        $theme->update();

        $setting_keys = [
            'header_color' => $request->header_color,
            'sticky_header' => isset($request->sticky_header) ? $request->sticky_header : 0,
            'language_bar_position' => $request->language_bar_position,
            'logo_position' => $request->logo_position,
            'search_box_position' => $request->search_box_position,
            'banner_position' => $request->banner_position,
            'banner_type' => $request->banner_type,
            'banner_slide_button' => isset($request->banner_slide_button) ? $request->banner_slide_button : 0,
            'banner_delay_time' => $request->banner_delay_time,
            'background_color' => $request->background_color,
            'font_color' => $request->font_color,
            'label_color' => $request->label_color,
            'social_media_icon_color' => $request->social_media_icon_color,
            'categories_bar_color' => $request->categories_bar_color,
            'menu_bar_font_color' => $request->menu_bar_font_color,
            'category_title_and_description_color' => $request->category_title_and_description_color,
            'price_color' => $request->price_color,
            'item_box_shadow' => isset($request->item_box_shadow) ? $request->item_box_shadow : 0,
            'item_box_shadow_color' => $request->item_box_shadow_color,
            'item_box_shadow_thickness' => $request->item_box_shadow_thickness,
            'item_divider' => $request->item_divider,
            'item_divider_color' => $request->item_divider_color,
            'item_divider_thickness' => $request->item_divider_thickness,
            'item_divider_type' => $request->item_divider_type,
            'item_divider_position' => $request->item_divider_position,
            'item_divider_font_color' => $request->item_divider_font_color,
            'tag_font_color' => $request->tag_font_color,
            'tag_label_color' => $request->tag_label_color,
            'category_bar_type' => $request->category_bar_type,
            'search_box_icon_color' => $request->search_box_icon_color,
            'read_more_link_color' => $request->read_more_link_color,
            'read_more_link_label' => $request->read_more_link_label,
            'banner_height' => $request->banner_height,
            'label_color_transparency' => $request->label_color_transparency,
            'item_box_background_color' => $request->item_box_background_color,
            'item_title_color' => $request->item_title_color,
            'item_description_color' => $request->item_description_color,
        ];

        if($request->hasFile('today_special_icon'))
        {
            $imgname = "today_special_icon_".time().".". $request->file('today_special_icon')->getClientOriginalExtension();
            $request->file('today_special_icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/today_special_icon/'), $imgname);
            $setting_keys['today_special_icon'] = $imgname;
        }

        if($request->hasFile('theme_preview_image'))
        {
            $imgname = "theme_preview_image_".time().".". $request->file('theme_preview_image')->getClientOriginalExtension();
            $request->file('theme_preview_image')->move(public_path('client_uploads/shops/'.$shop_slug.'/theme_preview_image/'), $imgname);
            $setting_keys['theme_preview_image'] = $imgname;
        }

        // Update Theme Settings
        foreach($setting_keys as $key => $value)
        {
            $query = ThemeSettings::where('key',$key)->where('theme_id',$theme_id)->first();
            $setting_id = isset($query->id) ? $query->id : '';

            // Update
            if(!empty($setting_id) || $setting_id != '')
            {
                $settings = ThemeSettings::find($setting_id);
                $settings->value = $value;
                $settings->update();
            }
            else
            {
                $settings = new ThemeSettings();
                $settings->theme_id = $theme_id;
                $settings->key = $key;
                $settings->value = $value;
                $settings->save();
            }
        }

        return redirect()->back()->with('success', 'Theme Settings has been Changed SuccessFully...');
    }



    // Remove the specified resource from storage.
    public function destroy($id)
    {
        // Delete Theme Settings
        ThemeSettings::where('theme_id',$id)->delete();

        // Delete Theme
        Theme::where('id',$id)->delete();

        return redirect()->route('design.theme')->with('success','Theme has been Removed SuccessFully..');
    }


    // Clone Theme View
    public function cloneView($id)
    {
        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(!isset($package_permissions['add_edit_clone_theme']) || empty($package_permissions['add_edit_clone_theme']) || $package_permissions['add_edit_clone_theme'] == 0)
        {
            return redirect()->route('client.dashboard')->with('error',"You have not access this Menu");
        }

        // Theme Details
        $theme = Theme::where('id',$id)->first();

        // Keys
        $keys = ([
            'header_color',
            'sticky_header',
            'language_bar_position',
            'logo_position',
            'search_box_position',
            'banner_position',
            'banner_type',
            'banner_slide_button',
            'banner_delay_time',
            'background_color',
            'font_color',
            'label_color',
            'social_media_icon_color',
            'categories_bar_color',
            'menu_bar_font_color',
            'category_title_and_description_color',
            'price_color',
            'item_box_shadow',
            'item_box_shadow_color',
            'item_box_shadow_thickness',
            'item_divider',
            'item_divider_color',
            'item_divider_thickness',
            'item_divider_type',
            'item_divider_position',
            'item_divider_font_color',
            'tag_font_color',
            'tag_label_color',
            'category_bar_type',
            'today_special_icon',
            'theme_preview_image',
            'search_box_icon_color',
            'read_more_link_color',
            'read_more_link_label',
            'banner_height',
            'label_color_transparency',
            'item_box_background_color',
            'item_title_color',
            'item_description_color',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = ThemeSettings::select('value')->where('key',$key)->where('theme_id',$id)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return view('client.design.theme.clone',compact(['theme','settings']));
    }
}
