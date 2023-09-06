@php
    $userID = Auth::user()->id;
    $userShopId = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

    // Get Language Settings
    $language_settings = clientLanguageSettings($userShopId);
    $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';
    $google_translate = isset($language_settings['google_translate']) ? $language_settings['google_translate'] : 0;

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Language'))

@section('content')

    <input type="hidden" name="shopID" id="shopID" value="{{ $userShopId }}">

    <section class="lang_main">
        <div class="row">
            <div class="col-md-3">
                <div class="lang_sidebar">
                    <div class="lang_title">
                        <h2>{{ __('Menu')}}</h2>
                    </div>
                    <ul class="nav flex-column lang_menu_ul" id="nav_accordion">
                        @if(count($categories) > 0)
                            @foreach ($categories as $category)
                                @php
                                    $cat_name_key = $lang_code."_name";
                                    $cat_name = $category[$cat_name_key];
                                @endphp
                                <li class="nav-item has-submenu">
                                    <a class="nav-link" style="cursor: pointer;">
                                        <span class="arrow-icon"><i class="fa-solid fa-chevron-right me-3"></i></span>
                                        <span onclick="getCatDetails({{ $category->id }})"><i class="fa-solid fa-cart-shopping me-1"></i> {{ $cat_name }}</span>
                                    </a>
                                    <ul class="submenu collapse lang_menu_ul">
                                        @if(isset($category->items) && count($category->items) > 0)
                                            @foreach ($category->items as $item)
                                            @php
                                                $item_name_key = $lang_code."_name";
                                                $item_name = $item[$item_name_key];
                                            @endphp
                                                <li><a class="nav-link" style="cursor: pointer;" onclick="getItemDetails({{ $item->id }})">{{ $item_name }}</a></li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </li>
                            @endforeach
                        @endif
                </div>
            </div>
            <div class="col-md-9">
                <div class="lang_right_side">
                    <div class="lang_title">
                        <h2>{{ __('Translations')}}</h2>
                        <p>On the left you can find your menu's structure. Click on every menu element and insert the translation of the selected additional languages. Note that primary language descriptions can only be changed through ‘Menu’ tab features.</p>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ __('Primary Language')}}</label>
                                <select class="form-select" name="primary_language" id="primary_language" onchange="setPrimaryLanguage({{ $userShopId }})">
                                    @if(count($languages) > 0)
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" {{ ($primary_lang_id == $language->id) ? 'selected' : '' }}>{{ $language->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        @php
                            $additional_lang_arr = [];
                            if(count($additional_languages) > 0)
                            {
                                foreach ($additional_languages as $key => $value)
                                {
                                    $additional_lang_arr[] = $value->language_id;
                                }
                            }
                        @endphp
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="additional_languages">{{ __('Additional Languages')}}</label>
                                <select name="additional_languages[]" id="additional_languages" class="form-select" multiple>
                                    @if(count($languages) > 0)
                                        @foreach ($languages as $key => $language)
                                            @if($primary_lang_id != $language->id)
                                                <option value="{{ $language->id }}" {{ (in_array($language->id,$additional_lang_arr)) ? 'selected' : '' }}>{{ $language->name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ __('Google translate')}}</label>
                                <br/>
                                <label class="switch">
                                    <input type="checkbox" name="google_translate" id="google_translate" value="1" {{ ($google_translate == 1) ? 'checked' : '' }}>
                                    <span class="slider round">
                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="select_lang">
                        <div class="row" id="langBox">
                            @if(count($additional_languages) > 0)
                                @foreach ($additional_languages as $key => $lang)
                                    <div class="col-md-3 mb-2 language_{{ $lang->language_id }}">
                                        <div class="select_lang_inr">
                                            <div class="">
                                                {{-- <input class="form-check-input" type="checkbox" value="1" id="cbox_{{ $lang->id }}"> --}}
                                                <label class="form-check-label" for="cbox_{{ $lang->id }}">{{ $lang->language['name'] }}</label>
                                            </div>
                                            <label class="switch">
                                                <input type="checkbox"  id="publish_{{ $lang->id }}" {{ ($lang->published == 1) ? 'checked' : '' }} onchange="changeLanguageStatus({{ $lang->id }})">
                                                <span class="slider round">
                                                    <span class="check_icon">{{ __('Publish')}}</span>
                                                    <span class="uncheck_icon">{{ __('Unpublish')}}</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="cate_lang_box">
                    </div>
                    <div class="item_lang_box">
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        // Select2 for Primary Language
        var primarySelect = $('#primary_language').select2();


        // Function for Set User's Primary Language
        function setPrimaryLanguage(shopID)
        {
            var languageID = $('#primary_language :selected').val();

            $.ajax({
                type: "POST",
                url: '{{ route("language.set-primary") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'language_id': languageID,
                    'shop_id': shopID,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });

        }

        // Add Additional Language
        $('#additional_languages').select2().on("select2:select", function (event)
        {
            var languageIds = $('#additional_languages').val();
            var shopID = $('#shopID').val();

            $.ajax({
                type: "POST",
                url: '{{ route("language.set-additional") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'language_ids': languageIds,
                    'shop_id': shopID,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                        $('#langBox').html('');
                        $('.cate_lang_box').html('');
                        $('.item_lang_box').html('');
                        $('#langBox').append(response.data);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });

        });


        // Remove Additional Language
        $('#additional_languages').select2().on("select2:unselect", function (event)
        {
            var languageID = event.params.data.id;
            var shopID = $('#shopID').val();

            $.ajax({
                type: "POST",
                url: '{{ route("language.delete-additional") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'language_id': languageID,
                    'shop_id': shopID,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                        $('.language_'+languageID).remove();
                        $('.cate_lang_box').html('');
                        $('.item_lang_box').html('');
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });
        });


        // Function for Change Status of Additional Language.
        function changeLanguageStatus(id)
        {
            var isChecked = $('#publish_'+id).is(":checked");
            isChecked = (isChecked == true) ? 1 : 0;

            toastr.clear();

            $.ajax({
                type: "POST",
                url: '{{ route("language.changeStatus") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'id': id,
                    'isChecked': isChecked,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });

        }


        // Category Menu Accordian
        document.addEventListener("DOMContentLoaded", function()
        {
            document.querySelectorAll('.lang_sidebar .nav-link').forEach(function(element)
            {
                element.addEventListener('click', function (e)
                {
                    let nextEl = element.nextElementSibling;
                    let parentEl  = element.parentElement;

                    if(nextEl)
                    {
                        e.preventDefault();
                        let mycollapse = new bootstrap.Collapse(nextEl);

                        if(nextEl.classList.contains('show'))
                        {
                            mycollapse.hide();
                        }
                        else
                        {
                            mycollapse.show();
                            // find other submenus with class=show
                            var opened_submenu = parentEl.parentElement.querySelector('.submenu.show');
                            // if it exists, then close all of them
                            if(opened_submenu)
                            {
                                new bootstrap.Collapse(opened_submenu);
                            }
                        }
                    }

                });
            });
        });


        // Get Language wise Category Details
        function getCatDetails(id)
        {
            toastr.clear();

            $.ajax({
                type: "POST",
                url: '{{ route("language.categorydetails") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'id': id,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                        $('.cate_lang_box').html('');
                        $('.item_lang_box').html('');
                        $('.cate_lang_box').append(response.data);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });
        }


        // Update Language Wise Category Details
        function updateCategoryDetail(langCode)
        {
            var formID = langCode+"_cat_form";
            var myFormData = new FormData(document.getElementById(formID));

            // Remove Validation Class
            $(formID+' #category_name').removeClass('is-invalid');
            $(formID+' #category_desc').removeClass('is-invalid');
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('language.update-category-details') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                        location.reload();
                    }
                },
                error: function(response)
                {
                    // All Validation Errors
                    const validationErrors = (response?.responseJSON?.errors) ? response.responseJSON.errors : '';

                    if (validationErrors != '')
                    {
                        // Name Error
                        var nameError = (validationErrors.category_name) ? validationErrors.category_name : '';
                        if (nameError != '')
                        {
                            $(formID+' #category_name').addClass('is-invalid');
                            toastr.error(nameError);
                        }

                        // Description Error
                        var descriptionError = (validationErrors.category_desc) ? validationErrors.category_desc : '';
                        if (descriptionError != '')
                        {
                            $(formID+' #category_desc').addClass('is-invalid');
                            toastr.error(descriptionError);
                        }
                    }
                }
            });

        }


        // Get Language wise Product Details
        function getItemDetails(id)
        {
            toastr.clear();

            $.ajax({
                type: "POST",
                url: '{{ route("language.itemdetails") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'id': id,
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success(response.message);
                        $('.cate_lang_box').html('');
                        $('.item_lang_box').html('');
                        $('.item_lang_box').append(response.data);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1200);
                    }
                }
            });
        }


        // Update Language Wise Item Details
        function updateItemDetail(langCode)
        {
            var formID = langCode+"_item_form";
            var myFormData = new FormData(document.getElementById(formID));

            // Remove Validation Class
            $(formID+' #item_name').removeClass('is-invalid');
            $(formID+' #item_desc').removeClass('is-invalid');
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('language.update-item-details') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                        location.reload();
                    }
                },
                error: function(response)
                {
                    // All Validation Errors
                    const validationErrors = (response?.responseJSON?.errors) ? response.responseJSON.errors : '';

                    if (validationErrors != '')
                    {
                        // Name Error
                        var nameError = (validationErrors.item_name) ? validationErrors.item_name : '';
                        if (nameError != '')
                        {
                            $(formID+' #item_name').addClass('is-invalid');
                            toastr.error(nameError);
                        }

                        // Description Error
                        var descriptionError = (validationErrors.item_desc) ? validationErrors.item_desc : '';
                        if (descriptionError != '')
                        {
                            $(formID+' #item_desc').addClass('is-invalid');
                            toastr.error(descriptionError);
                        }
                    }
                }
            });

        }


        // Function for On/Off Google Translate Functionality
        $('#google_translate').on('change',function(){
            var isChecked = $(this).prop('checked');
            if(isChecked == true)
            {
                isChecked = 1;
            }
            else
            {
                isChecked = 0;
            }

            $.ajax({
                type: "POST",
                url: "{{ route('language.google.translate') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    'status' : isChecked,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });

        });

    </script>

@endsection

