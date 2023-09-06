@php
    $admin_settings = getAdminSettings();
    $main_screen = isset($admin_settings['theme_main_screen_demo']) ? $admin_settings['theme_main_screen_demo'] : '';
    $category_screen = isset($admin_settings['theme_category_screen_demo']) ? $admin_settings['theme_category_screen_demo'] : '';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Clone Theme'))

@section('content')

<section class="theme_section">
    <div class="main_section_inr">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('design.theme') }}">{{ __('Themes')}}</a></li>
                <li class="breadcrumb-item active">{{ __('Clone Theme')}}</li>
            </ol>
        </nav>
        <div class="row">
            <form action="{{ route('design.theme-store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12">
                    <div class="theme_change_sec">
                        <div class="theme_name">
                            <h2 class="form-group">
                                <input type="text" name="theme_name" id="theme_name" class="form-control border-0 {{ ($errors->has('theme_name')) ? 'is-invalid' : '' }}" placeholder="Enter Theme Name" value="{{ isset($theme['name']) ? $theme['name'] : '' }}">
                                @if($errors->has('theme_name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('theme_name') }}
                                    </div>
                                @endif
                            </h2>
                        </div>
                        <div class="theme_change_sec_inr">
                            <div class="accordion" id="accordionExample">

                                {{-- Main Page Section --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">{{ __('Main Screen')}}</button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="main_theme_color">
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Header Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="header_color" name="header_color" class="form-control me-2 p-0" value="{{ isset($settings['header_color']) ? $settings['header_color'] : '' }}" onchange="changeVal('header_color','header_color_input')">
                                                                    <input type="text" id="header_color_input" class="form-control" onkeyup="changeColor('header_color_input','header_color')" value="{{ isset($settings['header_color']) ? $settings['header_color'] : '' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Sticky Header')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="sticky_header" id="sticky_header" value="1" {{ (isset($settings['sticky_header']) && $settings['sticky_header'] == 1) ? 'checked' : '' }}>
                                                                    <span class="slider round">
                                                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Language Box Position')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select name="language_bar_position" id="language_bar_position" class="form-select">
                                                                    <option value="right" {{ ($settings['language_bar_position'] == 'right') ? 'selected' : '' }}>Right</option>
                                                                    <option value="left" {{ ($settings['language_bar_position'] == 'left') ? 'selected' : '' }}>Left</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Logo Position')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select name="logo_position" id="logo_position" class="form-select">
                                                                    <option value="right" {{ ($settings['logo_position'] == 'right') ? 'selected' : '' }}>Right</option>
                                                                    <option value="left" {{ ($settings['logo_position'] == 'left') ? 'selected' : '' }}>Left</option>
                                                                    <option value="center" {{ ($settings['logo_position'] == 'center') ? 'selected' : '' }}>Center</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Search Box Position')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select name="search_box_position" id="search_box_position" class="form-select">
                                                                    <option value="right" {{ ($settings['search_box_position'] == 'right') ? 'selected' : '' }}>Right</option>
                                                                    <option value="left" {{ ($settings['search_box_position'] == 'left') ? 'selected' : '' }}>Left</option>
                                                                    <option value="center" {{ ($settings['search_box_position'] == 'center') ? 'selected' : '' }}>Center</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Search Box Icon Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="search_box_icon_color" name="search_box_icon_color" class="form-control me-2 p-0" value="{{ isset($settings['search_box_icon_color']) ? $settings['search_box_icon_color'] : '' }}" onchange="changeVal('search_box_icon_color','search_box_icon_color_input')">
                                                                    <input id="search_box_icon_color_input" type="text" class="form-control" value="{{ isset($settings['search_box_icon_color']) ? $settings['search_box_icon_color'] : '' }}" onkeyup="changeColor('search_box_icon_color_input','search_box_icon_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Banner Position')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select name="banner_position" id="banner_position" class="form-select">
                                                                    <option value="top" {{ ($settings['banner_position'] == 'top') ? 'selected' : '' }}>Top</option>
                                                                    <option value="bottom" {{ ($settings['banner_position'] == 'bottom') ? 'selected' : '' }}>Bottom</option>
                                                                    <option value="hide" {{ ($settings['banner_position'] == 'hide') ? 'selected' : '' }}>Hidden</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Banner Type')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select name="banner_type" id="banner_type" class="form-select">
                                                                    <option value="text" {{ ($settings['banner_type'] == 'text') ? 'selected' : '' }}>Text</option>
                                                                    <option value="image" {{ ($settings['banner_type'] == 'image') ? 'selected' : '' }}>Image</option>
                                                                </select>
                                                            </div>
                                                        </div> --}}
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Banner Slide Button')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="banner_slide_button" id="banner_slide_button" {{ (isset($settings['banner_slide_button']) && $settings['banner_slide_button'] == 1) ? 'checked' : '' }} value="1">
                                                                    <span class="slider round">
                                                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Banner Delay Time')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="form-group">
                                                                    <input type="number" name="banner_delay_time" id="banner_delay_time" class="form-control" value="{{ isset($settings['banner_delay_time']) ? $settings['banner_delay_time'] : '' }}">
                                                                    <code>Enter Time in Miliseconds</code>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Banner Height')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="form-group">
                                                                    <input type="number" name="banner_height" id="banner_height" class="form-control" value="{{ isset($settings['banner_height']) ? $settings['banner_height'] : '' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Background Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="background_color" name="background_color" class="form-control me-2 p-0" value="{{ isset($settings['background_color']) ? $settings['background_color'] : '' }}" onchange="changeVal('background_color','background_color_input')">
                                                                    <input id="background_color_input" type="text" class="form-control" value="{{ isset($settings['background_color']) ? $settings['background_color'] : '' }}" onkeyup="changeColor('background_color_input','background_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Font Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="font_color" name="font_color" class="form-control me-2 p-0" value="{{ isset($settings['font_color']) ? $settings['font_color'] : '' }}" onchange="changeVal('font_color','font_color_input')">
                                                                    <input id="font_color_input" type="text" class="form-control" value="{{ isset($settings['font_color']) ? $settings['font_color'] : '' }}" onkeyup="changeColor('font_color_input','font_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Label Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="label_color" name="label_color" class="form-control me-2 p-0" value="{{ isset($settings['label_color']) ? $settings['label_color'] : '' }}" onchange="changeVal('label_color','label_color_input')">
                                                                    <input id="label_color_input" type="text" class="form-control" value="{{ isset($settings['label_color']) ? $settings['label_color'] : '' }}" onkeyup="changeColor('label_color_input','label_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Label Color Transparency')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="range" id="label_color_transparency" min="0" max="1" step="0.1" value="{{ (isset($settings['label_color_transparency']) && !empty($settings['label_color_transparency'])) ? $settings['label_color_transparency'] : 1 }}" name="label_color_transparency">
                                                                    <output class="ms-1" for="label_color_transparency" id="slider-value">{{ (isset($settings['label_color_transparency']) && !empty($settings['label_color_transparency'])) ? $settings['label_color_transparency'] : 1 }}</output>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-7">
                                                                <span>{{ __('Social Media Icons Color')}}</span>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="social_media_icon_color" name="social_media_icon_color" class="form-control me-2 p-0" value="{{ isset($settings['social_media_icon_color']) ? $settings['social_media_icon_color'] : '' }}" onchange="changeVal('social_media_icon_color','social_media_icon_color_input')">
                                                                    <input id="social_media_icon_color_input" type="text" class="form-control" value="{{ isset($settings['social_media_icon_color']) ? $settings['social_media_icon_color'] : '' }}" onkeyup="changeColor('social_media_icon_color_input','social_media_icon_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="preview_img">
                                                        @if(!empty($main_screen))
                                                            <img src="{{ $main_screen }}" class="w-100">
                                                            {{-- <img src="{{ asset('public/client_images/not-found/theme_main_screen.png') }}" class="w-100"> --}}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Item Page Section --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            {{ __('Within Item Screen')}}
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="main_theme_color">
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Categories Bar Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="categories_bar_color" name="categories_bar_color" class="form-control me-2 p-0" value="{{ isset($settings['categories_bar_color']) ? $settings['categories_bar_color'] : '' }}" onchange="changeVal('categories_bar_color','categories_bar_color_input')">
                                                                    <input id="categories_bar_color_input" type="text" class="form-control" value="{{ isset($settings['categories_bar_color']) ? $settings['categories_bar_color'] : '' }}" onkeyup="changeColor('categories_bar_color_input','categories_bar_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Category Bar Type')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <select name="category_bar_type" id="category_bar_type" class="form-select">
                                                                    <option value="8px" {{ ($settings['category_bar_type'] == '8px') ? 'selected' : '' }}>Square</option>
                                                                    <option value="50%" {{ ($settings['category_bar_type'] == '50%') ? 'selected' : '' }}>Circle</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Category Bar Fonts Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="menu_bar_font_color" name="menu_bar_font_color" class="form-control me-2 p-0" value="{{ isset($settings['menu_bar_font_color']) ? $settings['menu_bar_font_color'] : '' }}" onchange="changeVal('menu_bar_font_color','menu_bar_font_color_input')">
                                                                    <input id="menu_bar_font_color_input" type="text" class="form-control" value="{{ isset($settings['menu_bar_font_color']) ? $settings['menu_bar_font_color'] : '' }}" onkeyup="changeColor('menu_bar_font_color_input','menu_bar_font_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Category Title & Description Fonts Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="category_title_and_description_color" name="category_title_and_description_color" class="form-control me-2 p-0" value="{{ isset($settings['category_title_and_description_color']) ? $settings['category_title_and_description_color'] : '' }}" onchange="changeVal('category_title_and_description_color','category_title_and_description_color_input')">
                                                                    <input id="category_title_and_description_color_input" type="text" class="form-control" value="{{ isset($settings['category_title_and_description_color']) ? $settings['category_title_and_description_color'] : '' }}" onkeyup="changeColor('category_title_and_description_color_input','category_title_and_description_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Price Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="price_color" name="price_color" class="form-control me-2 p-0" value="{{ isset($settings['price_color']) ? $settings['price_color'] : '' }}" onchange="changeVal('price_color','price_color_input')">
                                                                    <input id="price_color_input" type="text" class="form-control" value="{{ isset($settings['price_color']) ? $settings['price_color'] : '' }}" onkeyup="changeColor('price_color_input','price_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Box Background Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_box_background_color" name="item_box_background_color" class="form-control me-2 p-0" value="{{ isset($settings['item_box_background_color']) ? $settings['item_box_background_color'] : '' }}" onchange="changeVal('item_box_background_color','item_box_background_color_input')">
                                                                    <input id="item_box_background_color_input" type="text" class="form-control" value="{{ isset($settings['item_box_background_color']) ? $settings['item_box_background_color'] : '' }}" onkeyup="changeColor('item_box_background_color_input','item_box_background_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Title Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_title_color" name="item_title_color" class="form-control me-2 p-0" value="{{ isset($settings['item_title_color']) ? $settings['item_title_color'] : '' }}" onchange="changeVal('item_title_color','item_title_color_input')">
                                                                    <input id="item_title_color_input" type="text" class="form-control" value="{{ isset($settings['item_title_color']) ? $settings['item_title_color'] : '' }}" onkeyup="changeColor('item_title_color_input','item_title_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Description Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_description_color" name="item_description_color" class="form-control me-2 p-0" value="{{ isset($settings['item_description_color']) ? $settings['item_description_color'] : '' }}" onchange="changeVal('item_description_color','item_description_color_input')">
                                                                    <input id="item_description_color_input" type="text" class="form-control" value="{{ isset($settings['item_description_color']) ? $settings['item_description_color'] : '' }}" onkeyup="changeColor('item_description_color_input','item_description_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Box Shadow')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="item_box_shadow" id="item_box_shadow" {{ (isset($settings['item_box_shadow']) && $settings['item_box_shadow'] == 1) ? 'checked' : '' }} value="1">
                                                                    <span class="slider round">
                                                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Box Shadow Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_box_shadow_color" name="item_box_shadow_color" class="form-control me-2 p-0" value="{{ isset($settings['item_box_shadow_color']) ? $settings['item_box_shadow_color'] : '' }}" onchange="changeVal('item_box_shadow_color','item_box_shadow_color_input')">
                                                                    <input id="item_box_shadow_color_input" type="text" class="form-control" value="{{ isset($settings['item_box_shadow_color']) ? $settings['item_box_shadow_color'] : '' }}" onkeyup="changeColor('item_box_shadow_color_input','item_box_shadow_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Box Shadow Thickness')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <select name="item_box_shadow_thickness" id="item_box_shadow_thickness" class="form-select">
                                                                    <option value="1px" {{ ($settings['item_box_shadow_thickness'] == '1px') ? 'selected' : '' }}>Light</option>
                                                                    <option value="3px" {{ ($settings['item_box_shadow_thickness'] == '3px') ? 'selected' : '' }}>Medium</option>
                                                                    <option value="5px" {{ ($settings['item_box_shadow_thickness'] == '5px') ? 'selected' : '' }}>Bold</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="item_divider" id="item_divider" value="1" {{ (isset($settings['item_divider']) && $settings['item_divider'] == 1) ? 'checked' : '' }}>
                                                                    <span class="slider round">
                                                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_divider_color" name="item_divider_color" class="form-control me-2 p-0" value="{{ isset($settings['item_divider_color']) ? $settings['item_divider_color'] : '' }}" onchange="changeVal('item_divider_color','item_divider_color_input')">
                                                                    <input id="item_divider_color_input" type="text" class="form-control" value="{{ isset($settings['item_divider_color']) ? $settings['item_divider_color'] : '' }}" onkeyup="changeColor('item_divider_color_input','item_divider_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider Thickness')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="number" name="item_divider_thickness" id="item_divider_thickness" class="form-control" value="{{ isset($settings['item_divider_thickness']) ? $settings['item_divider_thickness'] : '' }}">
                                                                    <code>Enter Thickness in Pexels</code>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider Type')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <select name="item_divider_type" id="item_divider_type" class="form-select">
                                                                    <option value="solid" {{ ($settings['item_divider_type'] == 'solid') ? 'selected' : '' }}>Solid</option>
                                                                    <option value="dotted" {{ ($settings['item_divider_type'] == 'dotted') ? 'selected' : '' }}>Dotted</option>
                                                                    <option value="dashed" {{ ($settings['item_divider_type'] == 'dashed') ? 'selected' : '' }}>Dashed</option>
                                                                    <option value="double" {{ ($settings['item_divider_type'] == 'double') ? 'selected' : '' }}>Double</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider Position')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <select name="item_divider_position" id="item_divider_position" class="form-select">
                                                                    <option value="top" {{ ($settings['item_divider_position'] == 'top') ? 'selected' : '' }}>Top</option>
                                                                    <option value="bottom" {{ ($settings['item_divider_position'] == 'bottom') ? 'selected' : '' }}>Bottom</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Item Divider Font Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="item_divider_font_color" name="item_divider_font_color" class="form-control me-2 p-0" value="{{ isset($settings['item_divider_font_color']) ? $settings['item_divider_font_color'] : '' }}" onchange="changeVal('item_divider_font_color','item_divider_font_color_input')">
                                                                    <input id="item_divider_font_color_input" type="text" class="form-control" value="{{ isset($settings['item_divider_font_color']) ? $settings['item_divider_font_color'] : '' }}" onkeyup="changeColor('item_divider_font_color_input','item_divider_font_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Tag Font Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="tag_font_color" name="tag_font_color" class="form-control me-2 p-0" value="{{ isset($settings['tag_font_color']) ? $settings['tag_font_color'] : '' }}" onchange="changeVal('tag_font_color','tag_font_color_input')">
                                                                    <input id="tag_font_color_input" type="text" class="form-control" value="{{ isset($settings['tag_font_color']) ? $settings['tag_font_color'] : '' }}" onkeyup="changeColor('tag_font_color_input','tag_font_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Tag Label Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="tag_label_color" name="tag_label_color" class="form-control me-2 p-0" value="{{ isset($settings['tag_label_color']) ? $settings['tag_label_color'] : '' }}" onchange="changeVal('tag_label_color','tag_label_color_input')">
                                                                    <input id="tag_label_color_input" type="text" class="form-control" value="{{ isset($settings['tag_label_color']) ? $settings['tag_label_color'] : '' }}" onkeyup="changeColor('tag_label_color_input','tag_label_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Read More Link Color')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="from-group d-flex align-items-center">
                                                                    <input type="color" id="read_more_link_color" name="read_more_link_color" class="form-control me-2 p-0" value="{{ isset($settings['read_more_link_color']) ? $settings['read_more_link_color'] : '' }}" onchange="changeVal('read_more_link_color','read_more_link_color_input')">
                                                                    <input id="read_more_link_color_input" type="text" class="form-control" value="{{ isset($settings['read_more_link_color']) ? $settings['read_more_link_color'] : '' }}" onkeyup="changeColor('read_more_link_color_input','read_more_link_color')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-8">
                                                                <span>{{ __('Read More Link Label')}}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="text" name="read_more_link_label" id="read_more_link_label" class="form-control" value="{{ isset($settings['read_more_link_label']) ? $settings['read_more_link_label'] : '' }}" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-4">
                                                                <span>{{ __('Today Special Icon')}}</span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="form-group align-items-center">
                                                                    <input type="file" name="today_special_icon" id="today_special_icon" class="form-control {{ ($errors->has('today_special_icon')) ? 'is-invalid' : '' }}">
                                                                    @if($errors->has('today_special_icon'))
                                                                        <div class="invalid-feedback">
                                                                            {{ $errors->first('today_special_icon') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-4">
                                                            <div class="col-md-4">
                                                                <span>{{ __('Theme Prview Image')}}</span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="form-group align-items-center">
                                                                    <input type="file" name="theme_preview_image" id="theme_preview_image" class="form-control {{ ($errors->has('theme_preview_image')) ? 'is-invalid' : '' }}">
                                                                    @if($errors->has('theme_preview_image'))
                                                                        <div class="invalid-feedback">
                                                                            {{ $errors->first('theme_preview_image') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="preview_img">
                                                        @if(!empty($category_screen))
                                                            <img src="{{ $category_screen }}" class="w-100">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <button class="btn btn-success">{{ __('S A V E')}}</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        function changeVal(colorPickerID,textID)
        {
            var value = $('#'+colorPickerID).val();
            $('#'+textID).val(value);
        }

        function changeColor(textID,colorPickerID)
        {
            var value = $('#'+textID).val();
            $('#'+colorPickerID).val(value);
        }

        // Function for Label Color Opacity
        $('#label_color_transparency').on("change mousemove", function()
        {
            $('#slider-value').html($(this).val());
        });

    </script>

@endsection
