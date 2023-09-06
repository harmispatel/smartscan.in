<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\{AdditionalLanguage,Category,CategoryImages,CategoryProductTags,CategoryVisit,CheckIn,Clicks,ClientSettings,DeliveryAreas,Ingredient,ItemPrice,ItemReview,Items,ItemsVisit,Languages,LanguageSettings,Option,OptionPrice,Order,OrderItems,OrderSetting,PaymentSettings,QrSettings,Shop,ShopBanner,Subscriptions,Tags,Theme,ThemeSettings,User,UserShop,UsersSubscriptions,UserVisits};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,File,Hash,URL};
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    public function index()
    {
        $settings = getAdminSettings();
        $favourite_client_limit = isset($settings['favourite_client_limit']) ? $settings['favourite_client_limit'] : 5;

        $data['clients'] = FavClients($favourite_client_limit);
        return view('admin.clients.clients',$data);
    }



    public function clientsList($id="")
    {
        $settings = getAdminSettings();
        // $favourite_client_limit = isset($settings['favourite_client_limit']) ? $settings['favourite_client_limit'] : 5;

        if(empty($id))
        {
            $data['clients'] = User::with(['hasOneShop','hasOneSubscription'])->where('user_type',2)->get();
        }
        else
        {
            if(is_numeric($id))
            {
                $data['clients'] = User::with(['hasOneShop','hasOneSubscription'])->where('id',$id)->get();
            }
            else
            {
                $data['clients'] = User::with(['hasOneShop','hasOneSubscription'])->whereHas('hasOneSubscription', function($q) use ($id){
                    $q->whereHas('subscription', function($r) use ($id)
                    {
                        $r->where('name',$id);
                    });
                })->get();
            }
        }

        $data['filter_id'] = $id;
        $data['subscriptions'] = Subscriptions::where('status',1)->get();

        return view('admin.clients.clients_list',$data);
    }



    public function insert()
    {
        $data['subscriptions'] = Subscriptions::where('status',1)->get();
        $data['languages'] = Languages::get();
        return view('admin.clients.new_clients',$data);
    }



    public function store(ClientRequest $request)
    {
        $subscription_id = $request->subscription;
        $primary_language = $request->primary_language;

        $subscription = Subscriptions::where('id',$subscription_id)->first();
        $subscription_duration = isset($subscription->duration) ? $subscription->duration : '';

        $date = Carbon::now();
        $current_date = $date->toDateTimeString();
        $end_date = $date->addMonths($subscription_duration)->toDateTimeString();
        $duration = $subscription_duration.' Months.';

        $firstname = $request->firstname;
        $lastname = $request->lastname;
        $email = $request->email;
        $password = Hash::make($request->password);
        $status = (isset($request->status)) ? $request->status : 0;
        $shop_name = $request->shop_name;
        $shop_slug = $request->shop_url;
        $shop_description = $request->shop_description;

        // Insert New Client
        $client = new User();
        $client->firstname = $firstname;
        $client->lastname = $lastname;
        $client->email = $email;
        $client->password = $password;
        $client->status = $status;
        $client->user_type = 2;
        $client->is_fav = (isset($request->favourite)) ? $request->favourite : 0;
        $client->save();

        if($client->id)
        {
            // Insert Client Shop
            $shop = new Shop();
            $shop->name = $shop_name;
            $shop->shop_slug = $shop_slug;
            $shop->description = $shop_description;

            // Make Shop Directory
            mkdir(public_path('client_uploads/shops/'.$shop_slug));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/banners"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/ingredients"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/categories"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/intro_icons"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/items"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/theme_preview_image"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/today_special_icon"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/top_logos"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/tables"));
            mkdir(public_path('client_uploads/shops/'.$shop_slug."/rooms"));


            if($request->hasFile('shop_logo'))
            {
                $path = public_path('client_uploads/shops/').$shop_slug."/";
                $imgname = time().".". $request->file('shop_logo')->getClientOriginalExtension();
                $request->file('shop_logo')->move($path, $imgname);
                $imageurl = asset('public/client_uploads/shops/'.$shop_slug).'/'.$imgname;
                $shop->logo = $imageurl;

                $shop->directory = $path;
            }
            $shop->save();


            // Shop's Default Currency
            $default_currency = new ClientSettings();
            $default_currency->client_id = $client->id;
            $default_currency->shop_id = $shop->id;
            $default_currency->key = 'default_currency';
            $default_currency->value = "INR";
            $default_currency->save();

            // Business Name
            $business_name = new ClientSettings();
            $business_name->client_id = $client->id;
            $business_name->shop_id = $shop->id;
            $business_name->key = 'business_name';
            $business_name->value = $shop_name;
            $business_name->save();
            // Generate Shop Qr
            $new_shop_url = URL::to('/')."/".$shop_slug;
            $qr_name = $shop_slug."_".time()."_qr.png";
            $upload_path = public_path('admin_uploads/shops_qr/'.$qr_name);

            QrCode::format('png')->margin(2)->size(200)->generate($new_shop_url, $upload_path);

            // Insert Qr Code Settings
            $qrdata = [
                'qr_size' => '200',
                'qr_style' => 'square',
                'eye_style' => 'square',
                'color_type' => '',
                'color_transparent' => 100,
                'background_color_transparent' => 100,
                'eye_inner_color' => "#000000",
                'eye_outer_color' => "#000000",
                'first_color' => "#000000",
                'second_color' => "#000000",
                'background_color' => "#ffffff",
            ];

            $qr_setting = new QrSettings();
            $qr_setting->shop_id = $shop->id;
            $qr_setting->value = serialize($qrdata);
            $qr_setting->save();

            // Update Shop Details
            $update_shop_dt = Shop::find($shop->id);
            $update_shop_dt->qr_code = $qr_name;
            $update_shop_dt->update();

            // Insert Default Themes
            $def_themes = [
                'Default Light Theme',
                'Default Dark Theme',
            ];

            foreach ($def_themes as $key => $value)
            {
                $theme = new Theme();
                $theme->shop_id = $shop->id;
                $theme->name = $value;
                $theme->is_default = 1;
                $theme->save();

                // Insert Theme Settings
                if($value == 'Default Light Theme')
                {
                    $setting_keys = [
                        'header_color' => '#ffffff',
                        'sticky_header' => 1,
                        'language_bar_position' => 'left',
                        'logo_position' => 'center',
                        'search_box_position' => 'right',
                        'banner_position' => 'top',
                        'banner_type' => 'image',
                        'banner_slide_button' => 1,
                        'banner_delay_time' => 3000,
                        'background_color' => '#ffffff',
                        'font_color' => '#4d572b',
                        'label_color' => '#ffffff',
                        'social_media_icon_color' => '#4d572b',
                        'categories_bar_color' => '#ffffff',
                        'menu_bar_font_color' => '#4d572b',
                        'category_title_and_description_color' => '#4d572b',
                        'price_color' => '#000000',
                        'item_box_shadow' => 1,
                        'item_box_shadow_color' => '#d1ccb8',
                        'item_box_shadow_thickness' => '5px',
                        'item_divider' => 1,
                        'item_divider_color' => '#000000',
                        'item_divider_thickness' => '5',
                        'item_divider_type' => 'solid',
                        'item_divider_position' => 'top',
                        'item_divider_font_color' => '#4d572b',
                        'tag_font_color' => '#4d572b',
                        'tag_label_color' => '#ffffff',
                        'category_bar_type' => '8px',
                        'search_box_icon_color' => '#000000',
                        'read_more_link_color' => '#0000ff',
                        'read_more_link_label' => 'Read More',
                        'banner_height' => '350',
                        'label_color_transparency' => 1,
                        'item_box_background_color' => '#ffffff',
                        'item_title_color' => '#4d572b',
                        'item_description_color' => '#000000',
                    ];

                    foreach($setting_keys as $key => $val)
                    {
                        $theme_setting = new ThemeSettings();
                        $theme_setting->theme_id = $theme->id;
                        $theme_setting->key = $key;
                        $theme_setting->value = $val;
                        $theme_setting->save();
                    }

                    // Client's Active Theme
                    $active_theme = new ClientSettings();
                    $active_theme->client_id = $client->id;
                    $active_theme->shop_id = $shop->id;
                    $active_theme->key = 'shop_active_theme';
                    $active_theme->value = $theme->id;
                    $active_theme->save();
                }
                else
                {
                    $setting_keys = [
                        'header_color' => '#000000',
                        'sticky_header' => 1,
                        'language_bar_position' => 'left',
                        'logo_position' => 'center',
                        'search_box_position' => 'right',
                        'banner_position' => 'top',
                        'banner_type' => 'image',
                        'banner_slide_button' => 1,
                        'banner_delay_time' => 3000,
                        'background_color' => '#000000',
                        'font_color' => '#ffffff',
                        'label_color' => '#000000',
                        'social_media_icon_color' => '#ffffff',
                        'categories_bar_color' => '#000000',
                        'menu_bar_font_color' => '#E7B76B',
                        'category_title_and_description_color' => '#ffffff',
                        'price_color' => '#E7B76B',
                        'item_box_shadow' => 1,
                        'item_box_shadow_color' => '#E7B76B',
                        'item_box_shadow_thickness' => '5px',
                        'item_divider' => 1,
                        'item_divider_color' => '#ffffff',
                        'item_divider_thickness' => '3',
                        'item_divider_type' => 'dotted',
                        'item_divider_position' => 'bottom',
                        'item_devider_font_color' => '#ffffff',
                        'tag_font_color' => '#ffffff',
                        'tag_label_color' => '#000000',
                        'search_box_icon_color' => '#ffffff',
                        'read_more_link_color' => '#9f9f9f',
                        'read_more_link_label' => 'Read More',
                        'banner_height' => '350',
                        'label_color_transparency' => 1,
                        'item_box_background_color' => '#000000',
                        'item_title_color' => '#ffffff',
                        'item_description_color' => '#ffffff',
                    ];

                    foreach($setting_keys as $key => $val)
                    {
                        $theme_setting = new ThemeSettings();
                        $theme_setting->theme_id = $theme->id;
                        $theme_setting->key = $key;
                        $theme_setting->value = $val;
                        $theme_setting->save();
                    }
                }

            }

            // Insert Order Settings
            $order_settings_keys = [
                'delivery' => 0,
                'takeaway' => 0,
                'room_delivery' => 0,
                'table_service' => 0,
                'only_cart' => 0,
                'auto_order_approval' => 0,
                'scheduler_active' => 0,
                'min_amount_for_delivery' => '',
                'discount_percentage' => '',
                'order_arrival_minutes' => 30,
                'notification_sound' => 'buzzer-01.mp3',
                'play_sound' => 0,
                'auto_print' => 0,
                'email_field' => 1,
                'floor_field' => 1,
                'door_bell_field' => 1,
                'full_name_field' => 0,
                'instructions_field' => 1,
                'live_address_field' => 1,
                'schedule_array' => '{"sunday":{"name":"Sun","enabled":false,"dayInWeek":0,"timesSchedules":[{"startTime":"","endTime":""}]},"monday":{"name":"Mon","enabled":false,"dayInWeek":1,"timesSchedules":[{"startTime":"","endTime":""}]},"tuesday":{"name":"Tue","enabled":false,"dayInWeek":2,"timesSchedules":[{"startTime":"","endTime":""}]},"wednesday":{"name":"Wed","enabled":false,"dayInWeek":3,"timesSchedules":[{"startTime":"","endTime":""}]},"thursday":{"name":"Thu","enabled":false,"dayInWeek":4,"timesSchedules":[{"startTime":"","endTime":""}]},"friday":{"name":"Fri","enabled":false,"dayInWeek":5,"timesSchedules":[{"startTime":"","endTime":""}]},"saturday":{"name":"Sat","enabled":false,"dayInWeek":6,"timesSchedules":[{"startTime":"","endTime":""}]}}',
            ];
            foreach($order_settings_keys as $key => $value)
            {
                $settings = new OrderSetting();
                $settings->shop_id = $shop->id;
                $settings->key = $key;
                $settings->value = $value;
                $settings->save();
            }


            // Insert Payment Settings
            $payment_settings_keys = [
                'paypal' => 0,
                'paypal_mode' => 'sandbox',
                'paypal_public_key' => '',
                'paypal_private_key' => '',
                'every_pay' => 0,
                'everypay_mode' => 1,
                'every_pay_public_key' => '',
                'every_pay_private_key' => '',
            ];
            foreach($payment_settings_keys as $key => $value)
            {
                $settings = new PaymentSettings();
                $settings->shop_id = $shop->id;
                $settings->key = $key;
                $settings->value = $value;
                $settings->save();
            }


            // Add Client Default Language
            $primary_lang = new LanguageSettings();
            $primary_lang->shop_id = $shop->id;
            $primary_lang->key = "primary_language";
            $primary_lang->value = $primary_language;
            $primary_lang->save();


            // Add Special Icon From Admin To Client
            $admin_special_icons = Ingredient::where('shop_id',NULL)->get();

            if(count($admin_special_icons) > 0)
            {
                foreach($admin_special_icons as $sp_icon)
                {
                    $sp_icon_id = (isset($sp_icon['id'])) ? $sp_icon['id'] : '';
                    $sp_icon_name = (isset($sp_icon['name'])) ? $sp_icon['name'] : '';
                    $sp_icon_status = (isset($sp_icon['status'])) ? $sp_icon['status'] : 0;
                    $sp_icon_image = (isset($sp_icon['icon'])) ? $sp_icon['icon'] : '';

                    $new_special_icon = new Ingredient();
                    $new_special_icon->shop_id = $shop->id;
                    $new_special_icon->parent_id = $sp_icon_id;
                    $new_special_icon->name = $sp_icon_name;
                    $new_special_icon->status = $sp_icon_status;
                    $new_special_icon->icon = $sp_icon_image;
                    $new_special_icon->save();

                    if(!empty($sp_icon_image) && file_exists('public/admin_uploads/ingredients/'.$sp_icon_image))
                    {
                        File::copy(public_path('admin_uploads/ingredients/'.$sp_icon_image), public_path('client_uploads/shops/'.$shop->shop_slug.'/ingredients/'.$sp_icon_image));
                    }
                }
            }

            // Insert User Subscriptions
            if($subscription_id)
            {
                $user_subscription = new UsersSubscriptions();
                $user_subscription->user_id = $client->id;
                $user_subscription->subscription_id = $subscription_id;
                $user_subscription->duration = $duration;
                $user_subscription->start_date = $current_date;
                $user_subscription->end_date = $end_date;
                $user_subscription->save();
            }

        }

        if($client->id && $shop->id)
        {
            $userShop = new UserShop();
            $userShop->user_id = $client->id;
            $userShop->shop_id = $shop->id;
            $userShop->save();
        }

        return redirect()->route('clients.list')->with('success','Client has been Inserted SuccessFully....');

    }



    public function update(ClientRequest $request)
    {
        $subscription_id = $request->subscription;

        $subscription = Subscriptions::where('id',$subscription_id)->first();
        $subscription_duration = isset($subscription->duration) ? $subscription->duration : '';

        $date = Carbon::now();
        $current_date = $date->toDateTimeString();
        $end_date = $date->addMonths($subscription_duration)->toDateTimeString();
        $duration = $subscription_duration.' Months.';

        $firstname = $request->firstname;
        $lastname = $request->lastname;
        $email = $request->email;
        $password = Hash::make($request->password);
        $status = isset($request->status) ? $request->status : 0;
        $shop_id = $request->shop_id;
        $shop_name = $request->shop_name;
        $shop_description = $request->shop_description;

        // Update New Client
        $client = User::find($request->client_id);
        $client->firstname = $firstname;
        $client->lastname = $lastname;
        $client->email = $email;
        $client->is_fav = (isset($request->favourite)) ? $request->favourite : 0;

        if(!empty($request->password))
        {
            $client->password = $password;
        }

        $client->status = $status;
        $client->update();


        // Update User Subscriptions
        if(!empty($subscription_id ) && !empty($request->user_sub_id))
        {
            $user_subscription = UsersSubscriptions::find($request->user_sub_id);
            $user_subscription->subscription_id = $subscription_id;
            $user_subscription->duration = $duration;
            $user_subscription->start_date = $current_date;
            $user_subscription->end_date = $end_date;
            $user_subscription->update();
        }


        // Update Client Shop
        $shop = Shop::find($shop_id);
        $shop->name = $shop_name;
        $shop->description = $shop_description;

        if($request->hasFile('shop_logo'))
        {
            $old_image = isset($shop->logo) ? $shop->logo : '';
            if(!empty($old_image))
            {
                if(file_exists($old_image))
                {
                    unlink($old_image);
                }
            }

            $path = public_path('admin_uploads/shops/').$shop->shop_slug."/";
            $imgname = time().".". $request->file('shop_logo')->getClientOriginalExtension();
            $request->file('shop_logo')->move($path, $imgname);
            $imageurl = asset('public/admin_uploads/shops/'.$shop->shop_slug).'/'.$imgname;
            $shop->logo = $imageurl;
            $shop->directory = $path;
        }
        $shop->update();

        return redirect()->route('clients.list')->with('success','Client has been Updated SuccessFully....');
    }



    public function destroy(Request $request)
    {
        $id = $request->id;

        try
        {
            // Get User Details
            $user = User::with(['hasOneShop'])->where('id',$id)->first();
            $shop_id = isset($user->hasOneShop->shop['id']) ? $user->hasOneShop->shop['id'] : '';

            if(!empty($shop_id))
            {
                $shop = Shop::where('id',$shop_id)->first();
                $shop_slug = $shop->shop_slug;
                $shop_directory = public_path('client_uploads/shops/'.$shop_slug);
                if(!empty($shop_directory))
                {
                    File::deleteDirectory($shop_directory);
                }

                // Delete Shop Additional Languages
                AdditionalLanguage::where('shop_id',$shop_id)->delete();

                // Categories Array
                $categories = Category::select('id')->where('shop_id',$shop_id)->get();
                $cat_arr = [];

                if(count($categories) > 0)
                {
                    foreach($categories as $cat)
                    {
                        $cat_arr[] = $cat->id;
                    }
                }

                $cat_arr = (count($cat_arr) > 0) ? array_unique($cat_arr) : [];


                // Theme Array
                $themes = Theme::select('id')->where('shop_id',$shop_id)->get();
                $theme_arr = [];

                if(count($themes) > 0)
                {
                    foreach($themes as $theme)
                    {
                        $theme_arr[] = $theme->id;
                    }
                }

                $theme_arr = (count($theme_arr) > 0) ? array_unique($theme_arr) : [];

                // Delete Shop Category Tags
                CategoryProductTags::whereIn('category_id',$cat_arr)->delete();

                // Delete Shop Theme Settings
                ThemeSettings::whereIn('theme_id',$theme_arr)->delete();

                // Delete Shop Categories
                Category::where('shop_id',$shop_id)->delete();

                // Delete Category Images
                CategoryImages::whereIn('category_id',$cat_arr)->delete();

                // Delete Category Visits
                CategoryVisit::where('shop_id',$shop_id)->delete();

                // Delete Shop Items
                Items::where('shop_id',$shop_id)->delete();

                // Delete Item Prices
                ItemPrice::where('shop_id',$shop_id)->delete();

                // Delete Item Visits
                ItemsVisit::where('shop_id',$shop_id)->delete();

                // Delete Item Reviews
                ItemReview::where('shop_id',$shop_id)->delete();

                // Delete Language Settings
                LanguageSettings::where('shop_id',$shop_id)->delete();

                // Delete Order Settings
                OrderSetting::where('shop_id',$shop_id)->delete();

                // Delete Payment Settings
                PaymentSettings::where('shop_id',$shop_id)->delete();

                // Delete Orders
                Order::where('shop_id',$shop_id)->delete();

                // Delete Order Items
                OrderItems::where('shop_id',$shop_id)->delete();

                // Delete QR Settings
                QrSettings::where('shop_id',$shop_id)->delete();

                // Delete Shop Banners
                ShopBanner::where('shop_id',$shop_id)->delete();

                // Delete Shop Settings
                ClientSettings::where('shop_id',$shop_id)->delete();

                // Delete Shop Themes
                Theme::where('shop_id',$shop_id)->delete();

                // Delete Shop
                Shop::where('id',$shop_id)->delete();

                // Tags Delete
                Tags::where('shop_id',$shop_id)->delete();

                // Delete Users Visits
                UserVisits::where('shop_id',$shop_id)->delete();

                // Delete Options
                Option::where('shop_id',$shop_id)->delete();

                // Delete Option Prices
                OptionPrice::where('shop_id',$shop_id)->delete();

                // Delete Clicks
                Clicks::where('shop_id',$shop_id)->delete();

                // Delete CheckIns History
                CheckIn::where('shop_id',$shop_id)->delete();

                // Delete Delivery Areas
                DeliveryAreas::where('shop_id',$shop_id)->delete();

                // Delete Client Ingredients
                Ingredient::where('shop_id',$shop_id)->delete();

            }

            // Delete UserShop
            UserShop::where('user_id',$id)->delete();

            // Delete User
            User::where('id',$id)->delete();

            // Delete Users Subscription
            UsersSubscriptions::where('user_id',$id)->delete();

            return response()->json([
                'success' => 1,
                'message' => "Client has been Removed SuccessFully..",
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


    public function clientAccess($userID)
    {
        Auth::loginUsingId($userID);
        return redirect()->route('login');
    }



    public function edit($id)
    {
       try
       {
            $data['client'] = User::with(['hasOneShop','hasOneSubscription'])->where('id',$id)->first();
            $data['subscriptions'] = Subscriptions::where('status',1)->get();

            if($data['client'])
            {
                return view('admin.clients.edit_clients',$data);
            }
            return redirect()->route('clients')->with('error', 'Something went wrong!');
       }
       catch (\Throwable $th)
       {
            return redirect()->route('clients')->with('error', 'Something went wrong!');
       }
    }



    public function editProfile($id)
    {
        if(Auth::user()->user_type == 1)
        {
            $data['user'] = User::where('id',decrypt($id))->first();
            return view('auth.admin-profile-edit',$data);
        }
        else
        {
            $data['user'] = User::with(['hasOneShop','hasOneSubscription'])->where('id',decrypt($id))->first();
            return view('auth.client-profile-edit',$data);
        }
    }


    public function myProfile($id)
    {
        if(Auth::user()->user_type == 1)
        {
            $data['user'] = User::where('id',decrypt($id))->first();
            return view('auth.admin-profile',$data);
        }
        else
        {
            $data['user'] = User::with(['hasOneShop','hasOneSubscription'])->where('id',decrypt($id))->first();
            return view('auth.client-profile',$data);
        }
    }


    public function updateProfile(Request $request)
    {
        $user  = User::find($request->user_id);

        if(Auth::user()->user_type == 1)
        {
            $request->validate([
                'firstname'             =>      'required',
                'email'                 =>      'required|email|unique:users,email,'.$request->user_id,
                'confirm_password'      =>      'same:password',
                'profile_picture'       =>      'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG'
            ]);

            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;

            if(!empty($request->password))
            {
                $user->password = Hash::make($request->password);
            }

            if($request->hasFile('profile_picture'))
            {
                // Remove Old Image
                $old_image = isset($user->image) ? $user->image : '';
                if(!empty($old_image) && file_exists($old_image))
                {
                    unlink($old_image);
                }

                // Insert New Image
                $imgname = time().".". $request->file('profile_picture')->getClientOriginalExtension();
                $request->file('profile_picture')->move(public_path('admin_uploads/users/'), $imgname);
                $imageurl = asset('/').'public/admin_uploads/users/'.$imgname;
                $user->image = $imageurl;
            }

            $user->update();
            return redirect()->route('admin.profile.view',encrypt($request->user_id))->with('success','Profile has been Updated SuccessFully..');
        }
        else
        {
            $request->validate([
                'shop_name'             =>      'required',
                'firstname'             =>      'required',
                'email'                 =>      'required|email|unique:users,email,'.$request->user_id,
                'confirm_password'      =>      'same:password',
                'profile_picture'       =>      'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
                'shop_logo'             =>      'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
            ]);

            $explode_emails = explode(',',str_replace(' ','',$request->contact_emails));
            $contact_emails = serialize($explode_emails);

            // User Update
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->contact_emails = $contact_emails;

            if(!empty($request->password))
            {
                $user->password = Hash::make($request->password);
            }

            if($request->hasFile('profile_picture'))
            {
                // Remove Old Image
                $old_image = isset($user->image) ? $user->image : '';
                if(!empty($old_image) && file_exists($old_image))
                {
                    unlink($old_image);
                }

                // Insert New Image
                $imgname = time().".". $request->file('profile_picture')->getClientOriginalExtension();
                $request->file('profile_picture')->move(public_path('admin_uploads/users/'), $imgname);
                $imageurl = asset('/').'public/admin_uploads/users/'.$imgname;
                $user->image = $imageurl;
            }
            $user->update();

            // Shop Update
            $shop_id = isset($user->hasOneShop->shop['id']) ? $user->hasOneShop->shop['id'] : '';

            // Update Shop name in Client Settings
            $shop_name_query = ClientSettings::where('client_id',$request->user_id)->where('shop_id',$shop_id)->where('key','business_name')->first();
            $shop_name_setting_id = isset($shop_name_query->id) ? $shop_name_query->id : '';
            if (!empty($shop_name_setting_id) || $shop_name_setting_id != '')  // Update
            {
                $settings = ClientSettings::find($shop_name_setting_id);
                $settings->value = $request->shop_name;
                $settings->update();
            }
            else // Insert
            {
                $settings = new ClientSettings();
                $settings->client_id = $request->user_id;
                $settings->shop_id = $shop_id;
                $settings->key = 'business_name';
                $settings->value = $request->shop_name;
                $settings->save();
            }


            if(!empty($shop_id))
            {
                $shop = Shop::find($shop_id);
                $shop->name = $request->shop_name;
                $shop->description = $request->shop_description;

                if($request->hasFile('shop_logo'))
                {
                    $old_image = isset($shop->logo) ? $shop->logo : '';
                    if(!empty($old_image))
                    {
                        if(file_exists($old_image))
                        {
                            unlink($old_image);
                        }
                    }

                    $path = public_path('admin_uploads/shops/').$shop->shop_slug."/";
                    $imgname = time().".". $request->file('shop_logo')->getClientOriginalExtension();
                    $request->file('shop_logo')->move($path, $imgname);
                    $imageurl = asset('public/admin_uploads/shops/'.$shop->shop_slug).'/'.$imgname;
                    $shop->logo = $imageurl;
                    $shop->directory = $path;
                }
                $shop->update();
            }

            return redirect()->route('client.profile.view',encrypt($request->user_id))->with('success','Profile has been Updated SuccessFully..');
        }

    }


    public function deleteProfilePicture()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        if($user)
        {
            $user_image = isset($user->image) ? $user->image : '';
            if(!empty($user_image))
            {
                $new_path = str_replace(asset('/public/'),public_path(),$user_image);
                if(file_exists($new_path))
                {
                    unlink($new_path);
                }
            }

            $user->image = "";
        }

        $user->update();

        return redirect()->back()->with('success', "Profile Picture has been Removed SuccessFully..");
    }



    public function changeStatus(Request $request)
    {
        // Client ID & Status
        $client_id = $request->id;
        $status = $request->status;

        try
        {
            $client = User::find($client_id);
            $client->status = $status;
            $client->update();

            return response()->json([
                'success' => 1,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
            ]);
        }
    }



    public function addToFavClients(Request $request)
    {
        // Client ID & isFav
        $client_id = $request->id;
        $status = $request->status;

        try
        {
            $client = User::find($client_id);
            $client->is_fav = $status;
            $client->update();

            return response()->json([
                'success' => 1,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
            ]);
        }
    }



    // Admin Users
    public function AdminUsers()
    {
        $data['users'] = User::where('user_type',1)->get();
        return view('admin.admins.admins',$data);
    }



    // New Admin Users
    public function NewAdminUser()
    {
        return view('admin.admins.new_admin');
    }



    // Store Admin Users
    public function storeNewAdmin(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'user_image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        $firstname = $request->firstname;
        $lastname = $request->lastname;
        $email = $request->email;
        $password = Hash::make($request->password);
        $status = isset($request->status) ? $request->status : 0;

        $user = new User();
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->password = $password;
        $user->user_type = 1;
        $user->status = $status;

        if($request->hasFile('user_image'))
        {
            // Insert New Image
            $imgname = time().".". $request->file('user_image')->getClientOriginalExtension();
            $request->file('user_image')->move(public_path('admin_uploads/users/'), $imgname);
            $imageurl = asset('/').'public/admin_uploads/users/'.$imgname;
            $user->image = $imageurl;
        }

        $user->save();

        return redirect()->route('admins')->with('success','Admin has been Inserted SuccessFully....');

    }



    // Delete Admin Users
    public function destroyAdminUser($id)
    {
        // Get User Details
        $user = User::where('id',$id)->first();
        $user_image = isset($user->image) ? $user->image : '';
        if(!empty($user_image) && file_exists($user_image))
        {
            unlink($user_image);
        }

        // Delete User
        User::where('id',$id)->delete();

        return redirect()->route('admins')->with('success','Admin has been Removed SuccessFully..');
    }



    // Edit Admin Users
    public function editAdmin($id)
    {
       try
       {
            $data['user'] = User::where('id',$id)->first();

            if($data['user'])
            {
                return view('admin.admins.edit_admin',$data);
            }
            return redirect()->route('admins')->with('error', 'Something went wrong!');
       }
       catch (\Throwable $th)
       {
            return redirect()->route('admins')->with('error', 'Something went wrong!');
       }
    }



    // Update Admin
    public function updateAdmin(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
            'confirm_password' => 'same:password',
            'user_image' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ]);

        $firstname = $request->firstname;
        $lastname = $request->lastname;
        $email = $request->email;
        $status = isset($request->status) ? $request->status : 0;

        $user = User::find($request->user_id);
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;

        if(!empty($request->password))
        {
            $password = Hash::make($request->password);
            $user->password = $password;
        }

        $user->status = $status;

        if($request->hasFile('user_image'))
        {
            // Insert New Image
            $imgname = time().".". $request->file('user_image')->getClientOriginalExtension();
            $request->file('user_image')->move(public_path('admin_uploads/users/'), $imgname);
            $imageurl = asset('/').'public/admin_uploads/users/'.$imgname;
            $user->image = $imageurl;
        }

        $user->update();

        return redirect()->route('admins')->with('success','Admin has been Updated SuccessFully....');
    }



    // Delete Clients Data
    public function deleteClientsData(Request $request)
    {
        $shop_id = $request->shop_id;

        try
        {
            // Categories Array
            $categories = Category::select('id')->where('shop_id',$shop_id)->get();
            $cat_arr = [];

            if(count($categories) > 0)
            {
                foreach($categories as $cat)
                {
                    $cat_arr[] = $cat->id;
                }
            }

            $cat_arr = (count($cat_arr) > 0) ? array_unique($cat_arr) : [];

            // Delete Shop Category Tags
            CategoryProductTags::whereIn('category_id',$cat_arr)->delete();

            // Delete Category Images
            CategoryImages::whereIn('category_id',$cat_arr)->delete();

            // Delete Shop Categories
            Category::where('shop_id',$shop_id)->delete();

            // Delete Shop Items
            Items::where('shop_id',$shop_id)->delete();

            // Item Prices
            ItemPrice::where('shop_id',$shop_id)->delete();

            // Tags Delete
            Tags::where('shop_id',$shop_id)->delete();

            // Category Visits
            CategoryVisit::where('shop_id',$shop_id)->delete();

            // Items Visits
            ItemsVisit::where('shop_id',$shop_id)->delete();

            // Review Delete
            ItemReview::where('shop_id',$shop_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Data has been Deleted SuccessFully...',
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


    // Delete Clients Orders
    public function deleteClientsOrders(Request $request)
    {
        $shop_id = $request->shop_id;

        try
        {
            // Delete Client Orders
            OrderItems::where('shop_id',$shop_id)->delete();
            Order::where('shop_id',$shop_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Order has been Deleted SuccessFully...',
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


    // Verify Client Password
    function verifyClientPassword(Request $request)
    {
        try
        {
            $user_password = (isset(Auth::user()->password)) ? Auth::user()->password : '';
            $current_password = $request->password;

            if(Hash::check($current_password,$user_password))
            {
                return response()->json([
                    'success' => 1,
                    'matched' => 1,
                    'message' => 'Matched SuccessFully....',
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 1,
                    'matched' => 0,
                    'message' => 'Password does not Match!',
                ]);
            }

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
