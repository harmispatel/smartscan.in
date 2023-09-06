@php
    // Shop Settings
    $shop_settings = getClientSettings($shop_details['id']);
    $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

    $shop_slug = isset($shop_details['shop_slug']) ? $shop_details['shop_slug'] : '';

    // Intro Second
    $intro_second = (isset($shop_settings['intro_icon_duration']) && !empty($shop_settings['intro_icon_duration'])) ? $shop_settings['intro_icon_duration'] : '';

    // Shop Name
    $shop_name = (isset($shop_details['name']) && !empty($shop_details['name'])) ? $shop_details['name'] : "";

    $shop_desc = (isset($shop_details['description']) && !empty($shop_details['description'])) ? strip_tags($shop_details['description']) : "";

    // Default Logo
    $default_logo = asset('public/client_images/not-found/your_logo_1.png');

    // Shop Logo
    $shop_logo = (isset($shop_settings['shop_view_header_logo']) && !empty($shop_settings['shop_view_header_logo'])) ? $shop_settings['shop_view_header_logo'] : "";

    // Language Details
    $language_details = getLangDetailsbyCode($current_lang_code);

    // Get Banner Settings
    $shop_banners = getBanners($shop_details['id']);
    $banner_key = $language_details['code']."_image";
    $banner_text_key = $language_details['code']."_description";

    // Theme Settings
    $theme_settings = themeSettings($shop_theme_id);
    $slider_buttons = (isset($theme_settings['banner_slide_button']) && !empty($theme_settings['banner_slide_button'])) ? $theme_settings['banner_slide_button'] : 0;
    $slider_delay_time = (isset($theme_settings['banner_delay_time']) && !empty($theme_settings['banner_delay_time'])) ? $theme_settings['banner_delay_time'] : 3000;
    $banner_height = (isset($theme_settings['banner_height']) && !empty($theme_settings['banner_height'])) ? $theme_settings['banner_height'] : 0;

    // Get Subscription ID
    $subscription_id = getClientSubscriptionID($shop_details['id']);

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);
    $shop_desc= html_entity_decode($shop_desc);
    $shop_title = "$shop_name | $shop_desc";

@endphp

@extends('shop.shop-layout')

@section('title', $shop_title)

