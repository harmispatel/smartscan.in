@php
    // Shop Settings
    $shop_settings = getClientSettings($shop_details['id']);
    $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

    $shop_slug = isset($shop_details['shop_slug']) ? $shop_details['shop_slug'] : '';

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

    // Shop Name
    $shop_name = (isset($shop_details['name']) && !empty($shop_details['name'])) ? $shop_details['name'] : "";
    $shop_desc = (isset($shop_details['description']) && !empty($shop_details['description'])) ? strip_tags($shop_details['description']) : "";

    // Default Logo
    $default_logo = asset('public/client_images/not-found/your_logo_1.png');

    // Default Image
    $default_image = asset('public/client_images/not-found/no_image_1.jpg');

    // Shop Logo
    $shop_logo = (isset($shop_settings['shop_view_header_logo']) && !empty($shop_settings['shop_view_header_logo'])) ? $shop_settings['shop_view_header_logo'] : "";

    // Language Details
    $language_details = getLangDetailsbyCode($current_lang_code);

    // Name Key
    $name_key = $current_lang_code."_name";

    // Description Key
    $description_key = $current_lang_code."_description";

    // Price Key
    $price_label_key = $current_lang_code."_label";

    // Current Category
    $current_cat_id = isset($cat_details['id']) ? $cat_details['id'] : '';

    // Theme Settings
    $theme_settings = themeSettings($shop_theme_id);

    // Read More Label
    $read_more_label = (isset($theme_settings['read_more_link_label']) && !empty($theme_settings['read_more_link_label'])) ? $theme_settings['read_more_link_label'] : 'Read More';

    // Item Devider
    $item_devider = (isset($theme_settings['item_divider']) && !empty($theme_settings['item_divider'])) ? $theme_settings['item_divider'] : 0;

    // Today Special Icon
    $today_special_icon = isset($theme_settings['today_special_icon']) ? $theme_settings['today_special_icon'] : '';

    // Admin Settings
    $admin_settings = getAdminSettings();
    $default_special_image = (isset($admin_settings['default_special_item_image'])) ? $admin_settings['default_special_item_image'] : '';
    $shop_desc= html_entity_decode($shop_desc);

    $cat_name = isset($cat_details[$name_key]) ? $cat_details[$name_key] : '';
    $shop_title = "$shop_name | $cat_name";

    // Get Subscription ID
    $subscription_id = getClientSubscriptionID($shop_details['id']);

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);

@endphp


@extends('shop.shop-layout')

@section('title', $shop_title)

