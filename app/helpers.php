<?php

    use App\Models\{AdminSettings, Category, CategoryProductTags,ClientSettings, DeliveryAreas, Ingredient,ItemPrice, Items, Languages,LanguageSettings, OrderSetting, PaymentSettings, ShopBanner,Subscriptions,ThemeSettings,User,UserShop,UsersSubscriptions,Shop};
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    // Get Admin's Settings
    function getAdminSettings()
    {
        // Keys
        $keys = ([
            'favourite_client_limit',
            'copyright_text',
            'logo',
            'login_form_background',
            'default_light_theme_image',
            'default_dark_theme_image',
            'theme_main_screen_demo',
            'theme_category_screen_demo',
            'default_special_item_image',
            'contact_us_email',
            'google_map_api',
            'contact_us_mail_template',
            'subscription_expire_mail',
            'days_for_send_first_expiry_mail',
            'days_for_send_second_expiry_mail',
            'subscription_expiry_mails',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = AdminSettings::select('value')->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Client's Settings
    function getClientSettings($shopID="")
    {

        if(!empty($shopID))
        {
            $shop_id = $shopID;
        }
        else
        {
            $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        }

        // Keys
        $keys = ([
            'shop_view_header_logo',
            'shop_intro_icon',
            'intro_icon_status',
            'intro_icon_duration',
            'business_name',
            'default_currency',
            'business_telephone',
            'instagram_link',
            'pinterest_link',
            'twitter_link',
            'facebook_link',
            'foursquare_link',
            'tripadvisor_link',
            'homepage_intro',
            'map_url',
            'website_url',
            'shop_active_theme',
            'delivery_message',
            'orders_mail_form_client',
            'orders_mail_form_customer',
            'check_in_mail_form',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = ClientSettings::select('value')->where('shop_id',$shop_id)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Order Settings
    function getOrderSettings($shopID)
    {
        // Keys
        $keys = ([
            'delivery',
            'takeaway',
            'room_delivery',
            'table_service',
            'only_cart',
            'auto_order_approval',
            'scheduler_active',
            'min_amount_for_delivery',
            'discount_percentage',
            'order_arrival_minutes',
            'schedule_array',
            'default_printer',
            'receipt_intro',
            'auto_print',
            'printer_paper',
            'printer_tray',
            'play_sound',
            'notification_sound',
            'enable_print',
            'print_font_size',
            'discount_type',
            'customer_details',
            'email_field',
            'floor_field',
            'door_bell_field',
            'full_name_field',
            'instructions_field',
            'live_address_field',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = OrderSetting::select('value')->where('shop_id',$shopID)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Payment Settings
    function getPaymentSettings($shopID)
    {
        // Keys
        $keys = [
            'cash',
            'cash_pos',
            'paypal',
            'paypal_mode',
            'paypal_public_key',
            'paypal_private_key',
            'every_pay',
            'everypay_mode',
            'every_pay_public_key',
            'every_pay_private_key',
            'upi_payment',
            'upi_id',
            'payee_name',
            'upi_qr',
        ];

        $settings = [];

        foreach($keys as $key)
        {
            $query = PaymentSettings::select('value')->where('shop_id',$shopID)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Client's LanguageSettings
    function clientLanguageSettings($shopID)
    {
        // Keys
        $keys = ([
            'primary_language',
            'google_translate',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = LanguageSettings::select('value')->where('key',$key)->where('shop_id',$shopID)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Package Permissions
    function getPackagePermission($subID)
    {
        $details = Subscriptions::where('id',$subID)->first();
        $permission = (isset($details['permissions']) && !empty($details['permissions'])) ? unserialize($details['permissions']) : '';
        return $permission;
    }


    // Get Subscription ID
    function getClientSubscriptionID($shop_id)
    {
        $user_shop = UserShop::where('shop_id',$shop_id)->first();
        $user_id = (isset($user_shop['user_id'])) ? $user_shop['user_id'] : '';
        $user_subscription = UsersSubscriptions::where('user_id',$user_id)->first();
        $subscription_id = (isset($user_subscription['subscription_id'])) ? $user_subscription['subscription_id'] : '';
        return $subscription_id;
    }


    // Get Theme Settings
    function themeSettings($themeID)
    {
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
            $query = ThemeSettings::select('value')->where('key',$key)->where('theme_id',$themeID)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Language Details
    function getLangDetails($langID)
    {
        $language = Languages::where('id',$langID)->first();
        return $language;
    }


    // Get Language Details by Code
    function getLangDetailsbyCode($langCode)
    {
        $language = Languages::where('code',$langCode)->first();
        return $language;
    }


    // Get Tags Product
    function getTagsProducts($tagID,$catID)
    {
        if(!empty($tagID) && !empty($catID))
        {
            // $items = CategoryProductTags::with(['product'])->where('tag_id',$tagID)->where('category_id',$catID)->get();
            $items = CategoryProductTags::join('items','items.id','category_product_tags.item_id')->where('tag_id',$tagID)->where('category_product_tags.category_id',$catID)->where('items.published',1)->orderBy('items.order_key')->get();
        }
        else
        {
            $items = [];
        }
        return $items;
    }


    // Get Ingredients Details
    function getIngredientDetail($id)
    {
        $ingredient = Ingredient::where('id',$id)->first();
        return $ingredient;
    }


    // Get Banner Settings
    function getBanners($shopID)
    {
        $banners = ShopBanner::where('shop_id',$shopID)->where('key','shop_banner')->get();
        return $banners;
    }


    // Get Favourite Clients List
    function FavClients($limit)
    {
        $clients = User::with(['hasOneShop','hasOneSubscription'])->where('user_type',2)->where('is_fav',1)->limit($limit)->get();
        return $clients;
    }


    // Function for Hex to RGB
    function hexToRgb($hex)
    {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return $rgb;
    }


    // Function for Get Item Price
    function getItemPrice($itemID)
    {
        $prices = ItemPrice::where('item_id',$itemID)->get();
        return $prices;
    }


    // Function for Genrate random Token
    function genratetoken($length = 32)
    {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $max = strlen($string) - 1;
        $token = '';

        for ($i = 0; $i < $length; $i++)
        {
            $token .= $string[mt_rand(0, $max)];
        }

        return $token;
    }


    // Check Schedule
    function checkCategorySchedule($catID,$shop_id)
    {
        $current_date = Carbon::now();
        $today = strtolower($current_date->format('l'));
        $current_time = strtotime($current_date->format('G:i'));
        $cat_details = Category::where('id',$catID)->where('shop_id',$shop_id)->first();
        $schedule = (isset($cat_details['schedule'])) ? $cat_details['schedule'] : 0;

        if($schedule == 0)
        {
            return 1;
        }
        else
        {
            $schedule_type = (isset($cat_details['schedule_type']) && !empty($cat_details['schedule_type'])) ? $cat_details['schedule_type'] : 'time';

            if($schedule_type == 'time')
            {
                $schedule_arr = (isset($cat_details['schedule_value']) && !empty($cat_details['schedule_value'])) ? json_decode($cat_details['schedule_value'],true) : '';
                if(count($schedule_arr) > 0)
                {
                    $current_day = (isset($schedule_arr[$today])) ? $schedule_arr[$today] : '';
                    if(isset($current_day['enabled']) && $current_day['enabled'] == 1)
                    {
                        $time_schedule_arr = isset($current_day['timesSchedules']) ? $current_day['timesSchedules'] : [];

                        if(count($time_schedule_arr) > 0)
                        {
                            $count = 1;
                            $total_count = count($time_schedule_arr);
                            foreach($time_schedule_arr as $tsarr)
                            {
                                $start_time = strtotime($tsarr['startTime']);
                                $end_time = strtotime($tsarr['endTime']);

                                if($current_time > $start_time && $current_time < $end_time)
                                {
                                    return 1;
                                }
                                else
                                {
                                    if($count == $total_count)
                                    {
                                        return 0;
                                    }
                                }
                                $count ++;
                            }
                        }
                        else
                        {
                            return 0;
                        }
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    return 0;
                }
            }
            else
            {
                $start_date =  strtotime($cat_details['sch_start_date']);
                $end_date =  strtotime($cat_details['sch_end_date']);

                if(empty($start_date) || empty($end_date))
                {
                    return 1;
                }
                else
                {
                    $curr_date = strtotime($current_date);

                    if($curr_date > $start_date && $curr_date < $end_date)
                    {
                        return 1;
                    }
                    else
                    {
                        return 0;
                    }

                }

            }
        }
    }


    // Check Delivery Schedule
    function checkDeliverySchedule($shop_id)
    {
        date_default_timezone_set('Europe/Athens');

        $current_date = Carbon::now();
        $today = strtolower($current_date->format('l'));
        $current_time = strtotime($current_date->format('G:i'));

        // Order Settings
        $sch_enable_setting = OrderSetting::where('shop_id',$shop_id)->where('key','scheduler_active')->first();
        $sch_array_setting = OrderSetting::where('shop_id',$shop_id)->where('key','schedule_array')->first();

        $schedule = (isset($sch_enable_setting['value']) && $sch_enable_setting['value'] == 1) ? 1 : 0;
        $schedule_arr = (isset($sch_array_setting['value']) && !empty($sch_array_setting['value'])) ? json_decode($sch_array_setting['value'],true) : '';

        if($schedule == 0)
        {
            return 1;
        }
        else
        {
            if(count($schedule_arr) > 0)
            {
                $current_day = (isset($schedule_arr[$today])) ? $schedule_arr[$today] : '';
                if(isset($current_day['enabled']) && $current_day['enabled'] == 1)
                {
                    $time_schedule_arr = isset($current_day['timesSchedules']) ? $current_day['timesSchedules'] : [];

                    if(count($time_schedule_arr) > 0)
                    {
                        $count = 1;
                        $total_count = count($time_schedule_arr);
                        foreach($time_schedule_arr as $tsarr)
                        {
                            $start_time = strtotime($tsarr['startTime']);
                            $end_time = strtotime($tsarr['endTime']);

                            if($current_time > $start_time && $current_time < $end_time)
                            {
                                return 1;
                            }
                            else
                            {
                                if($count == $total_count)
                                {
                                    return 0;
                                }
                            }
                            $count ++;
                        }
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    return 0;
                }
            }
            else
            {
                return 0;
            }
        }

    }


    // Function for Check Delivery Available in Customer Zone
    function checkDeliveryAvilability($shop_id,$latitude,$longitude)
    {
        $delivery_areas = DeliveryAreas::where('shop_id',$shop_id)->get();
        $inside = 0;

        if(count($delivery_areas) > 0)
        {
            foreach($delivery_areas as $delivery_area)
            {
                $coordinates = (isset($delivery_area['coordinates']) && !empty($delivery_area['coordinates'])) ? unserialize($delivery_area['coordinates']) : '';

                $vertices = $coordinates;
                $vertexCount = count($vertices);

                for ($i = 0, $j = $vertexCount - 1; $i < $vertexCount; $j = $i++)
                {
                    $xi = $vertices[$i]['lat'];
                    $yi = $vertices[$i]['lng'];
                    $xj = $vertices[$j]['lat'];
                    $yj = $vertices[$j]['lng'];

                    $intersect = (($yi > $longitude) != ($yj > $longitude)) && ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);

                    if ($intersect)
                    {
                        $inside = 1;
                    }
                }

            }
        }
        else
        {
            $inside = 0;
        }
        return $inside;
    }


    // Get total Quantity of Cart
    function getCartQuantity()
    {
        $cart = session()->get('cart', []);
        $total_quantity = 0;
        if(count($cart) > 0)
        {
            foreach($cart as $cart_data)
            {
                if(count($cart_data) > 0)
                {
                    foreach ($cart_data as $cart_val)
                    {
                        if(count($cart_val) > 0)
                        {
                            foreach($cart_val as $item)
                            {
                                $total_quantity += (isset($item['quantity'])) ? $item['quantity'] : 0;
                            }
                        }
                    }
                }
            }
        }

        if($total_quantity == 0)
        {
            session()->forget('cart');
            session()->save();
        }

        return $total_quantity;
    }


    // Get Total of Cart
    function getCartTotal()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        if(count($cart) > 0)
        {
            foreach($cart as $cart_data)
            {
                if(count($cart_data) > 0)
                {
                    foreach($cart_data as $cart_val)
                    {
                        if(count($cart_val) > 0)
                        {
                            foreach($cart_val as $cart_item)
                            {
                                $total += (isset($cart_item['total_amount'])) ? $cart_item['total_amount'] : 0;
                            }
                        }
                    }
                }
            }
        }
        return $total;
    }


    // Get Item Details
    function itemDetails($itemID)
    {
        $item_details = Items::with(['category'])->where('id',$itemID)->first();
        return $item_details;
    }


    // Function for get client PayPal Config
    function getPayPalConfig($shop_slug)
    {
        $shop = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = isset($shop['id']) ? $shop['id'] : '';

        // Get Payment Settings
        $payment_settings = getPaymentSettings($shop_id);

        $paypal_config = [
            'client_id' => (isset($payment_settings['paypal_public_key'])) ? $payment_settings['paypal_public_key'] : '',
            'secret' => (isset($payment_settings['paypal_private_key'])) ? $payment_settings['paypal_private_key'] : '',
            'settings' => [
                'mode' => (isset($payment_settings['paypal_mode'])) ? $payment_settings['paypal_mode'] : '',
                'http.ConnectionTimeOut' => 30,
                'log.LogEnabled' => 1,
                'log.FileName' => storage_path() . '/logs/paypal.log',
                'log.LogLevel' => 'ERROR',
            ]
        ];
        return $paypal_config;
    }


    // Function for get client EveryPay Config
    function getEveryPayConfig($shop_slug)
    {
        $shop = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = isset($shop['id']) ? $shop['id'] : '';

        // Get Payment Settings
        $payment_settings = getPaymentSettings($shop_id);

        $every_pay_config = [
            'public_key' => (isset($payment_settings['every_pay_public_key'])) ? $payment_settings['every_pay_public_key'] : '',
            'secret_key' => (isset($payment_settings['every_pay_private_key'])) ? $payment_settings['every_pay_private_key'] : '',
            'mode' => (isset($payment_settings['everypay_mode'])) ? $payment_settings['everypay_mode'] : 1,
        ];
        return $every_pay_config;
    }


    // Function for Check Category Type Permission
    function checkCatTypePermission($catType,$shop_id)
    {
        $permission = 0;
        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if($catType == 'parent_category' || $catType == 'product_category')
        {
            $permission = 1;
        }
        else
        {
            if($catType == 'page')
            {
                if(isset($package_permissions['page']) && !empty($package_permissions['page']) && $package_permissions['page'] == 1)
                {
                    $permission = 1;
                }
            }
            elseif($catType == 'link')
            {
                if(isset($package_permissions['link']) && !empty($package_permissions['link']) && $package_permissions['link'] == 1)
                {
                    $permission = 1;
                }
            }
            elseif($catType == 'pdf_page')
            {
                if(isset($package_permissions['pdf_file']) && !empty($package_permissions['pdf_file']) && $package_permissions['pdf_file'] == 1)
                {
                    $permission = 1;
                }
            }
            elseif($catType == 'gallery')
            {
                if(isset($package_permissions['gallery']) && !empty($package_permissions['gallery']) && $package_permissions['gallery'] == 1)
                {
                    $permission = 1;
                }
            }
            elseif($catType == 'check_in')
            {
                if(isset($package_permissions['check_in']) && !empty($package_permissions['check_in']) && $package_permissions['check_in'] == 1)
                {
                    $permission = 1;
                }
            }
        }

        return $permission;
    }

?>