@section('content')

    <input type="hidden" name="shop_id" id="shop_id" value="{{ encrypt($shop_details['id']); }}">
    <input type="hidden" name="current_cat_id" id="current_cat_id" value="{{ $current_cat_id }}">

    @if(isset($package_permissions['banner']) && !empty($package_permissions['banner']) && $package_permissions['banner'] == 1)
        @if(isset($theme_settings['banner_position']) && !empty($theme_settings['banner_position']) && $theme_settings['banner_position'] == 'top')
            @if(count($shop_banners) > 0)
                <section class="home_main_slider" style="height: {{ $banner_height }}px;">
                    <div class="container h-100">
                        <div class="swiper-container h-100 position-relative">
                            <div class="swiper-wrapper">
                                @foreach ($shop_banners as $key => $banner)
                                    @if(($banner->display == 'both' || $banner->display == 'image') && (isset($banner[$banner_key]) && !empty($banner[$banner_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner[$banner_key])))
                                        <div class="swiper-slide" style="background-image: url('{{ asset('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner[$banner_key]) }}')">
                                    @else
                                        <div class="swiper-slide" style="background-color: {{ $banner->background_color }};">
                                    @endif
                                        @if($banner->display == 'both' || $banner->display == 'description')
                                            @if(isset($banner[$banner_text_key]) && !empty($banner[$banner_text_key]))
                                                <div class="swiper-text">
                                                    {!! $banner[$banner_text_key] !!}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($slider_buttons == 1)
                                <div class="swiper-slider-button-prev swiper-btn"><i class="fa-sharp fa-solid fa-arrow-right"></i></div>
                                <div class="swiper-slider-button-next swiper-btn"><i class="fa-sharp fa-solid fa-arrow-left"></i></div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif
        @endif
    @endif

    <section class="sec_main">
        <div class="container" id="CategorySection">

            @if(count($categories) > 0)
                <div class="menu_list">
                    @foreach ($categories as $category)

                        @php
                            $default_cat_img = asset('public/client_images/not-found/no_image_1.jpg');
                            $name_code = $current_lang_code."_name";
                            $cat_image = isset($category->categoryImages[0]['image']) ? $category->categoryImages[0]['image'] : '';
                            $thumb_image = isset($category->cover) ? $category->cover : '';

                            $active_cat = checkCategorySchedule($category->id,$category->shop_id);

                            $check_cat_type_permission = checkCatTypePermission($category->category_type,$shop_details['id']);
                        @endphp
                            @if($active_cat == 1)
                                @if($check_cat_type_permission == 1)
                                    <div class="menu_list_item">
                                        @if($category->category_type == 'link')
                                            <a href="{{ (isset($category->link_url) && !empty($category->link_url)) ? $category->link_url : '#' }}" target="_blank">
                                        @elseif($category->category_type == 'parent_category')
                                            <a href="{{ route('restaurant',[$shop_slug,$category->id]) }}">
                                        @else
                                            <a href="{{ route('items.preview',[$shop_details['shop_slug'],$category->id]) }}">
                                        @endif

                                            {{-- Image Section --}}
                                            @if($category->category_type == 'product_category')
                                                @if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image) }}" class="w-100">
                                                @else
                                                    <img src="{{ $default_cat_img }}" class="w-100">
                                                @endif
                                            @else
                                                @if(!empty($thumb_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$thumb_image))
                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$thumb_image) }}" class="w-100">
                                                @else
                                                    <img src="{{ $default_cat_img }}" class="w-100">
                                                @endif
                                            @endif

                                            {{-- Name Section --}}
                                            <h3 class="item_name">{{ isset($category->$name_code) ? $category->$name_code : '' }}</h3>
                                        </a>
                                    </div>
                                @endif
                            @endif
                    @endforeach
                </div>
            @else
                <h3 class="text-center empty-category">Categories not Found.</h3>
            @endif
        </div>
    </section>

    @if(isset($package_permissions['banner']) && !empty($package_permissions['banner']) && $package_permissions['banner'] == 1)
        @if(isset($theme_settings['banner_position']) && !empty($theme_settings['banner_position']) && $theme_settings['banner_position'] == 'bottom')
            @if(count($shop_banners) > 0)
                <section class="home_main_slider" style="height: {{ $banner_height }}px;">
                    <div class="container h-100">
                        <div class="swiper-container h-100 position-relative">
                            <div class="swiper-wrapper">
                                @foreach ($shop_banners as $key => $banner)
                                    @if(($banner->display == 'both' || $banner->display == 'image') && (isset($banner[$banner_key]) && !empty($banner[$banner_key]) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner[$banner_key])))
                                        <div class="swiper-slide" style="background-image: url('{{ asset('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner[$banner_key]) }}')">
                                    @else
                                        <div class="swiper-slide" style="background-color: {{ $banner->background_color }};">
                                    @endif
                                        @if($banner->display == 'both' || $banner->display == 'description')
                                            @if(isset($banner[$banner_text_key]) && !empty($banner[$banner_text_key]))
                                                <div class="swiper-text">
                                                    {!! $banner[$banner_text_key] !!}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($slider_buttons == 1)
                                <div class="swiper-slider-button-prev swiper-btn"><i class="fa-sharp fa-solid fa-arrow-right"></i></div>
                                <div class="swiper-slider-button-next swiper-btn"><i class="fa-sharp fa-solid fa-arrow-left"></i></div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif
        @endif
    @endif

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
                                <a target="_blank" href="{{ $shop_settings['tripadvisor_link'] }}"><i class="fa-solid fa-mask"></i></a>
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

    @if(!isset($shop_settings['intro_icon_status']) || empty($shop_settings['intro_icon_status']) && $shop_settings['intro_icon_status'] == 0)
        <div class="loader">
            {{-- <img src="{{ asset('public/admin_images/logos/qr_gif.gif') }}" width="200px"> --}}
            <div class="restaurant-loader">
                <div class="restaurant-loader-inner"></div>
            </div>
        </div>
    @endif

    <input type="hidden" name="intro_second" id="intro_second" value="{{ $intro_second }}">

    @if(isset($shop_settings['intro_icon_status']) && !empty($shop_settings['intro_icon_status']) && $shop_settings['intro_icon_status'] == 1)
        @if(isset($shop_settings['shop_intro_icon']) && !empty($shop_settings['shop_intro_icon']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_settings['shop_intro_icon']))
            @php
                $intro_file_ext = pathinfo($shop_settings['shop_intro_icon'], PATHINFO_EXTENSION);
            @endphp

            <div class="cover-img">
                @if($intro_file_ext == 'mp4' || $intro_file_ext == 'mov')
                    <video src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_settings['shop_intro_icon']) }}" width="100%" autoplay muted loop>
                </video>
                @else
                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_settings['shop_intro_icon']) }}" width="100%">
                @endif
            </div>
        @endif
    @endif

    @if(!empty($current_cat_id))
        <a class="back_bt" href="{{ route('restaurant',$shop_slug) }}"><i class="fa-solid fa-chevron-left"></i></a>
    @endif

@endsection

{{-- Page JS Function --}}
@section('page-js')

    <script type="text/javascript">

        var BannerSpeed = {{ $slider_delay_time }};

        // Document Ready Function
        $(document).ready(function()
        {
            // TimeOut for Intro
            setTimeout(() => {
                $('.loader').hide();
            }, 1500);

            // Timeout for Cover
            var introSec = $('#intro_second').val() * 1000;
            setTimeout(() => {
                $('.cover-img').hide();
            }, introSec);

            new Swiper('.home_main_slider .swiper-container', {
                // loop: true,
                speed:1000,
                effect: 'fade',
                slidesPerView: 1,
                autoplay: {
                    delay: BannerSpeed,
                    disableOnInteraction: false
                },
                paginationClickable: true,
                navigation: {
                    nextEl: ".home_main_slider .swiper-slider-button-next",
                    prevEl: ".home_main_slider .swiper-slider-button-prev"
                },
            });
        });


        // Function for Get Filterd Categories
        $('#search').on('keyup',function()
        {
            var keywords = $(this).val();
            var shopID = $('#shop_id').val();
            var currCatID = $('#current_cat_id').val();

            $.ajax({
                type: "POST",
                url: '{{ route("shop.categories.search") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'keywords':keywords,
                    'shopID':shopID,
                    'current_cat_id':currCatID,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        $('#CategorySection').html('');
                        $('#CategorySection').append(response.data);
                    }
                    else
                    {
                        console.log(response.message);
                    }
                }
            });

        });


        // Error Messages
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

    </script>

@endsection