@section('content')

    <input type="hidden" name="current_category_id" value="{{ $current_cat_id }}" id="current_category_id">
    <input type="hidden" name="current_tab_id" id="current_tab_id" value="no_tab">
    <input type="hidden" name="is_cat_tab" id="is_cat_tab" value="{{ count($cat_tags) }}">
    <input type="hidden" name="shop_id" id="shop_id" value="{{ $shop_details['id'] }}">
    <input type="hidden" name="tag_id" id="tag_id" value="">
    <input type="hidden" name="parent_id" id="parent_id" value="{{ $parent_id }}">

    <section class="item_sec_main">
        <div class="container">
            <div class="item_box_main">

                {{-- Categories Tabs --}}
                <ul class="nav nav-tabs" id="myTab" role="tablist">

                    @if(count($categories) > 0)
                        @foreach ($categories as $cat)
                            @php
                                $active_cat = checkCategorySchedule($cat->id,$cat->shop_id);
                                $check_cat_type_permission = checkCatTypePermission($cat->category_type,$shop_details['id']);
                            @endphp

                            @if($active_cat == 1)
                                @if($check_cat_type_permission == 1)
                                    <li class="nav-item" role="presentation">
                                        @if($cat->category_type == 'link')
                                            <a href="{{ $cat->link_url }}" target="_blank" class="nav-link cat-btn">
                                        @else
                                            <a href="{{ route('items.preview',[$shop_details['shop_slug'],$cat->id]) }}" class="nav-link cat-btn {{ ($cat->id == $current_cat_id) ? 'active' : '' }}">
                                        @endif
                                            <div class="img_box">
                                                {{-- Img Section --}}
                                                @if($cat->category_type == 'page' || $cat->category_type == 'gallery' || $cat->category_type == 'link' || $cat->category_type == 'check_in' || $cat->category_type == 'parent_category' || $cat->category_type == 'pdf_page')
                                                    @if(!empty($cat->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat->cover))
                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat->cover) }}" class="w-100">
                                                    @else
                                                        <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" class="w-100">
                                                    @endif
                                                @else
                                                    @php
                                                        $cat_image = isset($cat->categoryImages[0]['image']) ? $cat->categoryImages[0]['image'] : '';
                                                    @endphp

                                                    @if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image) }}" class="w-100">
                                                    @else
                                                        <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" class="w-100">
                                                    @endif
                                                @endif
                                                <span>{{ isset($cat->$name_key) ? $cat->$name_key : "" }}</span>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </ul>

                    <div class="item_list_div">
                        <h3 class="mb-3 cat_name text-center">{{ isset($cat_details[$name_key]) ? $cat_details[$name_key] : "" }}</h3>
                        <div class="mb-3">{!! isset($cat_details[$description_key]) ? $cat_details[$description_key] : "" !!}</div>

                        <div class="item_inr_info">

                            @if(count($cat_tags) > 0)
                                {{-- Tags Section --}}
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button onclick="setTabKey('all','')" class="nav-link active tags-btn" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
                                    </li>

                                    @foreach ($cat_tags as $tag)
                                        <li class="nav-item" role="presentation">
                                            <button onclick="setTabKey('{{ $tag['id'] }}','{{ $tag['tag_id'] }}')" class="nav-link tags-btn" id="{{ $tag['id'] }}-tab" data-bs-toggle="tab" data-bs-target="#tag{{ $tag['id'] }}" type="button" role="tab" aria-controls="tag{{ $tag['id'] }}" aria-selected="false">{{ (isset($tag[$name_key])) ? $tag[$name_key] : "" }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                                        <div class="item_inr_info_sec">
                                            <div class="row">
                                                @if(count($all_items) > 0)
                                                    @foreach ($all_items as $item)
                                                        @php
                                                            $item_discount = (isset($item['discount'])) ? $item['discount'] : 0;
                                                            $item_discount_type = (isset($item['discount_type'])) ? $item['discount_type'] : 'percentage';
                                                        @endphp
                                                        @if($item['type'] == 1)
                                                            <div class="col-md-6 col-lg-6 col-xl-3 mb-3">
                                                                <div class="single_item_inr devider-border" onclick="getItemDetails({{ $item->id }},{{ $shop_details['id'] }})" style="cursor: pointer">

                                                                    {{-- Image Section --}}
                                                                    @if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                                        <div class="item_image">
                                                                            <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) }}">
                                                                        </div>
                                                                    @endif

                                                                    {{-- Name Section --}}
                                                                    <h3>{{ (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "" }}</h3>

                                                                    {{-- New Product Image --}}
                                                                    @if($item['is_new'] == 1)
                                                                        <img class="is_new tag-img" src="{{ asset('public/client_images/bs-icon/new.png') }}">
                                                                    @endif

                                                                    {{-- Signature Image --}}
                                                                    @if($item['as_sign'] == 1)
                                                                        <img class="is_sign tag-img" src="{{ asset('public/client_images/bs-icon/signature.png') }}">
                                                                    @endif

                                                                    {{-- Day Special Image --}}
                                                                    {{-- @if($item['day_special'] == 1)
                                                                        <img class="is_special tag-img" src="{{ asset('public/client_images/bs-icon/today_special.gif') }}">
                                                                    @endif --}}

                                                                    {{-- Ingredient Section --}}
                                                                    @php
                                                                        $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                                                    @endphp

                                                                    @if(count($ingrediet_arr) > 0)
                                                                        <div>
                                                                            @foreach ($ingrediet_arr as $val)
                                                                                @php
                                                                                    $ingredient = getIngredientDetail($val);
                                                                                    $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                                    $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                                @endphp

                                                                                @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                                    @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                                    @endif
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    @endif

                                                                    {{-- Description Section --}}
                                                                    @php
                                                                        $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "";
                                                                    @endphp
                                                                    @if(strlen(strip_tags($desc)) > 180)
                                                                        <div class="item-desc">
                                                                            <p>
                                                                                {!! substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n")) !!}... <br>
                                                                                <a class="read-more-desc">{{ $read_more_label }}</a>
                                                                            </p>
                                                                        </div>
                                                                    @else
                                                                        <div class="item-desc"><p>{!! $desc !!}</p></div>
                                                                    @endif

                                                                    {{-- Price Section --}}
                                                                    <ul class="price_ul">
                                                                        @php
                                                                            $price_arr = getItemPrice($item['id']);
                                                                        @endphp

                                                                        @if(count($price_arr) > 0)
                                                                            @foreach ($price_arr as $key => $value)
                                                                                @php
                                                                                    $price = Currency::currency($currency)->format($value['price']);
                                                                                    $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";
                                                                                @endphp
                                                                                <li>
                                                                                    @if($item_discount > 0)
                                                                                        @php
                                                                                            if($item_discount_type == 'fixed')
                                                                                            {
                                                                                                $new_amount = $value['price'] - $item_discount;
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                $per_value = $value['price'] * $item_discount / 100;
                                                                                                $new_amount = $value['price'] - $per_value;
                                                                                            }
                                                                                        @endphp
                                                                                        <p>
                                                                                            {{ $price_label }} <span class="text-decoration-line-through">{{ $price }}</span> <span>{{ Currency::currency($currency)->format($new_amount) }}</span>
                                                                                        </p>
                                                                                    @else
                                                                                        <p>
                                                                                            {{ $price_label }} <span>{{ $price }}</span>
                                                                                        </p>
                                                                                    @endif
                                                                                </li>
                                                                            @endforeach
                                                                        @endif
                                                                    </ul>

                                                                    @if($item['day_special'] == 1)
                                                                        @if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                                                            <img width="170" class="mt-4" src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon) }}">
                                                                        @else
                                                                            @if(!empty($default_special_image))
                                                                                <img width="170" class="mt-4" src="{{ $default_special_image }}">
                                                                            @else
                                                                                <img width="170" class="mt-4" src="{{ asset('public/client_images/bs-icon/today_special.gif') }}">
                                                                            @endif
                                                                        @endif
                                                                    @endif

                                                                    @if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                                                                        <div class="cart-symbol"><i class="bi bi-cart4"></i></div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @else
                                                            @if($item_devider == 1)
                                                                <div class="col-md-12 mb-3">
                                                                    <div class="single_item_inr devider">

                                                                        {{-- Image Section --}}
                                                                        @if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                                            <div class="item_image">
                                                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) }}">
                                                                            </div>
                                                                        @endif

                                                                        {{-- Name Section --}}
                                                                        <h3>{{ (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "" }}</h3>


                                                                        {{-- Ingredient Section --}}
                                                                        @php
                                                                            $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                                                        @endphp

                                                                        @if(count($ingrediet_arr) > 0)
                                                                            <div>
                                                                                @foreach ($ingrediet_arr as $val)
                                                                                    @php
                                                                                        $ingredient = getIngredientDetail($val);
                                                                                        $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                                        $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                                    @endphp

                                                                                    @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                                        @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                            <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                                        @endif
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Description Section --}}
                                                                        <div>{!! (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "" !!}</div>

                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @foreach ($cat_tags as $tag)

                                        @php
                                            $tag_items = getTagsProducts($tag['tag_id'],$cat_details['id']);
                                        @endphp

                                        <div class="tab-pane fade show" id="tag{{ $tag['id'] }}" role="tabpanel" aria-labelledby="{{ $tag['id'] }}-tab">
                                            <div class="item_inr_info_sec">
                                                <div class="row">
                                                    @if(count($tag_items) > 0)
                                                        @foreach ($tag_items as $item)
                                                            @php
                                                                $item_discount = (isset($item['discount'])) ? $item['discount'] : 0;
                                                                $item_discount_type = (isset($item['discount_type'])) ? $item['discount_type'] : 'percentage';
                                                            @endphp
                                                            @if($item['type'] == 1)
                                                                <div class="col-md-6 col-lg-6 col-xl-3 mb-3">
                                                                    <div class="single_item_inr devider-border" onclick="getItemDetails({{ $item->id }},{{ $shop_details['id'] }})" style="cursor: pointer">

                                                                        {{-- Image Section --}}
                                                                        @if(!empty($item->product['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->product['image']))
                                                                            <div class="item_image">
                                                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->product['image']) }}">
                                                                            </div>
                                                                        @endif

                                                                        {{-- Name Section --}}
                                                                        <h3>{{ (isset($item->product[$name_key]) && !empty($item->product[$name_key])) ? $item->product[$name_key] : "" }}</h3>

                                                                        {{-- New Product Image --}}
                                                                        @if($item->product['is_new'] == 1)
                                                                            <img class="is_new tag-img" src="{{ asset('public/client_images/bs-icon/new.png') }}">
                                                                        @endif

                                                                        {{-- Signature Image --}}
                                                                        @if($item->product['as_sign'] == 1)
                                                                            <img class="is_sign tag-img" src="{{ asset('public/client_images/bs-icon/signature.png') }}">
                                                                        @endif

                                                                        {{-- Day Special Image --}}
                                                                        {{-- @if($item->product['day_special'] == 1)
                                                                            <img class="is_special tag-img" src="{{ asset('public/client_images/bs-icon/special.png') }}">
                                                                        @endif --}}

                                                                        {{-- Ingredient Section --}}
                                                                        @php
                                                                            $ingrediet_arr = (isset($item->product['ingredients']) && !empty($item->product['ingredients'])) ? unserialize($item->product['ingredients']) : [];
                                                                        @endphp

                                                                        @if(count($ingrediet_arr) > 0)
                                                                            <div>
                                                                                @foreach ($ingrediet_arr as $val)
                                                                                    @php
                                                                                        $ingredient = getIngredientDetail($val);
                                                                                        $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                                        $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                                    @endphp

                                                                                    @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                                        @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                            <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                                        @endif
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Description Section --}}
                                                                        @php
                                                                            $desc = (isset($item->product[$description_key]) && !empty($item->product[$description_key])) ? $item->product[$description_key] : "";
                                                                        @endphp
                                                                        @if(strlen(strip_tags($desc)) > 180)
                                                                            <div class="item-desc">
                                                                                <p>
                                                                                    {!! substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n")) !!}... <br>
                                                                                    <a class="read-more-desc">{{ $read_more_label }}</a>
                                                                                </p>
                                                                            </div>
                                                                        @else
                                                                            <div class="item-desc"><p>{!! $desc !!}</p></div>
                                                                        @endif

                                                                        {{-- Price Section --}}
                                                                        <ul class="price_ul">
                                                                            @php
                                                                                $price_arr = getItemPrice($item['id']);
                                                                            @endphp

                                                                            @if(count($price_arr) > 0)
                                                                                @foreach ($price_arr as $key => $value)
                                                                                    @php
                                                                                        $price = Currency::currency($currency)->format($value['price']);
                                                                                        $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";
                                                                                    @endphp
                                                                                    <li>
                                                                                        @if($item_discount > 0)
                                                                                            @php
                                                                                                if($item_discount_type == 'fixed')
                                                                                                {
                                                                                                    $new_amount = $value['price'] - $item_discount;
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    $per_value = $value['price'] * $item_discount / 100;
                                                                                                    $new_amount = $value['price'] - $per_value;
                                                                                                }
                                                                                            @endphp
                                                                                            <p>
                                                                                                {{ $price_label }} <span class="text-decoration-line-through">{{ $price }}</span> <span>{{ Currency::currency($currency)->format($new_amount) }}</span>
                                                                                            </p>
                                                                                        @else
                                                                                            <p>
                                                                                                {{ $price_label }} <span>{{ $price }}</span>
                                                                                            </p>
                                                                                        @endif
                                                                                    </li>
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>

                                                                        @if($item['day_special'] == 1)
                                                                            @if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                                                                <img width="170" class="mt-4" src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon) }}">
                                                                            @else
                                                                                @if(!empty($default_special_image))
                                                                                    <img width="170" class="mt-4" src="{{ $default_special_image }}">
                                                                                @else
                                                                                    <img width="170" class="mt-4" src="{{ asset('public/client_images/bs-icon/today_special.gif') }}">
                                                                                @endif
                                                                            @endif
                                                                        @endif

                                                                        @if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                                                                            <div class="cart-symbol"><i class="bi bi-cart4"></i></div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @else
                                                                @if($item_devider == 1)
                                                                    <div class="col-md-12 mb-3">
                                                                        <div class="single_item_inr devider">

                                                                            {{-- Image Section --}}
                                                                            @if(!empty($item->product['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->product['image']))
                                                                                <div class="item_image">
                                                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->product['image']) }}">
                                                                                </div>
                                                                            @endif

                                                                            {{-- Name Section --}}
                                                                            <h3>{{ (isset($item->product[$name_key]) && !empty($item->product[$name_key])) ? $item->product[$name_key] : "" }}</h3>

                                                                            {{-- Ingredient Section --}}
                                                                            @php
                                                                                $ingrediet_arr = (isset($item->product['ingredients']) && !empty($item->product['ingredients'])) ? unserialize($item->product['ingredients']) : [];
                                                                            @endphp

                                                                            @if(count($ingrediet_arr) > 0)
                                                                                <div>
                                                                                    @foreach ($ingrediet_arr as $val)
                                                                                        @php
                                                                                            $ingredient = getIngredientDetail($val);
                                                                                            $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                                            $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                                        @endphp

                                                                                        @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                                            @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                                            @endif
                                                                                        @endif
                                                                                    @endforeach
                                                                                </div>
                                                                            @endif

                                                                            {{-- Description Section --}}
                                                                            <div class="item-desc">{!! (isset($item->product[$description_key]) && !empty($item->product[$description_key])) ? $item->product[$description_key] : "" !!}</div>

                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="item_inr_info_sec">
                                    <div class="row">
                                        @if(count($all_items) > 0)
                                            @foreach ($all_items as $item)
                                                @php
                                                    $item_discount = (isset($item['discount'])) ? $item['discount'] : 0;
                                                    $item_discount_type = (isset($item['discount_type'])) ? $item['discount_type'] : 'percentage';
                                                @endphp
                                                @if($item['type'] == 1)
                                                    <div class="col-md-6 col-lg-6 col-xl-3 mb-3">
                                                        <div class="single_item_inr devider-border" onclick="getItemDetails({{ $item->id }},{{ $shop_details['id'] }})" style="cursor: pointer">

                                                            {{-- Image Section --}}
                                                            @if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                                <div class="item_image">
                                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) }}">
                                                                </div>
                                                            @endif

                                                            {{-- Name Section --}}
                                                            <h3>{{  (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "" }}</h3>

                                                            {{-- New Product Image --}}
                                                            @if($item['is_new'] == 1)
                                                                <img class="is_new tag-img" src="{{ asset('public/client_images/bs-icon/new.png') }}">
                                                            @endif

                                                            {{-- Signature Image --}}
                                                            @if($item['as_sign'] == 1)
                                                                <img class="is_sign tag-img" src="{{ asset('public/client_images/bs-icon/signature.png') }}">
                                                            @endif

                                                            {{-- Day Special Image --}}
                                                            {{-- @if($item['day_special'] == 1)
                                                                <img class="is_special tag-img" src="{{ asset('public/client_images/bs-icon/special.png') }}">
                                                            @endif --}}

                                                            {{-- Ingredient Section --}}
                                                            @php
                                                                $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                                            @endphp

                                                            @if(count($ingrediet_arr) > 0)
                                                                <div>
                                                                    @foreach ($ingrediet_arr as $val)
                                                                        @php
                                                                            $ingredient = getIngredientDetail($val);
                                                                            $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                            $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                        @endphp

                                                                        @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                            @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                            @endif
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            {{-- Description Section --}}
                                                            @php
                                                                $desc = (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "";
                                                            @endphp
                                                            @if(strlen(strip_tags($desc)) > 180)
                                                                <div class="item-desc">
                                                                    <p>
                                                                        {!! substr(strip_tags($desc), 0, strpos(wordwrap(strip_tags($desc),150), "\n")) !!}... <br>
                                                                        <a class="read-more-desc">{{ $read_more_label }}</a>
                                                                    </p>
                                                                </div>
                                                            @else
                                                                <div class="item-desc"><p>{!! $desc !!}</p></div>
                                                            @endif

                                                            {{-- Price Section --}}
                                                            <ul class="price_ul">
                                                                @php
                                                                    $price_arr = getItemPrice($item['id']);
                                                                @endphp

                                                                @if(count($price_arr) > 0)
                                                                    @foreach ($price_arr as $key => $value)
                                                                        @php
                                                                            $price = Currency::currency($currency)->format($value['price']);
                                                                            $price_label = (isset($value[$price_label_key])) ? $value[$price_label_key] : "";
                                                                        @endphp
                                                                        <li>
                                                                            @if($item_discount > 0)
                                                                                @php
                                                                                    if($item_discount_type == 'fixed')
                                                                                    {
                                                                                        // $new_amount = number_format($value['price'] - $item_discount,2);
                                                                                        $new_amount = $value['price'] - $item_discount;
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                        $per_value = $value['price'] * $item_discount / 100;
                                                                                        // $new_amount = number_format($value['price'] - $per_value,2);
                                                                                        $new_amount = $value['price'] - $per_value;
                                                                                    }
                                                                                @endphp
                                                                                <p>
                                                                                    {{ $price_label }} <span class="text-decoration-line-through">{{ $price }}</span> <span>{{ Currency::currency($currency)->format($new_amount) }}</span>
                                                                                </p>
                                                                            @else
                                                                                <p>
                                                                                    {{ $price_label }} <span>{{ $price }}</span>
                                                                                </p>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>

                                                            @if($item['day_special'] == 1)
                                                                @if(!empty($today_special_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon))
                                                                    <img width="170" class="mt-4" src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/today_special_icon/'.$today_special_icon) }}">
                                                                @else
                                                                    @if(!empty($default_special_image))
                                                                        <img width="170" class="mt-4" src="{{ $default_special_image }}">
                                                                    @else
                                                                        <img width="170" class="mt-4" src="{{ asset('public/client_images/bs-icon/today_special.gif') }}">
                                                                    @endif
                                                                @endif
                                                            @endif

                                                            @if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                                                                <div class="cart-symbol"><i class="bi bi-cart4"></i></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    @if($item_devider == 1)
                                                        <div class="col-md-12 mb-3">
                                                            <div class="single_item_inr devider">

                                                                {{-- Image Section --}}
                                                                @if(!empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']))
                                                                    <div class="item_image">
                                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) }}">
                                                                    </div>
                                                                @endif

                                                                {{-- Name Section --}}
                                                                <h3>{{  (isset($item[$name_key]) && !empty($item[$name_key])) ? $item[$name_key] : "" }}</h3>

                                                                {{-- Ingredient Section --}}
                                                                @php
                                                                    $ingrediet_arr = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
                                                                @endphp

                                                                @if(count($ingrediet_arr) > 0)
                                                                    <div>
                                                                        @foreach ($ingrediet_arr as $val)
                                                                            @php
                                                                                $ingredient = getIngredientDetail($val);
                                                                                $ing_icon = isset($ingredient['icon']) ? $ingredient['icon'] : '';
                                                                                $parent_ing_id = (isset($ingredient['parent_id'])) ? $ingredient['parent_id'] : NULL;
                                                                            @endphp

                                                                            @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_ing_id != NULL)
                                                                                @if(!empty($ing_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                                                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon) }}" width="60px" height="60px">
                                                                                @endif
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif

                                                                {{-- Description Section --}}
                                                                <div class="item-desc">{!! (isset($item[$description_key]) && !empty($item[$description_key])) ? $item[$description_key] : "" !!}</div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @else
                                            <h3>Items Not Found !</h3>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
            </div>
        </div>
    </section>

    <footer class="footer text-center">
        <div class="container">
            <div class="footer-inr">
                <div class="footer_media">
                    <h3>Find Us</h3>
                    <ul>
                        {{-- Phone Link --}}
                        @if(isset($shop_settings['business_telephone']) && !empty($shop_settings['business_telephone']))
                            <li>
                                <a href="tel:{{ $shop_settings['business_telephone'] }}"><i class="fa-solid fa-phone"></i></a>
                            </li>
                        @endif

                        {{-- Instagram Link --}}
                        @if(isset($shop_settings['instagram_link']) && !empty($shop_settings['instagram_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['instagram_link'] }}"><i class="fa-brands fa-square-instagram"></i></a>
                            </li>
                        @endif

                        {{-- Twitter Link --}}
                        @if(isset($shop_settings['twitter_link']) && !empty($shop_settings['twitter_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['twitter_link'] }}"><i class="fa-brands fa-square-twitter"></i></a>
                            </li>
                        @endif

                        {{-- Facebook Link --}}
                        @if(isset($shop_settings['facebook_link']) && !empty($shop_settings['facebook_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['facebook_link'] }}"><i class="fa-brands fa-square-facebook"></i></a>
                            </li>
                        @endif

                        {{-- Pinterest Link --}}
                        @if(isset($shop_settings['pinterest_link']) && !empty($shop_settings['pinterest_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['pinterest_link'] }}"><i class="fa-brands fa-pinterest"></i></a>
                            </li>
                        @endif

                        {{-- FourSquare Link --}}
                        @if(isset($shop_settings['foursquare_link']) && !empty($shop_settings['foursquare_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['foursquare_link'] }}"><i class="fa-brands fa-foursquare"></i></a>
                            </li>
                        @endif

                        {{-- TripAdvisor Link --}}
                        @if(isset($shop_settings['tripadvisor_link']) && !empty($shop_settings['tripadvisor_link']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['tripadvisor_link'] }}"><a target="_blank" href="{{ $shop_settings['tripadvisor_link'] }}"><i class="fa-solid fa-mask"></i></a></a>
                            </li>
                        @endif

                        {{-- Website Link --}}
                        @if(isset($shop_settings['website_url']) && !empty($shop_settings['website_url']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['website_url'] }}"><i class="fa-solid fa-globe"></i></a>
                            </li>
                        @endif

                        {{-- Gmap Link --}}
                        @if(isset($shop_settings['map_url']) && !empty($shop_settings['map_url']))
                            <li>
                                <a target="_blank" href="{{ $shop_settings['map_url'] }}"><i class="fa-solid fa-location-dot"></i></a>
                            </li>
                        @endif

                    </ul>
                </div>

                @if(isset($shop_settings['homepage_intro']) && !empty($shop_settings['homepage_intro']))
                    <p>{!! $shop_settings['homepage_intro'] !!}</p>
                @else
                    @php
                        $current_year = \Carbon\Carbon::now()->format('Y');
                        $settings = getAdminSettings();
                        $copyright_text = (isset($settings['copyright_text']) && !empty($settings['copyright_text'])) ? $settings['copyright_text'] : '';
                        $copyright_text = str_replace('[year]',$current_year,$copyright_text);
                    @endphp
                    <p>{!! $copyright_text !!}</p>
                @endif

            </div>
        </div>
    </footer>

    <a class="back_bt" href="{{ route('restaurant',$shop_details['shop_slug']) }}"><i class="fa-solid fa-chevron-left"></i></a>

@endsection


{{-- Page JS Function --}}
@section('page-js')

    <script type="text/javascript">

        $('document').ready(function()
        {
            var totalTab = $('#is_cat_tab').val();
            if(totalTab > 0)
            {
                $('#current_tab_id').val('all');
            }
        });

        // Remove Item Details from Model
        $('#itemDetailsModal .btn-close').on('click',function(){
            $('#itemDetailsModal #item_dt_div').html('');
        });

        // Function for Search Items
        $('#search').on('keyup',function()
        {
            var catID = $('#current_category_id').val();
            var tabID = $('#current_tab_id').val();
            var tag_id = $('#tag_id').val();
            var parent_id = $('#parent_id').val();
            var shop_id = $('#shop_id').val();
            var keyword = $(this).val();

            $.ajax({
                type: "POST",
                url: "{{ route('shop.items.search') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    "category_id" : catID,
                    "tab_id" : tabID,
                    "keyword" : keyword,
                    "shop_id" : shop_id,
                    "tag_id" : tag_id,
                    "parent_id" : parent_id,
                },
                dataType: "JSON",
                success: function (response) {
                    if(response.success == 1)
                    {
                        if(tabID == 'no_tab')
                        {
                            $('.item_inr_info').html('');
                            $('.item_inr_info').append(response.data);
                        }
                        else
                        {
                            if(keyword == '')
                            {
                                $('.item_inr_info #myTab').show();
                                $('.cat_name').show();
                            }
                            else
                            {
                                $('.item_inr_info #myTab').hide();
                                $('.cat_name').hide();
                            }

                            if(tabID == 'all')
                            {
                                $('#'+tabID).html('');
                                $('#'+tabID).append(response.data);
                            }
                            else
                            {
                                // alert('#tag'+tabID);
                                $('#tag'+tabID).html('');
                                $('#tag'+tabID).append(response.data);
                            }
                        }
                    }
                    else
                    {
                        console.log(response.message);
                    }
                }
            });

        });

        function setTabKey(key,tagID)
        {
            $('#current_tab_id').val(key);
            $('#tag_id').val(tagID);
        }

    </script>

@endsection
