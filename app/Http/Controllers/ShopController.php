<?php

namespace App\Http\Controllers;

use App\Models\{Category,CategoryProductTags,CategoryVisit,CheckIn,AdditionalLanguage,Clicks, CustomerVisit, FoodJunction, ItemPrice, ItemReview, Items,ItemsVisit,Shop,UserShop,UserVisits,Option, OptionPrice, Order, OrderItems, Rooms, ShopTables, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Magarrent\LaravelCurrencyFormatter\Facades\Currency;
use Mail;
use App\Mail\CheckInMail;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{

    // function for shop Preview
    public function index($slug,$cat_id=NULL,Request $request)
    {
        $shop_slug = $slug;

        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

        if(empty($shop_id))
        {
            return redirect()->route('home')->with('error','This Action is Unauthorized!');
        }

        if($cat_id != NULL && !is_numeric($cat_id))
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','This Action is Unauthorized!');
        }

        $user_ip = $request->ip();

        $current_date = Carbon::now()->format('Y-m-d');

        // Enter Visitor Count
        $user_visit = UserVisits::where('shop_id',$shop_id)->where('ip_address',$user_ip)->whereDate('created_at','=',$current_date)->first();

        if(!isset($user_visit) || empty($user_visit))
        {
            $new_visit = new UserVisits();
            $new_visit->shop_id = $shop_id;
            $new_visit->ip_address = $user_ip;
            $new_visit->save();
        }

        // Count Clicks
        $clicks = Clicks::where('shop_id',$shop_id)->whereDate('created_at',$current_date)->first();
        $click_id = isset($clicks->id) ? $clicks->id : '';
        if(!empty($click_id))
        {
            $edit_click = Clicks::find($click_id);
            $total_clicks = $edit_click->total_clicks + 1;
            $edit_click->total_clicks = $total_clicks;
            $edit_click->update();
        }
        else
        {
            $new_click = new Clicks();
            $new_click->shop_id = $shop_id;
            $new_click->total_clicks = 1;
            $new_click->save();
        }

        if($data['shop_details'])
        {

            $language_setting = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
            $data['primary_language_details'] = getLangDetails($primary_lang_id);
            $primary_lang_code = isset($data['primary_language_details']->code ) ? $data['primary_language_details']->code  : 'en';

            // If Session not have locale then set primary lang locale
            if(!session()->has('locale'))
            {
                App::setLocale($primary_lang_code);
                session()->put('locale', $primary_lang_code);
                session()->save();
            }

            // Current Languge Code
            $data['current_lang_code'] = (session()->has('locale')) ? session()->get('locale') : 'en';

            // Get all Categories of Shop
            $data['categories'] = Category::with(['categoryImages'])->where('published',1)->where('shop_id',$shop_id)->where('parent_id',$cat_id)->orderBy('order_key')->get();

            // Get all Additional Language of Shop
            $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

            // Category ID
            $data['current_cat_id'] = $cat_id;

            return view('shop.shop',$data);
        }
        else
        {
            return redirect()->route('login');
        }
    }



    // Function for Junction Preview
    public function foodJunction($slug)
    {
        try
        {
            $data['junction'] = FoodJunction::where('junction_slug',$slug)->first();
            $junction_id = (isset($data['junction']['id'])) ? $data['junction']['id'] : '';

            if($junction_id)
            {
                return view('junction.junction',$data);
            }
            else
            {
                return redirect()->route('home')->with('error','Junction not Found!');
            }

        }
        catch (\Throwable $th)
        {
            return redirect()->route('home')->with('error','Internal Server Error!');
        }
    }



    // Function for Shop Table
    public function shopTable($shop_slug,$table_no)
    {
        $shop_details = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = (isset($shop_details['id'])) ? $shop_details['id'] : '';

        if(!empty($shop_id))
        {
            $table_details = ShopTables::where('shop_id',$shop_id)->where('table_no',$table_no)->first();
            $table_id = (isset($table_details['id'])) ? $table_details['id'] : '';
            $table_status = (isset($table_details['status'])) ? $table_details['status'] : 0;

            if(!empty($table_id) && $table_status == 1)
            {
                session()->put('checkout_type','table_service');
                session()->put('table_no',$table_no);
                session()->save();
                return redirect()->route('restaurant',$shop_slug);
            }
            else
            {
                return redirect()->route('home')->with('error','Something Went Wrong!');
            }
        }
        else
        {
            return redirect()->route('home')->with('error','Something Went Wrong!');
        }
    }



    // Function for Shop Room
    public function shopRoom($shop_slug,$room_no)
    {
        $shop_details = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = (isset($shop_details['id'])) ? $shop_details['id'] : '';

        if(!empty($shop_id))
        {
            $room_details = Rooms::where('shop_id',$shop_id)->where('room_no',$room_no)->first();
            $room_id = (isset($room_details['id'])) ? $room_details['id'] : '';
            $room_status = (isset($room_details['status'])) ? $room_details['status'] : 0;

            if(!empty($room_id) && $room_status == 1)
            {
                session()->put('checkout_type','room_delivery');
                session()->put('room_no',$room_no);
                session()->save();
                return redirect()->route('restaurant',$shop_slug);
            }
            else
            {
                return redirect()->route('home')->with('error','Something Went Wrong!');
            }
        }
        else
        {
            return redirect()->route('home')->with('error','Something Went Wrong!');
        }
    }



    // function for shop's Items Preview
    public function itemPreview($shop_slug,$cat_id)
    {

        $current_date = Carbon::now()->format('Y-m-d');

        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

        $is_active_cat = checkCategorySchedule($cat_id,$shop_id);

        if($is_active_cat == 0)
        {
            return redirect()->route('restaurant',$shop_slug);
        }

        // Category Details
        $data['cat_details'] = Category::with(['categoryImages'])->where('shop_id',$shop_id)->where('id',$cat_id)->first();
        $cat_parent_id = isset($data['cat_details']->parent_id) ? $data['cat_details']->parent_id : null;
        $is_parent = isset($data['cat_details']->parent_category) ? $data['cat_details']->parent_category : 0;

        // CategoryItem Tags
        $data['cat_tags'] = CategoryProductTags::join('tags','tags.id','category_product_tags.tag_id')->orderBy('tags.order')->where('category_id',$cat_id)->where('tags.shop_id',$shop_id)->get()->unique('tag_id');

        // Get all Categories
        // $data['categories'] = Category::orderBy('order_key')->where('published',1)->where('shop_id',$shop_id)->where('parent_id',$cat_parent_id)->where('parent_category',0)->get();
        $data['categories'] = Category::orderBy('order_key')->where('published',1)->where('shop_id',$shop_id)->where('parent_id',$cat_parent_id)->get();

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Current Languge Code
        $data['current_lang_code'] = (session()->has('locale')) ? session()->get('locale') : 'en';

        $data['all_items'] = Items::where('category_id',$cat_id)->orderBy('order_key')->where('published',1)->get();

        if($data['cat_details'] && $data['shop_details'])
        {
            // Get all Additional Language of Shop
            $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

            // Count Category Visit
            $category_visit = CategoryVisit::where('category_id',$cat_id)->where('shop_id',$shop_id)->first();
            $cat_visit_id = isset($category_visit->id) ? $category_visit->id : '';

            if(!empty($cat_visit_id))
            {
                $cat_visit = CategoryVisit::find($cat_visit_id);
                $total_clicks = $cat_visit->total_clicks + 1;
                $cat_visit->total_clicks = $total_clicks;
                $cat_visit->update();
            }
            else
            {
                $new_cat_visit = new CategoryVisit();
                $new_cat_visit->shop_id = $shop_id;
                $new_cat_visit->category_id = $cat_id;
                $new_cat_visit->total_clicks = 1;
                $new_cat_visit->save();
            }


            // Count Clicks
            $clicks = Clicks::where('shop_id',$shop_id)->whereDate('created_at',$current_date)->first();
            $click_id = isset($clicks->id) ? $clicks->id : '';
            if(!empty($click_id))
            {
                $edit_click = Clicks::find($click_id);
                $total_clicks = $edit_click->total_clicks + 1;
                $edit_click->total_clicks = $total_clicks;
                $edit_click->update();
            }
            else
            {
                $new_click = new Clicks();
                $new_click->shop_id = $shop_id;
                $new_click->total_clicks = 1;
                $new_click->save();
            }

            $data['parent_id'] = $cat_parent_id;

            if($is_parent == 1)
            {
                return redirect()->route('restaurant',[$shop_slug,$cat_id]);
            }
            else
            {
                if($data['cat_details']->category_type == 'page' || $data['cat_details']->category_type == 'gallery' || $data['cat_details']->category_type == 'pdf_page' || $data['cat_details']->category_type == 'check_in')
                {
                    return view('shop.page_preview',$data);
                }
            }

            return view('shop.item_preview',$data);
        }
        else
        {
            return redirect()->back()->with('error',"Oops, Something Went Wrong !");
        }

    }



    // Change Locale
    public function changeShopLocale(Request $request)
    {
        $lang_code = $request->lang_code;

        // If Session not have locale then set primary lang locale
        if(session()->has('locale'))
        {
            App::setLocale($lang_code);
            session()->put('locale', $lang_code);
            session()->save();
        }
        else
        {
            App::setLocale($lang_code);
            session()->put('locale', $lang_code);
            session()->save();
        }

        return response()->json([
            'success' => 1,
        ]);
    }


    // Search Categories
    public function searchCategories(Request $request)
    {
        $shop_id = decrypt($request->shopID);
        $keyword = $request->keywords;
        $current_cat_id = $request->current_cat_id;
        $categories_ids = [];

        $sub_cat = Category::select('id')->where('shop_id',$shop_id)->where('parent_id',$current_cat_id)->get();

        if(count($sub_cat) > 0)
        {
            foreach($sub_cat as $val)
            {
                $categories_ids[] = $val->id;
            }
        }

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

        $name_key = $current_lang_code."_name";
        $description_key = $current_lang_code."_description";
        $price_label_key = $current_lang_code."_label";

        // Shop Details
        $shop_details = Shop::where('id',$shop_id)->first();

        $shop_slug = isset($shop_details['shop_slug']) ? $shop_details['shop_slug'] : '';

        // Shop Settings
        $shop_settings = getClientSettings($shop_id);
        $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        // Theme Settings
        $theme_settings = themeSettings($shop_theme_id);

        // Read More Label
        $read_more_label = (isset($theme_settings['read_more_link_label']) && !empty($theme_settings['read_more_link_label'])) ? $theme_settings['read_more_link_label'] : 'Read More';

        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        try
        {
            $categories = Category::with(['categoryImages'])->where("$name_key",'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id)->where('parent_id',$current_cat_id)->where('published',1)->orderBy('order_key')->get();

            $html = '';

            if(empty($keyword))
            {
                if(count($categories) > 0)
                {
                    $html .= '<div class="menu_list">';

                    foreach($categories as $category)
                    {
                        $category_name = (isset($category->$name_key)) ? $category->$name_key : '';
                        $default_image = asset('public/client_images/not-found/no_image_1.jpg');
                        $cat_image = isset($category->categoryImages[0]['image']) ? $category->categoryImages[0]['image'] : '';
                        $thumb_image = isset($category->cover) ? $category->cover : '';
                        $active_cat = checkCategorySchedule($category->id,$category->shop_id);

                        if($category->category_type == 'product_category')
                        {
                            if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
                            {
                                $image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image);
                            }
                            else
                            {
                                $image = $default_image;
                            }
                        }
                        else
                        {
                            if(!empty($thumb_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$thumb_image))
                            {
                                $image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$thumb_image);
                            }
                            else
                            {
                                $image = $default_image;
                            }
                        }

                        if($category->category_type == 'link')
                        {
                            $cat_items_url = (isset($category->link_url) && !empty($category->link_url)) ? $category->link_url : '#';
                        }
                        elseif($category->category_type == 'parent_category')
                        {
                            $cat_items_url = route('restaurant',[$shop_details['shop_slug'],$category->id]);
                        }
                        else
                        {
                            $cat_items_url = route('items.preview',[$shop_details['shop_slug'],$category->id]);
                        }

                        if($active_cat == 1)
                        {
                            $html .= '<div class="menu_list_item">';
                                $html .= '<a href="'.$cat_items_url.'">';
                                    $html .= '<img src="'.$image.'" class="w-100">';
                                    $html .= '<h3 class="item_name">'.$category_name.'</h3>';
                                $html .= '</img>';
                            $html .= '</div>';
                        }

                    }

                    $html .= '</div>';
                }
                else
                {
                    $html .= '<h3 class="text-center">Categories not Found.</h3>';
                }
            }
            else
            {
                $items = Items::where("$name_key",'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id)->whereIn('category_id',$categories_ids)->where('published',1)->get();

                if(count($items) > 0)
                {
                    $html .= '<div class="item_inr_info_sec">';
                        $html .= '<div class="row">';
                            foreach($items as $item)
                            {
                                $item_name = (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "";
                                $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                $active_cat = checkCategorySchedule($item->category_id,$item->shop_id);

                                if($active_cat == 1)
                                {
                                    if($item['type'] == 2)
                                    {
                                        $html .= '<div class="col-md-12 mb-3">';
                                            $html .= '<div class="single_item_inr devider">';

                                                if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                {
                                                    $item_divider_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                                    $html .= '<div class="item_image">';
                                                        $html .= '<img src="'.$item_divider_image.'">';
                                                    $html .= '</div>';
                                                }

                                                $html .= '<h3>'.$item_name.'</h3>';

                                                if(count($ingrediet_arr) > 0)
                                                {
                                                    $html .= '<div>';
                                                        foreach ($ingrediet_arr as $val)
                                                        {
                                                            $ingredient = getIngredientDetail($val);
                                                            $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                            $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                            if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                            {
                                                                if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                {
                                                                    $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                                    $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                                }
                                                            }
                                                        }
                                                    $html .= '</div>';
                                                }

                                                $html .= '<div class="item-desc">'.(isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "".'</div>';

                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    else
                                    {
                                        $html .= '<div class="col-md-6 col-lg-6 col-xl-3 mb-3">';
                                            $html .= '<div class="single_item_inr devider-border" onclick="getItemDetails('.$item->id.','.$shop_id.')" style="cursor: pointer">';

                                            if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                            {
                                                $item_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                                $html .= '<div class="item_image">';
                                                    $html .= '<img src="'.$item_image.'">';
                                                $html .= '</div>';
                                            }

                                            if($item['day_special'] == 1)
                                            {
                                                if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                                {
                                                    $today_spec_icon = asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon);
                                                    $html .= '<img width="170" class="mt-3" src="'.$today_spec_icon.'">';
                                                }
                                                else
                                                {
                                                    if(!empty($default_special_image))
                                                    {
                                                        $html .= '<img width="170" class="mt-3" src="'.$default_special_image.'">';
                                                    }
                                                    else
                                                    {
                                                        $def_tds_img = asset('public/client_images/bs-icon/today_special.gif');
                                                        $html .= '<img width="170" class="mt-3" src="'.$def_tds_img.'">';
                                                    }
                                                }
                                            }

                                            $html .= '<h3>'.$item_name.'</h3>';

                                            if($item['is_new'] == 1)
                                            {
                                                $new_img = asset('public/client_images/bs-icon/new.png');
                                                $html .= '<img class="is_new tag-img" src="'.$new_img.'">';
                                            }

                                            if($item['as_sign'] == 1)
                                            {
                                                $as_sign_img = asset('public/client_images/bs-icon/signature.png');
                                                $html .= '<img class="is_sign tag-img" src="'.$as_sign_img.'">';
                                            }

                                            if(count($ingrediet_arr) > 0)
                                            {
                                                $html .= '<div>';
                                                    foreach ($ingrediet_arr as $val)
                                                    {
                                                        $ingredient = getIngredientDetail($val);
                                                        $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                        $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                        if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                        {
                                                            if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                            {
                                                                $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                                $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                            }
                                                        }
                                                    }
                                                $html .= '</div>';
                                            }

                                            $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "";

                                            if(strlen(strip_tags($desc)) > 180)
                                            {
                                                $desc = substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n"));
                                                $html .= '<div class="item-desc"><p>'.$desc.' ... <br>
                                                <a class="read-more-desc">'.$read_more_label.'</a></p></div>';
                                            }
                                            else
                                            {
                                                $html .= '<div class="item-desc"><p>'.strip_tags($desc).'</p></div>';
                                            }

                                            $html .= '<ul class="price_ul">';
                                                $price_arr = getItemPrice($item['id']);
                                                if(count($price_arr) > 0)
                                                {
                                                    foreach ($price_arr as $key => $value)
                                                    {
                                                        $price = Currency::currency($currency)->format($value['price']);
                                                        $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";

                                                        $html .= '<li><p>'.$price_label.' <span>'.$price.'</span></p></li>';
                                                    }
                                                }
                                            $html .= '</ul>';

                                            if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
                                            {
                                                $html .= '<div class="cart-symbol"><i class="bi bi-cart4"></i></div>';
                                            }

                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                }

                            }
                        $html .= '</div>';
                    $html .= '</row>';
                }
                else
                {
                    $html .= '<h3 class="text-center">Items not Found.</h3>';
                }
            }


            return response()->json([
                'success' => 1,
                'message' => "Categories has been retrived Successfully...",
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


    // Search Itens
    public function searchItems(Request $request)
    {
        $category_id = $request->category_id;
        $tab_id = $request->tab_id;
        $keyword = $request->keyword;
        $shop_id = $request->shop_id;
        $tag_id = $request->tag_id;
        $parent_id = $request->parent_id;

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';
        $name_key = $current_lang_code."_name";
        $description_key = $current_lang_code."_description";
        $price_label_key = $current_lang_code."_label";

        // Shop Details
        $shop_details = Shop::where('id',$shop_id)->first();

        $shop_slug = isset($shop_details['shop_slug']) ? $shop_details['shop_slug'] : '';

        // Shop Settings
        $shop_settings = getClientSettings($shop_id);
        $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Theme Settings
        $theme_settings = themeSettings($shop_theme_id);

        // Read More Label
        $read_more_label = (isset($theme_settings['read_more_link_label']) && !empty($theme_settings['read_more_link_label'])) ? $theme_settings['read_more_link_label'] : 'Read More';

        // Today Special Icon
        $today_special_icon = isset($theme_settings['today_special_icon']) ? $theme_settings['today_special_icon'] : '';

        // Admin Settings
        $admin_settings = getAdminSettings();
        $default_special_image = (isset($admin_settings['default_special_item_image'])) ? $admin_settings['default_special_item_image'] : '';

        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        try
        {
            if($tab_id == 'all' || $tab_id == 'no_tab')
            {
                $html = '';

                if($keyword == '')
                {
                    $items = Items::where("$name_key",'LIKE','%'.$keyword.'%')->where('category_id',$category_id)->where('published',1)->get();
                }
                else
                {
                    $items = Items::whereHas('category', function($q) use ($parent_id)
                    {
                        $q->where('parent_id',$parent_id);
                    })->where("$name_key",'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id)->where('published',1)->get();
                }

                if(count($items) > 0)
                {
                    $html .= '<div class="item_inr_info_sec">';
                        $html .= '<div class="row">';

                            foreach($items as $item)
                            {
                                $item_name = (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "";
                                $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                $active_cat = checkCategorySchedule($item->category_id,$item->shop_id);

                                if($active_cat == 1)
                                {
                                    if($item['type'] == 2)
                                    {
                                        $html .= '<div class="col-md-12 mb-3">';
                                            $html .= '<div class="single_item_inr devider">';

                                                if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                {
                                                    $item_divider_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                                    $html .= '<div class="item_image">';
                                                        $html .= '<img src="'.$item_divider_image.'">';
                                                    $html .= '</div>';
                                                }

                                                $html .= '<h3>'.$item_name.'</h3>';

                                                if(count($ingrediet_arr) > 0)
                                                {
                                                    $html .= '<div>';
                                                        foreach ($ingrediet_arr as $val)
                                                        {
                                                            $ingredient = getIngredientDetail($val);
                                                            $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                            $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                            if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                            {
                                                                if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                {
                                                                    $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                                    $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                                }
                                                            }
                                                        }
                                                    $html .= '</div>';
                                                }
                                                $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? html_entity_decode($item[$description_key]) : "";
                                                $html .= '<div class="item-desc">'.json_decode($desc,true).'</div>';

                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    else
                                    {
                                        $html .= '<div class="col-md-6 col-lg-6 col-xl-3 mb-3">';
                                            $html .= '<div class="single_item_inr devider-border" onclick="getItemDetails('.$item->id.','.$shop_id.')" style="cursor: pointer">';

                                            if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                            {
                                                $item_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                                $html .= '<div class="item_image">';
                                                    $html .= '<img src="'.$item_image.'">';
                                                $html .= '</div>';
                                            }

                                            if($item['day_special'] == 1)
                                            {
                                                if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                                {
                                                    $today_spec_icon = asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon);
                                                    $html .= '<img width="170" class="mt-3" src="'.$today_spec_icon.'">';
                                                }
                                                else
                                                {
                                                    if(!empty($default_special_image))
                                                    {
                                                        $html .= '<img width="170" class="mt-3" src="'.$default_special_image.'">';
                                                    }
                                                    else
                                                    {
                                                        $def_tds_img = asset('public/client_images/bs-icon/today_special.gif');
                                                        $html .= '<img width="170" class="mt-3" src="'.$def_tds_img.'">';
                                                    }
                                                }
                                            }

                                            $html .= '<h3>'.$item_name.'</h3>';

                                            if($item['is_new'] == 1)
                                            {
                                                $new_img = asset('public/client_images/bs-icon/new.png');
                                                $html .= '<img class="is_new tag-img" src="'.$new_img.'">';
                                            }

                                            if($item['as_sign'] == 1)
                                            {
                                                $as_sign_img = asset('public/client_images/bs-icon/signature.png');
                                                $html .= '<img class="is_sign tag-img" src="'.$as_sign_img.'">';
                                            }

                                            if(count($ingrediet_arr) > 0)
                                            {
                                                $html .= '<div>';
                                                    foreach ($ingrediet_arr as $val)
                                                    {
                                                        $ingredient = getIngredientDetail($val);
                                                        $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                        $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                        if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                        {
                                                            if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                            {
                                                                $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                                $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                            }
                                                        }
                                                    }
                                                $html .= '</div>';
                                            }

                                            $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? html_entity_decode($item[$description_key]) : "";

                                            if(strlen(strip_tags($desc)) > 180)
                                            {
                                                $desc = substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n"));
                                                $html .= '<div class="item-desc"><p>'.$desc.' ... <br>
                                                <a class="read-more-desc">'.$read_more_label.'</a></p></div>';
                                            }
                                            else
                                            {
                                                $html .= '<div class="item-desc"><p>'.strip_tags($desc).'</p></div>';
                                            }

                                            $html .= '<ul class="price_ul">';
                                                $price_arr = getItemPrice($item['id']);
                                                if(count($price_arr) > 0)
                                                {
                                                    foreach ($price_arr as $key => $value)
                                                    {
                                                        $price = Currency::currency($currency)->format($value['price']);
                                                        $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";

                                                        $html .= '<li><p>'.$price_label.' <span>'.$price.'</span></p></li>';
                                                    }
                                                }
                                            $html .= '</ul>';

                                            if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
                                            {
                                                $html .= '<div class="cart-symbol"><i class="bi bi-cart4"></i></div>';
                                            }

                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                }

                            }

                        $html .= '</div>';
                    $html .= '</div>';

                    return response()->json([
                        'success' => 1,
                        'data'    => $html,
                    ]);
                }
                else
                {
                    $html .= '<h3 class="text-center">Items Not Found!</h3>';
                    return response()->json([
                        'success' => 1,
                        'data' => $html,
                    ]);
                }
            }
            else
            {
                $html = '';
                if($keyword == '')
                {
                    $items = CategoryProductTags::join('items','items.id','category_product_tags.item_id')->where("items.$name_key",'LIKE','%'.$keyword.'%')->where('tag_id',$tag_id)->where('category_product_tags.category_id',$category_id)->orderBy('items.order_key')->where('items.published',1)->get();
                }
                else
                {
                    $items = Items::where("$name_key",'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id)->where('published',1)->get();
                }

                if(count($items) > 0)
                {
                    $html .= '<div class="item_inr_info_sec">';
                        $html .= '<div class="row">';

                            foreach($items as $item)
                            {
                                $item_name = (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "";
                                $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];

                                if($item['type'] == 2)
                                {
                                    $html .= '<div class="col-md-12 mb-3">';
                                        $html .= '<div class="single_item_inr devider">';

                                            if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                            {
                                                $item_divider_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                                $html .= '<div class="item_image">';
                                                    $html .= '<img src="'.$item_divider_image.'">';
                                                $html .= '</div>';
                                            }

                                            $html .= '<h3>'.$item_name.'</h3>';

                                            if(count($ingrediet_arr) > 0)
                                            {
                                                $html .= '<div>';
                                                    foreach ($ingrediet_arr as $val)
                                                    {
                                                        $ingredient = getIngredientDetail($val);
                                                        $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                        $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                        if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                        {
                                                            if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                            {
                                                                $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                                $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                            }
                                                        }
                                                    }
                                                $html .= '</div>';
                                            }

                                            $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? html_entity_decode($item[$description_key]) : "";
                                            $html .= '<div class="item-desc">'.json_decode($desc,true).'</div>';

                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                                else
                                {
                                    $html .= '<div class="col-md-6 col-lg-6 col-xl-3 mb-3">';
                                        $html .= '<div class="single_item_inr devider-border" onclick="getItemDetails('.$item->id.','.$shop_id.')" style="cursor: pointer">';

                                        if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                        {
                                            $item_image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']);
                                            $html .= '<div class="item_image">';
                                                $html .= '<img src="'.$item_image.'">';
                                            $html .= '</div>';
                                        }

                                        if($item['day_special'] == 1)
                                        {
                                            if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                            {
                                                $today_spec_icon = asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon);
                                                $html .= '<img width="170" class="mt-3" src="'.$today_spec_icon.'">';
                                            }
                                            else
                                            {
                                                if(!empty($default_special_image))
                                                {
                                                    $html .= '<img width="170" class="mt-3" src="'.$default_special_image.'">';
                                                }
                                                else
                                                {
                                                    $def_tds_img = asset('public/client_images/bs-icon/today_special.gif');
                                                    $html .= '<img width="170" class="mt-3" src="'.$def_tds_img.'">';
                                                }
                                            }
                                        }

                                        $html .= '<h3>'.$item_name.'</h3>';

                                        if($item['is_new'] == 1)
                                        {
                                            $new_img = asset('public/client_images/bs-icon/new.png');
                                            $html .= '<img class="is_new tag-img" src="'.$new_img.'">';
                                        }

                                        if($item['as_sign'] == 1)
                                        {
                                            $as_sign_img = asset('public/client_images/bs-icon/signature.png');
                                            $html .= '<img class="is_sign tag-img" src="'.$as_sign_img.'">';
                                        }

                                        if(count($ingrediet_arr) > 0)
                                        {
                                            $html .= '<div>';
                                                foreach ($ingrediet_arr as $val)
                                                {
                                                    $ingredient = getIngredientDetail($val);
                                                    $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                    $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;

                                                    if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                    {
                                                        if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                        {
                                                            $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                                                            $html .= '<img src="'.$ing_icon.'" width="60px" height="60px">';
                                                        }
                                                    }
                                                }
                                            $html .= '</div>';
                                        }

                                        $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? html_entity_decode($item[$description_key]) : "";

                                        if(strlen(strip_tags($desc)) > 180)
                                        {
                                            $desc = substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n"));
                                            $html .= '<div class="item-desc">'.$desc.' ... <br>
                                                <a class="read-more-desc"><p>'.$read_more_label.'</a></p></div>';
                                        }
                                        else
                                        {
                                            $html .= '<div class="item-desc"><p>'.strip_tags($desc).'</p></div>';
                                        }

                                        $html .= '<ul class="price_ul">';
                                            $price_arr = getItemPrice($item['id']);
                                            if(count($price_arr) > 0)
                                            {
                                                foreach ($price_arr as $key => $value)
                                                {
                                                    $price = Currency::currency($currency)->format($value['price']);
                                                    $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";

                                                    $html .= '<li><p>'.$price_label.' <span>'.$price.'</span></p></li>';
                                                }
                                            }
                                        $html .= '</ul>';

                                        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
                                        {
                                            $html .= '<div class="cart-symbol"><i class="bi bi-cart4"></i></div>';
                                        }

                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                            }

                        $html .= '</div>';
                    $html .= '</div>';

                    return response()->json([
                        'success' => 1,
                        'data'    => $html,
                    ]);
                }
                else
                {
                    $html .= '<h3 class="text-center">Items Not Found!</h3>';
                    return response()->json([
                        'success' => 1,
                        'data' => $html,
                    ]);
                }
            }
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                "message" => 'Internal Server Errors',
            ]);
        }

    }


    // Delete Shop Logo
    public function deleteShopLogo()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $shop = Shop::find($shop_id);

        if($shop)
        {
            $shop_logo = isset($shop->logo) ? $shop->logo : '';
            if(!empty($shop_logo))
            {
                $new_path = str_replace(asset('/public/'),public_path(),$shop_logo);
                if(file_exists($new_path))
                {
                    unlink($new_path);
                }
            }

            $shop->logo = "";
        }

        $shop->update();

        return redirect()->back()->with('success', "Shop Logo has been Removed SuccessFully..");

    }


    // Function for Get Item Details
    public function getDetails(Request $request)
    {
        $current_date = Carbon::now();

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

        // Shop Settings
        $shop_settings = getClientSettings($request->shop_id);

        // Shop Default Currency
        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Shop Theme ID
        $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

        // Theme Settings
        $theme_settings = themeSettings($shop_theme_id);

        // Today Special Icon
        $today_special_icon = isset($theme_settings['today_special_icon']) ? $theme_settings['today_special_icon'] : '';

        // Admin Settings
        $admin_settings = getAdminSettings();

        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($request->shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        // Shop Details
        $shop_details = Shop::where('id',$request->shop_id)->first();

        $shop_slug = isset($shop_details['shop_slug']) ? $shop_details['shop_slug'] : '';

        // Default Today Special Image
        $default_special_image = (isset($admin_settings['default_special_item_image'])) ? $admin_settings['default_special_item_image'] : '';

        // Name Key
        $name_key = $current_lang_code."_name";
        // Title Key
        $title_key = $current_lang_code."_title";
        // Description Key
        $description_key = $current_lang_code."_description";
        // Price Label Key
        $price_label_key = $current_lang_code."_label";
        // Item ID
        $item_id = $request->item_id;

        // Count Items Visit
        $item_visit = ItemsVisit::where('item_id',$item_id)->where('shop_id',$request->shop_id)->first();
        $item_visit_id = isset($item_visit->id) ? $item_visit->id : '';

        if(!empty($item_visit_id))
        {
            $edit_item_visit = ItemsVisit::find($item_visit_id);
            $total_clicks = $edit_item_visit->total_clicks + 1;
            $edit_item_visit->total_clicks = $total_clicks;
            $edit_item_visit->update();
        }
        else
        {
            $new_item_visit = new ItemsVisit();
            $new_item_visit->shop_id = $request->shop_id;
            $new_item_visit->item_id = $item_id;
            $new_item_visit->total_clicks = 1;
            $new_item_visit->save();
        }


        // Count Clicks
        $clicks = Clicks::where('shop_id',$request->shop_id)->whereDate('created_at',$current_date)->first();
        $click_id = isset($clicks->id) ? $clicks->id : '';
        if(!empty($click_id))
        {
            $edit_click = Clicks::find($click_id);
            $total_clicks = $edit_click->total_clicks + 1;
            $edit_click->total_clicks = $total_clicks;
            $edit_click->update();
        }
        else
        {
            $new_click = new Clicks();
            $new_click->shop_id = $request->shop_id;
            $new_click->total_clicks = 1;
            $new_click->save();
        }


        try
        {

            $html = '';

            $item = Items::where('id',$item_id)->first();

            if(isset($item))
            {
                $item_image = (isset($item['image']) && !empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) : '';
                $item_name = (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : $item['name'];
                $item_desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : $item['description'];
                $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                $price_arr = getItemPrice($item['id']);
                $item_discount = (isset($item['discount'])) ? $item['discount'] : 0;
                $item_discount_type = (isset($item['discount_type'])) ? $item['discount_type'] : 'percentage';

                $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';
                $html .= '<input type="hidden" name="shop_id" id="shop_id" value="'.$request->shop_id.'">';

                if($item['as_sign'] == 1)
                {
                    $sign_image = asset('public/client_images/bs-icon/signature.png');

                    $html .= '<img class="is_sign tag-img position-absolute" src="'.$sign_image.'" style="top:0; left:50%; transform:translate(-50%,0); width:45px;">';
                }

                if($item['is_new'] == 1)
                {
                    $is_new_img = asset('public/client_images/bs-icon/new.png');

                    $html .= '<img class="is_new tag-img position-absolute" src="'.$is_new_img.'" style="top:0; left:0; width:55px;">';
                }

                $html .= '<div class="row ';
                    if($item['as_sign'] == 1 || $item['is_new'] == 1)
                    {
                        $html .= 'mt-3';
                    }
                $html .='">';

                    $html .= '<div class="col-md-12 text-center mb-2 ';
                    if($item['as_sign'] == 1 || $item['is_new'] == 1)
                    {
                        $html .= 'mt-4';
                    }
                    $html .='">';
                        $html .= '<h3>'.$item_name.'</h3>';
                    $html .= '</div>';

                    if(!empty($item_image))
                    {
                        $html .= '<div class="col-md-12 mb-2 text-center">';
                            // $html .= '<img src="'.$item_image.'" class="w-100 item-dt-img" style="max-height:400px">';
                            $html .= '<img src="'.$item_image.'" class="item-dt-img">';
                        $html .= '</div>';
                    }

                    if($item['day_special'] == 1)
                    {
                        $html .= '<div class="col-md-12 mb-3 text-center">';
                        if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                        {
                            $tds_icon = asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon);
                            $html .= '<img class="mt-3" src="'.$tds_icon.'">';
                        }
                        else
                        {
                            if(!empty($default_special_image))
                            {
                                $html .= '<img class="mt-3" src="'.$default_special_image.'">';
                            }
                            else
                            {
                                $sp_image = asset('public/client_images/bs-icon/today_special.gif');
                                $html .= '<img class="mt-3" src="'.$sp_image.'">';
                            }
                        }
                        $html .= '</div>';
                    }

                    if(count($ingrediet_arr) > 0)
                    {
                        $html .= '<div class="col-md-12 mb-3">';
                            $html .= '<div class="d-flex align-items-center justify-content-center">';
                                foreach ($ingrediet_arr as $val)
                                {
                                    $ingredient = getIngredientDetail($val);

                                    if(isset($ingredient['icon']) && !empty($ingredient['icon']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ingredient['icon']))
                                    {
                                        $ing_icon = asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ingredient['icon']);
                                        $html .= '<img src="'.$ing_icon.'" width="80px" height="80px" style="border: 1px solid black; border-radius:100%; padding:2px;margin:0 2px;">';
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';
                    }

                    if(!empty($item_desc))
                    {
                        $html .= '<div class="col-md-12 text-center mt-2 mb-2">';
                            $html .= $item_desc;
                        $html .= '</div>';
                    }

                    if(count($price_arr) > 0)
                    {
                        $html .= '<input type="hidden" name="def_currency" id="def_currency" value="'.$currency.'">';

                        $html .= '<div class="col-md-12 text-center py-2" style="border-top: 2px solid #ccc; border-bottom: 2px solid #ccc">';
                            $t_price = (isset($price_arr[0]->price)) ? Currency::currency($currency)->format($price_arr[0]->price) : Currency::currency($currency)->format(0.00);
                            $html .= '<div><b id="total_price">'.$t_price.'</b></div>';

                            if($item_discount > 0)
                            {
                                if($item_discount_type == 'fixed')
                                {
                                    $hidden_price = number_format($price_arr[0]->price - $item_discount,2);
                                }
                                else
                                {
                                    $dis_per = $price_arr[0]->price * $item_discount / 100;
                                    $hidden_price = number_format($price_arr[0]->price - $dis_per,2);
                                }
                                $html .= "<input type='hidden' name='total_amount' id='total_amount' value='".$hidden_price."'>";
                            }
                            else
                            {
                                $html .= "<input type='hidden' name='total_amount' id='total_amount' value='".$price_arr[0]->price."'>";
                            }

                        $html .= '</div>';

                        if(count($price_arr) > 0)
                        {
                            if(count($price_arr) > 1)
                            {
                                $display = '';
                            }
                            else
                            {
                                $display = 'none';
                            }

                            $html .= '<div class="col-md-12 mt-3 cart-price" style="display:'.$display.'">';
                                $html .= '<div class="row p-3">';
                                foreach ($price_arr as $key => $value)
                                {
                                    if($item_discount > 0)
                                    {
                                        if($item_discount_type == 'fixed')
                                        {
                                            $price = $value['price'] - $item_discount;
                                        }
                                        else
                                        {
                                            $price_per = $value['price'] *  $item_discount / 100;
                                            $price = $value['price'] - $price_per;
                                        }
                                    }
                                    else
                                    {
                                        $price = $value['price'];
                                    }
                                    $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";

                                    $html .= '<div class="col-6">';
                                        $html .= '<input type="radio" name="base_price" onchange="updatePrice()" value="'.$price.'" id="base_price_'.$key.'" class="me-2" ';
                                            if($key == 0)
                                            {
                                                $html .= 'checked';
                                            }
                                        $html .=' option-id="'.$value['id'].'">';
                                        $html .= '<label class="form-label" for="base_price_'.$key.'">'.$price_label.'</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-6 text-end">';
                                        $html .= '<label class="form-label">'.Currency::currency($currency)->format($price).'</label>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                            $html .= '</div>';
                        }

                        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
                        {
                            // Options
                            $option_ids = (isset($item['options']) && !empty($item['options'])) ? unserialize($item['options']) : [];

                            $html .= "<input type='hidden' name='option_ids' id='option_ids' value='".json_encode($option_ids,TRUE)."'>";

                            if(count($option_ids) > 0)
                            {
                                $html .= '<div class="col-md-12 mb-3 cart-price">';
                                    foreach($option_ids as $outer_key => $opt_id)
                                    {
                                        $html .= '<div class="row p-3" id="option_'.$outer_key.'">';
                                            $opt_dt = Option::with(['optionPrices'])->where('id',$opt_id)->first();
                                            $enable_price = (isset($opt_dt['enabled_price'])) ? $opt_dt['enabled_price'] : '';
                                            $option_prices = (isset($opt_dt['optionPrices'])) ? $opt_dt['optionPrices'] : [];

                                            if(count($option_prices) > 0)
                                            {
                                                $html .= '<div class="col-md-12 mb-2">';
                                                    $html .= '<b>'.$opt_dt[$title_key].'</b>';
                                                $html .= '</div>';
                                                $radio_key = 0;
                                                foreach($option_prices as $key => $option_price)
                                                {
                                                    $opt_price = Currency::currency($currency)->format($option_price['price']);
                                                    $opt_price_label = (isset($option_price[$name_key])) ? $option_price[$name_key] : "";
                                                    if(isset($opt_dt['multiple_select']) && $opt_dt['multiple_select'] == 1)
                                                    {
                                                        $is_checked = (isset($opt_dt['pre_select']) && $opt_dt['pre_select'] == 1) ? 'checked' : '';
                                                        $html .= '<div class="col-6">';
                                                            $html .= '<input type="checkbox" value="'.$option_price['price'].'" name="option_price_checkbox_'.$outer_key.'" onchange="updatePrice()" id="option_price_checkbox_'.$outer_key.'_'.$key.'" class="me-2" opt_price_id="'.$option_price['id'].'" '.$is_checked.'>';
                                                            $html .= '<label class="form-label" for="option_price_checkbox_'.$outer_key.'_'.$key.'">'.$opt_price_label.'</label>';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-6 text-end">';
                                                            if($enable_price == 1)
                                                            {
                                                                $html .= '<label class="form-label">'.$opt_price.'</label>';
                                                            }
                                                        $html .= '</div>';
                                                    }
                                                    else
                                                    {
                                                        $radio_key ++;
                                                        if($radio_key == 1)
                                                        {
                                                            $auto_check_radio = 'checked';
                                                        }
                                                        else
                                                        {
                                                            $auto_check_radio = "";
                                                        }

                                                        $html .= '<div class="col-6">';
                                                            $html .= '<input type="radio" value="'.$option_price['price'].'" name="option_price_radio_'.$outer_key.'" onchange="updatePrice()" id="option_price_radio_'.$outer_key.'_'.$key.'" class="me-2" opt_price_id="'.$option_price['id'].'" '.$auto_check_radio.'>';
                                                            $html .= '<label class="form-label" for="option_price_radio_'.$outer_key.'_'.$key.'">'.$opt_price_label.'</label>';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-6 text-end">';
                                                            if($enable_price == 1)
                                                            {
                                                                $html .= '<label class="form-label">'.$opt_price.'</label>';
                                                            }
                                                        $html .= '</div>';
                                                    }
                                                }
                                            }
                                        $html .= '</div>';
                                    }
                                $html .= '</div>';
                            }
                        }

                        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
                        {
                            $html .= '<div class="col-md-12 cart-price">';
                                $html .= '<div class="row p-3">';
                                    $html .= '<div class="col-md-12">';
                                        $html .= '<div class="d-flex align-items-center justify-content-between">';
                                            $html .= '<div class="input-group" style="width:130px">';
                                                $html .= '<span class="input-group-btn">';
                                                    $html .= '<button type="button" class="btn btn-danger btn-number" disabled="disabled" data-type="minus" onclick="QuntityIncDec(this)" data-field="quant[1]" style="border-radius:5px 0 0 5px">';
                                                        $html .= '<span class="fa fa-minus"></span>';
                                                    $html .= '</button>';
                                                $html .= '</span>';
                                                $html .= '<input type="text" name="quant[1]" id="quantity" onchange="QuntityIncDecOnChange(this)" class="form-control input-number" value="1" min="1" max="1000">';
                                                $html .= '<span class="input-group-btn">';
                                                    $html .= '<button type="button" onclick="QuntityIncDec(this)" class="btn btn-success btn-number" data-type="plus" data-field="quant[1]" style="border-radius:0 5px 5px 0">';
                                                        $html .= '<span class="fa fa-plus"></span>';
                                                    $html .= '</button>';
                                                $html .= '</span>';
                                            $html .= '</div>';
                                            $html .= '<a class="btn btn-primary" onclick="addToCart('.$item['id'].')"><i class="bi bi-cart4"></i> '.__('Add It').'</a>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // $html .= '<div class="row">';
                            //     $html .= '<div class="col-md-12 text-center mt-3">';
                            //         $html .= '<a class="btn btn-primary" onclick="addToCart('.$item['id'].')"><i class="bi bi-cart4"></i> '.__('Add It').'</a>';
                            //     $html .= '</div>';
                            // $html .= '</div>';
                        }

                    }

                    // Review Section
                    if($item['review'] == 1)
                    {
                        $html .= '<div class="col-md-12 mt-3">';
                            $html .= '<div class="accordion accordion-flush" id="reviewAccordion">';
                                $html .= '<div class="accordion-item">';
                                    $html .= "<h2 class='accordion-header' id='flush-headingOne'>";
                                        $html .= "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#flush-collapseOne' aria-expanded='false' aria-controls='flush-collapseOne'>Item Review</button>";
                                    $html .= "</h2>";
                                    $html .= '<div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#reviewAccordion">';
                                        $html .= "<div class='accordion-body'>";
                                            $html .= '<div class="row">';
                                                $html .= '<form method="POST" id="reviewForm" enctype="multipart/form-data">';
                                                    $html .= csrf_field();
                                                    $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';
                                                    $html .= '<div class="col-md-12">';
                                                        $html .= '<div class="rate">';
                                                            $html .= '<input type="radio" id="star5" class="rate" name="rating" value="5"/>';
                                                            $html .= '<label for="star5" title="text">5 stars</label>';
                                                            $html .= '<input type="radio" id="star4" class="rate" name="rating" value="4"/>';
                                                            $html .= '<label for="star4" title="text">4 stars</label>';
                                                            $html .= '<input type="radio" id="star3" class="rate" name="rating" value="3" checked />';
                                                            $html .= '<label for="star3" title="text">3 stars</label>';
                                                            $html .= '<input type="radio" id="star2" class="rate" name="rating" value="2">';
                                                            $html .= '<label for="star2" title="text">2 stars</label>';
                                                            $html .= '<input type="radio" id="star1" class="rate" name="rating" value="1"/>';
                                                            $html .= '<label for="star1" title="text">1 star</label>';
                                                        $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-12 mt-2">';
                                                        $html .= '<input type="text" name="email_id" id="email_id" class="form-control" placeholder="Enter Your Email">';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-12 mt-2">';
                                                        $html .= '<textarea class="form-control" name="item_review" id="item_review" rows="4" placeholder="Comment"></textarea>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-12 mb-2 mt-2 text-center">';
                                                        $html .= '<a class="btn btn-success" onclick="submitItemReview()" id="btn-review"><i class="bi bi-send"></i> Submit</a>';
                                                        $html .= '<button class="btn btn-success" type="button" disabled style="display:none;" id="load-btn-review">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        Please Wait...
                                                    </button>';
                                                    $html .= '</div>';
                                                $html .= '</form>';
                                            $html .= '</div>';
                                        $html .= "</div>";
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    }

                $html .= '</div>';
            }

            return response()->json([
                'success' => 1,
                'message' => 'Details has been Fetched SuccessFully...',
                'data'    => $html,
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


    // Function for Check In
    public function checkIn(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required|min:10',
            'passport' => 'required',
            'date_of_birth' => 'required',
            'nationality' => 'required',
            'arrival_date' => 'required',
            'departure_date' => 'required',
            'room_number' => 'required',
            'residence_address' => 'required',
        ]);

        $shop_id = $request->store_id;

        $shop_details = Shop::where('id',$shop_id)->first();
        $shop_name = (isset($shop_details['name'])) ? $shop_details['name'] : '';
        $shop_url = (isset($shop_details['shop_slug'])) ? $shop_details['shop_slug'] : '';
        $shop_url = asset($shop_url);
        $shop_name = '<a href="'.$shop_url.'">'.$shop_name.'</a>';
        $shop_logo = (isset($shop_details['logo'])) ? $shop_details['logo'] : '';
        $shop_logo = '<img src="'.$shop_logo.'" width="100">';

        $shop_user = UserShop::with(['user'])->where('shop_id',$shop_id)->first();
        $contact_emails = (isset($shop_user->user['contact_emails']) && !empty($shop_user->user['contact_emails'])) ? unserialize($shop_user->user['contact_emails']) : [];
        $client_email = (isset($shop_user->user['email']) && !empty($shop_user->user['email'])) ? $shop_user->user['email'] : '';


        $shop_settings = getClientSettings($shop_id);

        // CheckIN Mail Template
        $check_in_mail_form = (isset($shop_settings['check_in_mail_form'])) ? $shop_settings['check_in_mail_form'] : '';

        $age = Carbon::parse($request->date_of_birth)->age;

        $data['firstname'] = $request->firstname;
        $data['lastname'] = $request->lastname;
        $data['email'] = $request->email;
        $data['phone'] = $request->phone;
        $data['passport'] = $request->passport;
        $data['nationality'] = $request->nationality;
        $data['arrival_date'] = $request->arrival_date;
        $data['departure_date'] = $request->departure_date;
        $data['room_number'] = $request->room_number;
        $data['residence_address'] = $request->residence_address;
        $data['message'] = $request->message;
        $data['dob'] = $request->date_of_birth;
        $data['age'] = $age;

        $from_mail = $data['email'];
        $data['subject'] = "New Check In";
        $data['description'] = $data['firstname'].' '.$data['lastname'].' has been check in at : '.date('d-m-Y h:i:s',strtotime($data['arrival_date']));

        // $sendData = [
        //     'message' => $data['description'],
        //     'subject' => $data['subject'],
        //     'firstname' => $data['firstname'],
        //     'lastname' => $data['lastname'],
        //     'email' => $data['email'],
        //     'phone' => $data['phone'],
        //     'age' => $data['age'],
        //     'room_number' => $data['room_number'],
        //     'from_mail' => $from_mail,
        // ];

        try
        {
            if(count($contact_emails) > 0 && !empty($check_in_mail_form))
            {
                foreach($contact_emails as $mail)
                {
                    $to = $mail;
                    $subject = $data['subject'];

                    $message = $check_in_mail_form;
                    $message = str_replace('{shop_logo}',$shop_logo,$message);
                    $message = str_replace('{shop_name}',$shop_name,$message);
                    $message = str_replace('{firstname}',$data['firstname'],$message);
                    $message = str_replace('{lastname}',$data['lastname'],$message);
                    $message = str_replace('{phone}',$data['phone'],$message);
                    $message = str_replace('{passport_no}',$data['passport'],$message);
                    $message = str_replace('{room_no}',$data['room_number'],$message);
                    $message = str_replace('{nationality}',$data['nationality'],$message);
                    $message = str_replace('{age}',$data['age'],$message);
                    $message = str_replace('{address}',$data['residence_address'],$message);
                    $message = str_replace('{arrival_date}',date('d-m-Y h:i:s',strtotime($data['arrival_date'])),$message);
                    $message = str_replace('{departure_date}',date('d-m-Y h:i:s',strtotime($data['departure_date'])),$message);
                    $message = str_replace('{message}',$data['message'],$message);

                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    // More headers
                    $headers .= 'From: <'.$from_mail.'>' . "\r\n";

                    mail($to,$subject,$message,$headers);

                    // Mail::to($mail)->send(new CheckInMail($sendData));
                    // mail($mail,$data['subject'],$data['description']);
                }
            }
            elseif(!empty($check_in_mail_form))
            {
                    $to = $client_email;
                    $subject = $data['subject'];

                    $message = $check_in_mail_form;
                    $message = str_replace('{shop_name}',$shop_name,$message);
                    $message = str_replace('{first_name}',$data['firstname'],$message);
                    $message = str_replace('{last_name}',$data['lastname'],$message);
                    $message = str_replace('{phone}',$data['phone'],$message);
                    $message = str_replace('{passport_no}',$data['passport'],$message);
                    $message = str_replace('{room_no}',$data['room_number'],$message);
                    $message = str_replace('{nationality}',$data['nationality'],$message);
                    $message = str_replace('{age}',$data['age'],$message);
                    $message = str_replace('{address}',$data['residence_address'],$message);
                    $message = str_replace('{arrival_date}',$data['arrival_date'],$message);
                    $message = str_replace('{departure_date}',$data['departure_date'],$message);
                    $message = str_replace('{message}',$data['message'],$message);

                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    // More headers
                    $headers .= 'From: <'.$from_mail.'>' . "\r\n";

                    mail($to,$subject,$message,$headers);
                // Mail::to($client_email)->send(new CheckInMail($sendData));
                // mail($mail,$data['subject'],$data['description']);
            }

            // Insert Check In Info
            $new_check_in = new CheckIn();
            $new_check_in->shop_id = $shop_id;
            $new_check_in->firstname = $data['firstname'];
            $new_check_in->lastname = $data['lastname'];
            $new_check_in->email = $data['email'];
            $new_check_in->phone = $data['phone'];
            $new_check_in->passport_no = $data['passport'];
            $new_check_in->nationality = $data['nationality'];
            $new_check_in->arrival_date = $data['arrival_date'];
            $new_check_in->departure_date = $data['departure_date'];
            $new_check_in->room_no = $data['room_number'];
            $new_check_in->address = $data['residence_address'];
            $new_check_in->message = $data['message'];
            $new_check_in->dob = $data['dob'];
            $new_check_in->age = $data['age'];
            $new_check_in->save();

            return redirect()->back()->with('success','Check In SuccessFully....');

        }
        catch (\Throwable $th)
        {
            return redirect()->back()->with('error','Internal Server Error!');
        }

    }


    // Function for add to Cart
    public function addToCart(Request $request)
    {
        $cart_data = $request->cart_data;
        $item_id = $cart_data['item_id'];
        $quantity = $cart_data['quantity'];
        $total_amount = $cart_data['total_amount'];
        $total_amount_text = $cart_data['total_amount_text'];
        $option_id = $cart_data['option_id'];
        $shop_id = $cart_data['shop_id'];
        $currency = $cart_data['currency'];
        $categories_data =  (isset($cart_data['categories_data']) && !empty($cart_data['categories_data'])) ? json_decode($cart_data['categories_data'],true) : [];

        try
        {
            $cart = session()->get('cart', []);

            if(isset($cart[$item_id][$option_id]))
            {
                $serialized_new_options = (isset($categories_data) && count($categories_data) > 0) ? serialize($categories_data) : '';

                $items = $cart[$item_id][$option_id];

                if(count($items) > 0)
                {
                    $update = 0;
                    foreach($items as $key => $item)
                    {
                        $serialized_old_options = (isset($item['categories_data']) && !empty($item['categories_data'])) ? serialize($item['categories_data']) : '';

                        if($serialized_old_options == $serialized_new_options)
                        {
                            $new_amount = number_format($total_amount / $quantity,2);
                            $quantity = $quantity + $cart[$item_id][$option_id][$key]['quantity'];
                            $total_amount = $new_amount * $quantity;
                            $total_amount_text = Currency::currency($currency)->format($total_amount);

                            $cart[$item_id][$option_id][$key] = [
                                'item_id' => $item_id,
                                'shop_id' => $shop_id,
                                'option_id' => $option_id,
                                'quantity' => $quantity,
                                'total_amount' => $total_amount,
                                'total_amount_text' => $total_amount_text,
                                'currency' => $currency,
                                'categories_data' => $categories_data,
                            ];

                            $update = 1;
                            break;
                        }
                    }

                    if($update == 0)
                    {
                        $cart[$item_id][$option_id][] = [
                            'item_id' => $item_id,
                            'shop_id' => $shop_id,
                            'option_id' => $option_id,
                            'quantity' => $quantity,
                            'total_amount' => $total_amount,
                            'total_amount_text' => $total_amount_text,
                            'currency' => $currency,
                            'categories_data' => $categories_data,
                        ];
                    }
                }
                else
                {
                    $cart[$item_id][$option_id][] = [
                        'item_id' => $item_id,
                        'shop_id' => $shop_id,
                        'option_id' => $option_id,
                        'quantity' => $quantity,
                        'total_amount' => $total_amount,
                        'total_amount_text' => $total_amount_text,
                        'currency' => $currency,
                        'categories_data' => $categories_data,
                    ];
                }
            }
            else
            {
                $cart[$item_id][$option_id][] = [
                    'item_id' => $item_id,
                    'shop_id' => $shop_id,
                    'option_id' => $option_id,
                    'quantity' => $quantity,
                    'total_amount' => $total_amount,
                    'total_amount_text' => $total_amount_text,
                    'currency' => $currency,
                    'categories_data' => $categories_data,
                ];
            }

            session()->put('cart', $cart);
            session()->save();

            return response()->json([
                'success' => 1,
                'message' => 'Items has been Added to Cart',
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


    // Function for UpdateCart
    public function updateCart(Request $request)
    {
        $item_id = $request->item_id;
        $price_id = $request->price_id;
        $item_key = $request->item_key;
        $quantity = $request->quantity;
        // $old_quantity = $request->old_quantity;
        $currency = $request->currency;

        try
        {
            if(!is_numeric($quantity))
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Please Enter a Valid Number',
                ]);
            }
            else
            {
                if($quantity > 0)
                {
                    if($quantity > 1000)
                    {
                        return response()->json([
                            'success' => 0,
                            'message' => 'Maximum Quantity Limit is 1000!',
                        ]);
                    }
                    else
                    {
                        $cart = session()->get('cart', []);

                        if(isset($cart[$item_id][$price_id][$item_key]))
                        {
                            $old_quantity = $cart[$item_id][$price_id][$item_key]['quantity'];
                            $amount = $cart[$item_id][$price_id][$item_key]['total_amount'] / $old_quantity;
                            $total_amount = $amount * $quantity;
                            $total_amount_text = Currency::currency($currency)->format($total_amount);

                            $cart[$item_id][$price_id][$item_key]['quantity'] = $quantity;
                            $cart[$item_id][$price_id][$item_key]['total_amount'] = $total_amount;
                            $cart[$item_id][$price_id][$item_key]['total_amount_text'] = $total_amount_text;

                            session()->put('cart', $cart);
                            session()->save();
                        }

                        return response()->json([
                            'success' => 1,
                            'message' => 'Cart has been Updated SuccessFully...',
                        ]);
                    }
                }
                else
                {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Minumum 1 Quanity is Required!',
                    ]);
                }
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


    // Function for Remove Cart Item
    public function removeCartItem(Request $request)
    {
        $item_id = $request->item_id;
        $price_id = $request->price_id;
        $item_key = $request->item_key;

        try
        {
            $cart = session()->get('cart', []);

            if(isset($cart[$item_id][$price_id][$item_key]))
            {
                unset($cart[$item_id][$price_id][$item_key]);
                session()->put('cart', $cart);
                session()->save();
            }

            return response()->json([
                'success' => 1,
                'message' => 'Item has been Removed SuccessFully...',
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


    // Function for Display Cart Details
    public function viewCart($shop_slug)
    {
        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

        // Order Settings
        $order_settings = getOrderSettings($shop_id);

        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);


        if(!isset($package_permissions['ordering']) || empty($package_permissions['ordering']) || $package_permissions['ordering'] != 1)
        {
            session()->remove('cart');
            session()->save();
            return redirect()->route('restaurant',$shop_slug);
        }

        $discount_per = (isset($order_settings['discount_percentage']) && ($order_settings['discount_percentage'] > 0)) ? $order_settings['discount_percentage'] : 0;
        $discount_type = (isset($order_settings['discount_type'])) ? $order_settings['discount_type'] : 'percentage';
        session()->put('discount_per',$discount_per);
        session()->put('discount_type',$discount_type);
        session()->save();

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Get all Additional Language of Shop
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

        // Current Languge Code
        $data['current_lang_code'] = (session()->has('locale')) ? session()->get('locale') : 'en';

        $data['cart'] = session()->get('cart', []);

        if(count($data['cart']) > 0)
        {
            return view('shop.view_cart',$data);
        }
        else
        {
            return redirect()->route('restaurant',$shop_slug);
        }
    }


    // Set Checkout Type
    public function setCheckoutType(Request $request)
    {
        $checkout_type = $request->check_type;

        try
        {
            session()->put('checkout_type',$checkout_type);
            session()->save();

            return response()->json([
                'success' => 1,
                "message" => "Redirecting to Checkout SuccessFully...",
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                "message" => "Internal server error!",
            ]);
        }
    }


    // Function for Redirect Checkout Page
    public function cartCheckout($shop_slug)
    {
        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

        // Get Subscription ID
        $subscription_id = getClientSubscriptionID($shop_id);

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);


        if(!isset($package_permissions['ordering']) || empty($package_permissions['ordering']) || $package_permissions['ordering'] != 1)
        {
            session()->remove('cart');
            session()->save();
            return redirect()->route('restaurant',$shop_slug);
        }

        $order_settings = getOrderSettings($shop_id);
        $min_amount_for_delivery = (isset($order_settings['min_amount_for_delivery'])) ? $order_settings['min_amount_for_delivery'] : '';
        $total_cart_amount = getCartTotal();

        $data['cart'] = session()->get('cart', []);

        $data['checkout_type'] = session()->get('checkout_type', '');

        if($data['checkout_type'] == 'delivery')
        {
            if(!empty($min_amount_for_delivery) && ($total_cart_amount < $min_amount_for_delivery))
            {
                return redirect()->back();
            }
        }

        $delivery_schedule = checkDeliverySchedule($shop_id);

        if($delivery_schedule == 0)
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','We are sorry the venue is no longer accepting orders.');
        }

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Get all Additional Language of Shop
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

        // Current Languge Code
        $data['current_lang_code'] = (session()->has('locale')) ? session()->get('locale') : 'en';

        if($data['checkout_type'] == '')
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','UnAuthorized Action!');
        }

        if(count($data['cart']) > 0)
        {
            return view('shop.view_checkout',$data);
        }
        else
        {
            return redirect()->route('restaurant',$shop_slug);
        }
    }


    // Function for Processing Checkout
    public function checkoutProcessing($shop_slug, Request $request)
    {
        // Checkout Type & Payment Method
        $checkout_type = $request->checkout_type;
        $payment_method = $request->payment_method;
        $discount_per = session()->get('discount_per');
        $discount_type = session()->get('discount_type');

        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        $user_id = (isset( $data['shop_details']->usershop->user->id)) ?  $data['shop_details']->usershop->user->id : '';

        $user_details = User::where('id',$user_id)->first();
        $sgst = (isset($user_details['sgst'])) ? $user_details['sgst'] : 0;
        $cgst = (isset($user_details['cgst'])) ? $user_details['cgst'] : 0;

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';
        $shop_name = isset($data['shop_details']->name) ? $data['shop_details']->name : '';
        $shop_url = (isset($data['shop_details']->shop_slug)) ? $data['shop_details']->shop_slug : '';
        $shop_url = asset($shop_url);
        $shop_name = '<a href="'.$shop_url.'">'.$shop_name.'</a>';
        $shop_logo = (isset($data['shop_details']->logo)) ? $data['shop_details']->logo : '';
        $shop_logo = '<img src="'.$shop_logo.'" width="200">';

        // Order Settings
        $order_settings = getOrderSettings($shop_id);

        // Optional Fields
        $email_field = (isset($order_settings['email_field']) && $order_settings['email_field'] == 1) ? $order_settings['email_field'] : 0;
        $floor_field = (isset($order_settings['floor_field']) && $order_settings['floor_field'] == 1) ? $order_settings['floor_field'] : 0;
        $door_bell_field = (isset($order_settings['door_bell_field']) && $order_settings['door_bell_field'] == 1) ? $order_settings['door_bell_field'] : 0;
        $full_name_field = (isset($order_settings['full_name_field']) && $order_settings['full_name_field'] == 1) ? $order_settings['full_name_field'] : 0;
        $instructions_field = (isset($order_settings['instructions_field']) && $order_settings['instructions_field'] == 1) ? $order_settings['instructions_field'] : 0;
        $live_address_field = (isset($order_settings['live_address_field']) && $order_settings['live_address_field'] == 1) ? $order_settings['live_address_field'] : 0;

        if($checkout_type == 'takeaway')
        {
            $rules = [
                'phone' => 'required|max:10|min:10',
            ];

            if($full_name_field == 1)
            {
                $rules += [
                    'name' => 'required',
                ];
            }
            else
            {
                $rules += [
                    'firstname' => 'required',
                    'lastname' => 'required',
                ];
            }

            if($email_field == 1)
            {
                $rules += [
                    'email' => 'required|email',
                ];
            }

            $request->validate($rules);

        }
        elseif($checkout_type == 'table_service')
        {
            $request->validate([
                'table' => 'required',
            ]);
        }
        elseif($checkout_type == 'room_delivery')
        {
            $rules = [
                'room' => 'required',
            ];

            if($full_name_field == 1)
            {
                $rules += [
                    'name' => 'required',
                ];
            }
            else
            {
                $rules += [
                    'firstname' => 'required',
                    'lastname' => 'required',
                ];
            }

            $request->validate($rules);
        }
        elseif($checkout_type == 'delivery')
        {
            $rules = [
                'address' => 'required',
                'phone' => 'required|max:10|min:10',
            ];

            if($full_name_field == 1)
            {
                $rules += [
                    'name' => 'required',
                ];
            }
            else
            {
                $rules += [
                    'firstname' => 'required',
                    'lastname' => 'required',
                ];
            }

            if($email_field == 1)
            {
                $rules += [
                    'email' => 'required|email',
                ];
            }

            $request->validate($rules);
        }

        $delivery_schedule = checkDeliverySchedule($shop_id);

        if($delivery_schedule == 0)
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','We are sorry the venue is no longer accepting orders.');
        }

        $min_amount_for_delivery = (isset($order_settings['min_amount_for_delivery'])) ? $order_settings['min_amount_for_delivery'] : '';
        $total_cart_amount = getCartTotal();

        if($checkout_type == 'delivery')
        {
            if(!empty($min_amount_for_delivery) && ($total_cart_amount < $min_amount_for_delivery))
            {
                return redirect()->route('shop.cart',$shop_slug);
            }

            $latitude = isset($request->latitude) ? $request->latitude : '';
            $longitude = isset($request->longitude) ? $request->longitude : '';

            if($live_address_field == 1)
            {
                $delivey_avaialbility = checkDeliveryAvilability($shop_id,$latitude,$longitude);

                if($delivey_avaialbility == 0)
                {
                    $validator = Validator::make([], []);
                    $validator->getMessageBag()->add('address', 'Sorry your address is out of our delivery range.');
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }
        }


        if(isset($order_settings['auto_order_approval']) && $order_settings['auto_order_approval'] == 1)
        {
            $order_status = 'accepted';
            $is_new = 0;
        }
        else
        {
            $order_status = 'pending';
            $is_new = 1;
        }

        $shop_settings = getClientSettings($shop_id);

        // Shop Currency
        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Get all Additional Language of Shop
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

        // Keys
        $name_key = $current_lang_code."_name";
        $label_key = $current_lang_code."_label";

        $cart = session()->get('cart', []);

        if(count($cart) == 0)
        {
            return redirect()->route('restaurant',$shop_slug);
        }

        // Ip Address
        $user_ip = $request->ip();

        $total_amount = 0;
        $subtotal_amount = 0;
        $discount_amount = 0;
        $gst_amount = 0;
        $total_qty = 0;

        // Order Mail Template
        $orders_mail_form_client = (isset($shop_settings['orders_mail_form_client'])) ? $shop_settings['orders_mail_form_client'] : '';

        $shop_user = UserShop::with(['user'])->where('shop_id',$shop_id)->first();
        $contact_emails = (isset($shop_user->user['contact_emails']) && !empty($shop_user->user['contact_emails'])) ? unserialize($shop_user->user['contact_emails']) : [];

        if($payment_method == 'cash' || $payment_method == 'cash_pos')
        {
            // New Order
            $order = new Order();
            $order->shop_id = $shop_id;
            $order->ip_address = $user_ip;
            $order->currency = $currency;
            $order->checkout_type = $checkout_type;
            $order->payment_method = $payment_method;
            $order->order_status = $order_status;
            $order->is_new = $is_new;
            $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';

            if($checkout_type == 'takeaway')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }

                if($email_field == 1)
                {
                    $order->email = $request->email;
                }
                $order->phone = $request->phone;
            }
            elseif($checkout_type == 'table_service')
            {
                $order->table = $request->table;
            }
            elseif($checkout_type == 'room_delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }
                $order->room = $request->room;
                $order->delivery_time = (isset($request->delivery_time)) ? $request->delivery_time : '';
            }
            elseif($checkout_type == 'delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }

                if($email_field == 1)
                {
                    $order->email = $request->email;
                }

                $order->phone = $request->phone;
                $order->address = $request->address;

                if($live_address_field == 1)
                {
                    $order->latitude = $request->latitude;
                    $order->longitude = $request->longitude;
                }

                if($floor_field == 1)
                {
                    $order->floor = $request->floor;
                }

                if($door_bell_field == 1)
                {
                    $order->door_bell = $request->door_bell;
                }

                if($instructions_field == 1)
                {
                    $order->instructions = $request->instructions;
                }
            }

            $order->save();

            $from_email = (isset($request->email)) ? $request->email : '';

            // Insert Order Items
            if($order->id)
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
                                    $otpions_arr = [];
                                    // Item Details
                                    $item_details = Items::where('id',$cart_item['item_id'])->first();
                                    $item_discount = (isset($item_details['discount'])) ? $item_details['discount'] : 0;
                                    $item_discount_type = (isset($item_details['discount_type'])) ? $item_details['discount_type'] : 'percentage';
                                    $item_name = (isset($item_details[$name_key])) ? $item_details[$name_key] : '';

                                    //Price Details
                                    $price_detail = ItemPrice::where('id',$cart_item['option_id'])->first();
                                    $price_label = (isset($price_detail[$label_key])) ? $price_detail[$label_key] : '';
                                    $item_price = (isset($price_detail['price'])) ? $price_detail['price'] : 0;

                                    if($item_discount > 0)
                                    {
                                        if($item_discount_type == 'fixed')
                                        {
                                            $item_price = number_format($item_price - $item_discount,2);
                                        }
                                        else
                                        {
                                            $dis_per = $item_price * $item_discount / 100;
                                            $item_price = number_format($item_price - $dis_per,2);
                                        }
                                    }

                                    if(!empty($price_label))
                                    {
                                        $otpions_arr[] = $price_label;
                                    }

                                    $item_total_amount = $cart_item['total_amount'];
                                    $total_amount_text = $cart_item['total_amount_text'];
                                    $categories_data = (isset($cart_item['categories_data']) && !empty($cart_item['categories_data'])) ? $cart_item['categories_data'] : [];

                                    $subtotal_amount += $item_total_amount;
                                    $total_qty += $cart_item['quantity'];

                                    if(count($categories_data) > 0)
                                    {
                                        foreach($categories_data as $option_id)
                                        {
                                            $my_opt = $option_id;

                                            if(is_array($my_opt))
                                            {
                                                if(count($my_opt) > 0)
                                                {
                                                    foreach ($my_opt as $optid)
                                                    {
                                                        $opt_price_dt = OptionPrice::where('id',$optid)->first();$opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                        $otpions_arr[] = $opt_price_name;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $opt_price_dt = OptionPrice::where('id',$my_opt)->first();
                                                $opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                $otpions_arr[] = $opt_price_name;
                                            }
                                        }
                                    }

                                    // Order Items
                                    $order_items = new OrderItems();
                                    $order_items->shop_id = $shop_id;
                                    $order_items->order_id = $order->id;
                                    $order_items->item_id = $cart_item['item_id'];
                                    $order_items->item_name = $item_name;
                                    $order_items->item_price = $item_price;
                                    $order_items->item_price_label = $price_label;
                                    $order_items->item_qty = $cart_item['quantity'];
                                    $order_items->sub_total = $item_total_amount;
                                    $order_items->sub_total_text = $total_amount_text;
                                    $order_items->options = serialize($otpions_arr);
                                    $order_items->save();
                                }
                            }
                        }
                    }
                }

                $update_order = Order::find($order->id);
                $update_order->order_subtotal = $subtotal_amount;

                $total_amount += $subtotal_amount;

                if($discount_per > 0)
                {
                    if($discount_type == 'fixed')
                    {
                        $discount_amount = $discount_per;
                    }
                    else
                    {
                        $discount_amount = ($subtotal_amount * $discount_per) / 100;
                    }

                    $update_order->discount_per = $discount_per;
                    $update_order->discount_type = $discount_type;
                    $update_order->discount_value = $discount_amount;
                    $total_amount = $total_amount - $discount_amount;
                }

                // CGST & SGST
                if($cgst > 0 && $sgst > 0)
                {
                    $gst_per =  $cgst + $sgst;
                    $gst_amount = ( $total_amount * $gst_per) / 100;
                    $update_order->cgst = $cgst;
                    $update_order->sgst = $sgst;
                    $update_order->gst_amount = $gst_amount;
                    $total_amount += $gst_amount;
                }

                $total_amount = $total_amount;

                $update_order->order_total = $total_amount;
                $update_order->order_total_text = Currency::currency($currency)->format($total_amount);
                $update_order->total_qty = $total_qty;
                $update_order->update();

                // Mail Sent Functionality
                // if($checkout_type == 'takeaway' || $checkout_type == 'delivery')
                // {
                //     $order_details = Order::with(['order_items'])->where('id',$order->id)->first();
                //     $order_items = (isset($order_details->order_items) && count($order_details->order_items) > 0) ? $order_details->order_items : [];

                //     // Sent Mail to Shop Owner
                //     if(count($contact_emails) > 0 && !empty($orders_mail_form_client) && $email_field == 1)
                //     {
                //         foreach($contact_emails as $mail)
                //         {
                //             $to = $mail;
                //             $subject = "New Order";

                //             if($full_name_field == 1)
                //             {
                //                 $fname = (isset($request->name)) ? $request->name : '';
                //                 $lname = "";
                //             }
                //             else
                //             {
                //                 $fname = (isset($request->firstname)) ? $request->firstname : '';
                //                 $lname = (isset($request->lastname)) ? $request->lastname : '';
                //             }


                //             $message = $orders_mail_form_client;
                //             $message = str_replace('{shop_logo}',$shop_logo,$message);
                //             $message = str_replace('{shop_name}',$shop_name,$message);
                //             $message = str_replace('{firstname}',$fname,$message);
                //             $message = str_replace('{lastname}',$lname,$message);
                //             $message = str_replace('{order_id}',$order->id,$message);
                //             $message = str_replace('{order_type}',$checkout_type,$message);
                //             $message = str_replace('{payment_method}',$payment_method,$message);

                //             // Order Items
                //             $order_html  = "";
                //             $order_html .= '<div>';
                //                 $order_html .= '<table style="width:100%; border:1px solid gray;border-collapse: collapse;">';
                //                     $order_html .= '<thead style="background:lightgray; color:white">';
                //                         $order_html .= '<tr style="text-transform: uppercase!important;    font-weight: 700!important;">';
                //                             $order_html .= '<th style="text-align: left!important;width: 60%;padding:10px">Item</th>';
                //                             $order_html .= '<th style="text-align: center!important;padding:10px">Qty.</th>';
                //                             $order_html .= '<th style="text-align: right!important;padding:10px">Item Total</th>';
                //                         $order_html .= '</tr>';
                //                     $order_html .= '</thead>';
                //                     $order_html .= '<tbody style="font-weight: 600!important;">';

                //                         if(count($order_items) > 0)
                //                         {
                //                             foreach($order_items as $order_item)
                //                             {
                //                                 $item_dt = itemDetails($order_item['item_id']);
                //                                 $item_image = (isset($item_dt['image']) && !empty($item_dt['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image']) : asset('public/client_images/not-found/no_image_1.jpg');
                //                                 $options_array = (isset($order_item['options']) && !empty($order_item['options'])) ? unserialize($order_item['options']) : '';
                //                                 if(count($options_array) > 0)
                //                                 {
                //                                     $options_array = implode(', ',$options_array);
                //                                 }

                //                                 $order_html .= '<tr>';

                //                                     $order_html .= '<td style="text-align: left!important;padding:10px; border-bottom:1px solid gray;">';
                //                                         $order_html .= '<div style="align-items: center!important;display: flex!important;">';
                //                                             $order_html .= '<a style="display: inline-block;
                //                                             flex-shrink: 0;position: relative;border-radius: 0.75rem;">';
                //                                                 $order_html .= '<span style="width: 50px;
                //                                                 height: 50px;display: flex;
                //                                                 align-items: center;
                //                                                 justify-content: center;
                //                                                 font-weight: 500;background-repeat: no-repeat;
                //                                                 background-position: center center;
                //                                                 background-size: cover;
                //                                                 border-radius: 0.75rem; background-image:url('.$item_image.')"></span>';
                //                                             $order_html .= '</a>';
                //                                             $order_html .= '<div style="display: block;    margin-left: 3rem!important;">';
                //                                                 $order_html .= '<a style="font-weight: 700!important;color: #7e8299;
                //                                                 ">'.$order_item->item_name.'</a>';

                //                                                 if(!empty($options_array))
                //                                                 {
                //                                                     $order_html .= '<div style="color: #a19e9e;display: block;">'.$options_array.'</div>';
                //                                                 }
                //                                                 else
                //                                                 {
                //                                                     $order_html .= '<div style="color: #a19e9e;display: block;"></div>';
                //                                                 }

                //                                             $order_html .= '</div>';
                //                                         $order_html .= '</div>';
                //                                     $order_html .= '</td>';

                //                                     $order_html .= '<td style="text-align: center!important;padding:10px; border-bottom:1px solid gray;">';
                //                                         $order_html .= $order_item['item_qty'];
                //                                     $order_html .= '</td>';

                //                                     $order_html .= '<td style="text-align: right!important;padding:10px; border-bottom:1px solid gray;">';
                //                                         $order_html .= Currency::currency($currency)->format($order_item['sub_total']);
                //                                     $order_html .= '</td>';

                //                                 $order_html .= '</tr>';
                //                             }
                //                         }

                //                     $order_html .= '</tbody>';
                //                 $order_html .= '</table>';
                //             $order_html .= '</div>';
                //             $message = str_replace('{items}',$order_html,$message);

                //             // Order Total
                //             $order_total_html = "";
                //             $order_total_html .= '<div>';
                //                 $order_total_html .= '<table style="width:50%; border:1px solid gray;border-collapse: collapse;">';
                //                     $order_total_html .= '<tbody style="font-weight: 700!important;">';
                //                         $order_total_html .= '<tr>';
                //                             $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Sub Total : </td>';
                //                             $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.Currency::currency($currency)->format($order_details->order_subtotal).'</td>';
                //                         $order_total_html .= '</tr>';

                //                         if($order_details->discount_per > 0)
                //                         {
                //                             $order_total_html .= '<tr>';
                //                                 $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Discount : </td>';
                //                                 if($order_details->discount_per == 'fixed')
                //                                 {
                //                                     $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.Currency::currency($currency)->format($order_details->discount_per).'</td>';
                //                                 }
                //                                 else
                //                                 {
                //                                     $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.$order_details->discount_per.'%</td>';
                //                                 }
                //                             $order_total_html .= '</tr>';
                //                         }

                //                         if($order_details->cgst > 0 && $order_details->sgst > 0)
                //                         {
                //                             $gst_amt = $order_details->cgst + $order_details->sgst;
                //                             $gst_amt = $order_details->gst_amount / $gst_amt;

                //                             $order_total_html .= '<tr>';
                //                                 $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('CGST.').' ('.$order_details->cgst.'%)</td>';
                //                                 $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->cgst * $gst_amt).'</td>';
                //                             $order_total_html .= '</tr>';
                //                             $order_total_html .= '<tr>';
                //                                 $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('SGST.').' ('.$order_details->sgst.'%)</td>';
                //                                 $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->sgst * $gst_amt).'</td>';
                //                             $order_total_html .= '</tr>';
                //                         }

                //                         $order_total_html .= '<tr>';
                //                             $order_total_html .= '<td style="padding:10px;">Total : </td>';
                //                             $order_total_html .= '<td style="padding:10px;">';
                //                                 $order_total_html .= Currency::currency($currency)->format($order_details->order_total);
                //                             $order_total_html .= '</td>';
                //                         $order_total_html .= '</tr>';

                //                     $order_total_html .= '</tbody>';
                //                 $order_total_html .= '</table>';
                //             $order_total_html .= '</div>';
                //             $message = str_replace('{total}',$order_total_html,$message);

                //             $headers = "MIME-Version: 1.0" . "\r\n";
                //             $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                //             // More headers
                //             $headers .= 'From: <'.$from_email.'>' . "\r\n";

                //             mail($to,$subject,$message,$headers);

                //         }
                //     }
                // }

            }

            session()->forget('cart');
            session()->forget('discount_per');
            session()->forget('discount_type');
            session()->forget('cust_lat');
            session()->forget('cust_long');
            session()->forget('cust_address');
            session()->save();

            return redirect()->route('shop.checkout.success',[$shop_slug,encrypt($order->id)]);
        }
        elseif($payment_method == 'paypal')
        {
            session()->put('order_details',$request->all());
            session()->save();
            return redirect()->route('paypal.payment',$shop_slug);
        }
        elseif($payment_method == 'every_pay')
        {
            session()->put('order_details',$request->all());
            session()->save();
            return redirect()->route('everypay.checkout.view',$shop_slug);
        }
        elseif($payment_method == 'upi_payment')
        {
            // New Order
            $order = new Order();
            $order->shop_id = $shop_id;
            $order->ip_address = $user_ip;
            $order->currency = $currency;
            $order->checkout_type = $checkout_type;
            $order->payment_method = $payment_method;
            $order->order_status = $order_status;
            $order->is_new = $is_new;
            $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';

            if($checkout_type == 'takeaway')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }

                if($email_field == 1)
                {
                    $order->email = $request->email;
                }
                $order->phone = $request->phone;
            }
            elseif($checkout_type == 'table_service')
            {
                $order->table = $request->table;
            }
            elseif($checkout_type == 'room_delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }
                $order->room = $request->room;
                $order->delivery_time = (isset($request->delivery_time)) ? $request->delivery_time : '';
            }
            elseif($checkout_type == 'delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname = $request->name;
                }
                else
                {
                    $order->firstname = $request->firstname;
                    $order->lastname = $request->lastname;
                }

                if($email_field == 1)
                {
                    $order->email = $request->email;
                }

                $order->phone = $request->phone;
                $order->address = $request->address;

                if($live_address_field == 1)
                {
                    $order->latitude = $request->latitude;
                    $order->longitude = $request->longitude;
                }

                if($floor_field == 1)
                {
                    $order->floor = $request->floor;
                }

                if($door_bell_field == 1)
                {
                    $order->door_bell = $request->door_bell;
                }

                if($instructions_field == 1)
                {
                    $order->instructions = $request->instructions;
                }
            }

            $order->save();

            $from_email = (isset($request->email)) ? $request->email : '';

            // Insert Order Items
            if($order->id)
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
                                    $otpions_arr = [];
                                    // Item Details
                                    $item_details = Items::where('id',$cart_item['item_id'])->first();
                                    $item_discount = (isset($item_details['discount'])) ? $item_details['discount'] : 0;
                                    $item_discount_type = (isset($item_details['discount_type'])) ? $item_details['discount_type'] : 'percentage';
                                    $item_name = (isset($item_details[$name_key])) ? $item_details[$name_key] : '';

                                    //Price Details
                                    $price_detail = ItemPrice::where('id',$cart_item['option_id'])->first();
                                    $price_label = (isset($price_detail[$label_key])) ? $price_detail[$label_key] : '';
                                    $item_price = (isset($price_detail['price'])) ? $price_detail['price'] : 0;

                                    if($item_discount > 0)
                                    {
                                        if($item_discount_type == 'fixed')
                                        {
                                            $item_price = number_format($item_price - $item_discount,2);
                                        }
                                        else
                                        {
                                            $dis_per = $item_price * $item_discount / 100;
                                            $item_price = number_format($item_price - $dis_per,2);
                                        }
                                    }

                                    if(!empty($price_label))
                                    {
                                        $otpions_arr[] = $price_label;
                                    }

                                    $item_total_amount = $cart_item['total_amount'];
                                    $total_amount_text = $cart_item['total_amount_text'];
                                    $categories_data = (isset($cart_item['categories_data']) && !empty($cart_item['categories_data'])) ? $cart_item['categories_data'] : [];

                                    $subtotal_amount += $item_total_amount;
                                    $total_qty += $cart_item['quantity'];

                                    if(count($categories_data) > 0)
                                    {
                                        foreach($categories_data as $option_id)
                                        {
                                            $my_opt = $option_id;

                                            if(is_array($my_opt))
                                            {
                                                if(count($my_opt) > 0)
                                                {
                                                    foreach ($my_opt as $optid)
                                                    {
                                                        $opt_price_dt = OptionPrice::where('id',$optid)->first();$opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                        $otpions_arr[] = $opt_price_name;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $opt_price_dt = OptionPrice::where('id',$my_opt)->first();
                                                $opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                $otpions_arr[] = $opt_price_name;
                                            }
                                        }
                                    }

                                    // Order Items
                                    $order_items = new OrderItems();
                                    $order_items->shop_id = $shop_id;
                                    $order_items->order_id = $order->id;
                                    $order_items->item_id = $cart_item['item_id'];
                                    $order_items->item_name = $item_name;
                                    $order_items->item_price = $item_price;
                                    $order_items->item_price_label = $price_label;
                                    $order_items->item_qty = $cart_item['quantity'];
                                    $order_items->sub_total = $item_total_amount;
                                    $order_items->sub_total_text = $total_amount_text;
                                    $order_items->options = serialize($otpions_arr);
                                    $order_items->save();
                                }
                            }
                        }
                    }
                }

                $update_order = Order::find($order->id);
                $update_order->order_subtotal = $subtotal_amount;

                $total_amount += $subtotal_amount;

                if($discount_per > 0)
                {
                    if($discount_type == 'fixed')
                    {
                        $discount_amount = $discount_per;
                    }
                    else
                    {
                        $discount_amount = ($subtotal_amount * $discount_per) / 100;
                    }

                    $update_order->discount_per = $discount_per;
                    $update_order->discount_type = $discount_type;
                    $update_order->discount_value = $discount_amount;
                    $total_amount = $total_amount - $discount_amount;
                }

                // CGST & SGST
                if($cgst > 0 && $sgst > 0)
                {
                    $gst_per =  $cgst + $sgst;
                    $gst_amount = ( $total_amount * $gst_per) / 100;
                    $update_order->cgst = $cgst;
                    $update_order->sgst = $sgst;
                    $update_order->gst_amount = $gst_amount;
                    $total_amount += $gst_amount;
                }

                $total_amount = $total_amount;

                $update_order->order_total = $total_amount;
                $update_order->order_total_text = Currency::currency($currency)->format($total_amount);
                $update_order->total_qty = $total_qty;
                $update_order->update();


                // Mail Sent Functionality
                if($checkout_type == 'takeaway' || $checkout_type == 'delivery')
                {
                    $order_details = Order::with(['order_items'])->where('id',$order->id)->first();
                    $order_items = (isset($order_details->order_items) && count($order_details->order_items) > 0) ? $order_details->order_items : [];

                    // Sent Mail to Shop Owner
                    if(count($contact_emails) > 0 && !empty($orders_mail_form_client) && $email_field == 1)
                    {
                        foreach($contact_emails as $mail)
                        {
                            $to = $mail;
                            $subject = "New Order";
                            if($full_name_field == 1)
                            {
                                $fname = (isset($request->name)) ? $request->name : '';
                                $lname = "";
                            }
                            else
                            {
                                $fname = (isset($request->firstname)) ? $request->firstname : '';
                                $lname = (isset($request->lastname)) ? $request->lastname : '';
                            }

                            $message = $orders_mail_form_client;
                            $message = str_replace('{shop_logo}',$shop_logo,$message);
                            $message = str_replace('{shop_name}',$shop_name,$message);
                            $message = str_replace('{firstname}',$fname,$message);
                            $message = str_replace('{lastname}',$lname,$message);
                            $message = str_replace('{order_id}',$order->id,$message);
                            $message = str_replace('{order_type}',$checkout_type,$message);
                            $message = str_replace('{payment_method}',$payment_method,$message);

                            // Order Items
                            $order_html  = "";
                            $order_html .= '<div>';
                                $order_html .= '<table style="width:100%; border:1px solid gray;border-collapse: collapse;">';
                                    $order_html .= '<thead style="background:lightgray; color:white">';
                                        $order_html .= '<tr style="text-transform: uppercase!important;    font-weight: 700!important;">';
                                            $order_html .= '<th style="text-align: left!important;width: 60%;padding:10px">Item</th>';
                                            $order_html .= '<th style="text-align: center!important;padding:10px">Qty.</th>';
                                            $order_html .= '<th style="text-align: right!important;padding:10px">Item Total</th>';
                                        $order_html .= '</tr>';
                                    $order_html .= '</thead>';
                                    $order_html .= '<tbody style="font-weight: 600!important;">';

                                        if(count($order_items) > 0)
                                        {
                                            foreach($order_items as $order_item)
                                            {
                                                $item_dt = itemDetails($order_item['item_id']);
                                                $item_image = (isset($item_dt['image']) && !empty($item_dt['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image']) : asset('public/client_images/not-found/no_image_1.jpg');
                                                $options_array = (isset($order_item['options']) && !empty($order_item['options'])) ? unserialize($order_item['options']) : '';
                                                if(count($options_array) > 0)
                                                {
                                                    $options_array = implode(', ',$options_array);
                                                }

                                                $order_html .= '<tr>';

                                                    $order_html .= '<td style="text-align: left!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= '<div style="align-items: center!important;display: flex!important;">';
                                                            $order_html .= '<a style="display: inline-block;
                                                            flex-shrink: 0;position: relative;border-radius: 0.75rem;">';
                                                                $order_html .= '<span style="width: 50px;
                                                                height: 50px;display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                font-weight: 500;background-repeat: no-repeat;
                                                                background-position: center center;
                                                                background-size: cover;
                                                                border-radius: 0.75rem; background-image:url('.$item_image.')"></span>';
                                                            $order_html .= '</a>';
                                                            $order_html .= '<div style="display: block;    margin-left: 3rem!important;">';
                                                                $order_html .= '<a style="font-weight: 700!important;color: #7e8299;
                                                                ">'.$order_item->item_name.'</a>';

                                                                if(!empty($options_array))
                                                                {
                                                                    $order_html .= '<div style="color: #a19e9e;display: block;">'.$options_array.'</div>';
                                                                }
                                                                else
                                                                {
                                                                    $order_html .= '<div style="color: #a19e9e;display: block;"></div>';
                                                                }

                                                            $order_html .= '</div>';
                                                        $order_html .= '</div>';
                                                    $order_html .= '</td>';

                                                    $order_html .= '<td style="text-align: center!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= $order_item['item_qty'];
                                                    $order_html .= '</td>';

                                                    $order_html .= '<td style="text-align: right!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= Currency::currency($currency)->format($order_item['sub_total']);
                                                    $order_html .= '</td>';

                                                $order_html .= '</tr>';
                                            }
                                        }

                                    $order_html .= '</tbody>';
                                $order_html .= '</table>';
                            $order_html .= '</div>';
                            $message = str_replace('{items}',$order_html,$message);

                            // Order Total
                            $order_total_html = "";
                            $order_total_html .= '<div>';
                                $order_total_html .= '<table style="width:50%; border:1px solid gray;border-collapse: collapse;">';
                                    $order_total_html .= '<tbody style="font-weight: 700!important;">';
                                        $order_total_html .= '<tr>';
                                            $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Sub Total : </td>';
                                            $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.Currency::currency($currency)->format($order_details->order_subtotal).'</td>';
                                        $order_total_html .= '</tr>';

                                        if($order_details->discount_per > 0)
                                        {
                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Discount : </td>';
                                                if($order_details->discount_per == 'fixed')
                                                {
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.Currency::currency($currency)->format($order_details->discount_per).'</td>';
                                                }
                                                else
                                                {
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.$order_details->discount_per.'%</td>';
                                                }
                                            $order_total_html .= '</tr>';
                                        }

                                        if($order_details->cgst > 0 && $order_details->sgst > 0)
                                        {
                                            $gst_amt = $order_details->cgst + $order_details->sgst;
                                            $gst_amt = $order_details->gst_amount / $gst_amt;

                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('CGST.').' ('.$order_details->cgst.'%)</td>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->cgst * $gst_amt).'</td>';
                                            $order_total_html .= '</tr>';
                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('SGST.').' ('.$order_details->sgst.'%)</td>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->sgst * $gst_amt).'</td>';
                                            $order_total_html .= '</tr>';
                                        }

                                        $order_total_html .= '<tr>';
                                            $order_total_html .= '<td style="padding:10px;">Total : </td>';
                                            $order_total_html .= '<td style="padding:10px;">';
                                                $order_total_html .= Currency::currency($currency)->format($order_details->order_total);
                                            $order_total_html .= '</td>';
                                        $order_total_html .= '</tr>';

                                    $order_total_html .= '</tbody>';
                                $order_total_html .= '</table>';
                            $order_total_html .= '</div>';
                            $message = str_replace('{total}',$order_total_html,$message);

                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                            // More headers
                            $headers .= 'From: <'.$from_email.'>' . "\r\n";

                            mail($to,$subject,$message,$headers);

                        }
                    }
                }
            }

            session()->forget('cart');
            session()->forget('discount_per');
            session()->forget('discount_type');
            session()->forget('cust_lat');
            session()->forget('cust_long');
            session()->forget('cust_address');
            session()->save();

            $data['currency'] = $currency;
            $data['amount'] = $total_amount;
            $data['order_id'] = $order->id;
            $data['success_url'] = route('shop.checkout.success',[$shop_slug,encrypt($order->id)]);

            return view('shop.upi_payment',$data);
        }
    }


    // Function for redirect Checkout Success
    public function checkoutSuccess($shop_slug, $orderID)
    {
        try
        {
            $order_id = decrypt($orderID);

            $data['order_details'] = Order::where('id',$order_id)->first();

            if(empty($data['order_details']))
            {
                return redirect()->route('restaurant',$shop_slug);
            }

            // Shop Details
            $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

            // Shop ID
            $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

            // Primary Language Details
            $language_setting = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
            $data['primary_language_details'] = getLangDetails($primary_lang_id);

            // Get all Additional Language of Shop
            $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

            // Current Languge Code
            $data['current_lang_code'] = (session()->has('locale')) ? session()->get('locale') : 'en';

            return view('shop.checkout_success',$data);
        }
        catch (\Throwable $th)
        {
           return redirect()->route('restaurant',$shop_slug)->with('error','Internal Server Error!');
        }
    }


    // Function for Check Order Status
    public function checkOrderStatus(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id',$order_id)->first();
        $order_status = (isset($order['order_status'])) ? $order['order_status'] : '';
        return response()->json([
            'success' => 1,
            'status' => $order_status,
        ]);
    }


    // Function for Send Item Review
    public function sendItemReview(Request $request)
    {

        $rules = [
            'item_review' => 'required',
        ];

        if(!empty($request->email_id))
        {
            $rules += [
                'email_id' => 'email',
            ];
        }

       $request->validate($rules);

        try
        {

            $item_id = (isset($request->item_id)) ? $request->item_id : '';
            $comment = (isset($request->item_review)) ? $request->item_review : '';
            $rating = (isset($request->rating)) ? $request->rating : '';
            $email = (isset($request->email_id)) ? $request->email_id : '';

            // Item Details
            $item = Items::where('id',$item_id)->first();
            $cat_id = (isset($item['category_id'])) ? $item['category_id'] : '';
            $shop_id = (isset($item['shop_id'])) ? $item['shop_id'] : '';
            $user_ip = $request->ip();

            if($item->id)
            {
                $item_review = new ItemReview();
                $item_review->shop_id = $shop_id;
                $item_review->category_id = $cat_id;
                $item_review->item_id = $item_id;
                $item_review->rating = $rating;
                $item_review->rating = $rating;
                $item_review->ip_address = $user_ip;
                $item_review->comment = $comment;
                $item_review->email = $email;
                $item_review->save();

                return response()->json([
                    'success' => 1,
                    'message' => 'Your Review has been Submitted SuccessFully...',
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'Internal Server Error!',
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


    // Function for Save CustomerDetails
    public function saveCustomerDetails(Request $request)
    {
        $shop_id = $request->shop_id;
        $request->validate([
            'user_name' => 'required',
            'mobile_no' => 'required|digits:10',
        ]);

        try
        {
            $user_data = [
                'user_name' => $request->user_name,
                'mobile_no' => $request->mobile_no
            ];

            $customer_visit = new CustomerVisit;
            $customer_visit->shop_id = $shop_id;
            $customer_visit->name = $request->user_name;
            $customer_visit->mobile_no = $request->mobile_no;
            $customer_visit->save();

            session()->put('cust_details',$user_data);
            session()->save();

            return response()->json([
                'success' => 1,
                'message' => "Welcome $request->user_name",
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
