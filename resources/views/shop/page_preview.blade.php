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
@endphp


@extends('shop.shop-layout')

@section('title', $shop_title)

@section('content')

    <input type="hidden" name="tag_id" id="tag_id" value="">

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

                <div class="page-div-main mt-4">
                    @if($cat_details->category_type == 'page')
                        <div class="page-details">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    @php
                                        $page_cover_img = isset($cat_details->categoryImages[0]->image) ? $cat_details->categoryImages[0]->image : '';
                                    @endphp
                                    @if(!empty($page_cover_img) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$page_cover_img))
                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$page_cover_img) }}" class="w-100">
                                    @endif
                                </div>
                                <div class="col-md-12 mt-3 text-center">
                                    <h3>{{ $cat_details->$name_key }}</h3>
                                </div>
                                <div class="col-md-12 mt-3">
                                    {!! $cat_details->$description_key !!}
                                </div>
                            </div>
                        </div>
                    @elseif($cat_details->category_type == 'gallery')
                        @php
                            $gallery_images = isset($cat_details->categoryImages) ? $cat_details->categoryImages : [];
                        @endphp
                        <div class="gallary-details">
                            <div class="row">
                                <div class="col-md-12 mb-3 text-center">
                                    <h3>{{ $cat_details->$name_key }}</h3>
                                </div>
                                @if(count($gallery_images) > 0)
                                    @foreach ($gallery_images as $album)
                                        @if(!empty($album->image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$album->image))
                                            {{-- <div class="col-md-3 mb-3">
                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$album->image) }}" class="w-100">
                                            </div> --}}
                                            <div class="col-lg-3 col-md-6 image">
                                                <div class="img-wrapper">
                                                    <a href="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$album->image) }}"><img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$album->image) }}" class="img-responsive"></a>
                                                    <div class="img-overlay">
                                                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @elseif($cat_details->category_type == 'pdf_page')
                        @php
                            $pdf_file = (!empty($cat_details->file) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_details->file)) ? asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_details->file) : '';
                        @endphp
                        <input type="hidden" name="pdf_url" id="pdf_url" value="{{ $pdf_file }}">
                        <div class="pdf-view">
                            <div class="row">
                                <div class="col-md-12 mb-3 text-center">
                                    <h3>{{ $cat_details->$name_key }}</h3>
                                </div>
                                <div class="col-md-12" id="canvas_container">
                                </div>
                            </div>
                        </div>
                    @elseif($cat_details->category_type == 'check_in')
                        <div class="check-in-page">
                            <div class="row justify-content-center">
                                <div class="col-md-12 mb-3 text-center">
                                    <h3>{{ $cat_details->$name_key }}</h3>
                                    <div class="check-in-page-desc">
                                        {!! $cat_details->$description_key !!}
                                    </div>
                                </div>
                                @php
                                    $check_page_styles = (isset($cat_details->styles) && !empty($cat_details->styles)) ? unserialize($cat_details->styles) : '';
                                @endphp
                                <div class="col-md-8 mb-3">
                                    <div class="check-in-form" style="background-color: {{ isset($check_page_styles['background_color']) ? $check_page_styles['background_color'] : '' }}">
                                        <form action="{{ route('do.check.in') }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="store_id" id="store_id" value="{{ ($shop_details['id']) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label for="firstname" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="firstname" id="firstname" placeholder="Enter Your First Name" class="form-control {{ ($errors->has('firstname')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('firstname'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('firstname') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="lastname" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="lastname" id="lastname" placeholder="Enter Your Last Name" class="form-control {{ ($errors->has('lastname')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('lastname'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('lastname') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="email" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Email <span class="text-danger">*</span></label>
                                                    <input type="text" name="email" id="email" placeholder="Enter Your Email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('email'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('email') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="phone" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Phone No. <span class="text-danger">*</span></label>
                                                    <input type="number" name="phone" id="phone" placeholder="Enter Your Phone No." class="form-control {{ ($errors->has('phone')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('phone'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('phone') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="passport" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Passport No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="passport" id="passport" placeholder="Enter Your Passport No." class="form-control {{ ($errors->has('passport')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('passport'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('passport') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="room_number" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Room No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="room_number" id="room_number" class="form-control {{ ($errors->has('room_number')) ? 'is-invalid' : '' }}" placeholder="Enter Your Room No.">
                                                    @if($errors->has('room_number'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('room_number') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="nationality" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Nationality <span class="text-danger">*</span></label>
                                                    <input type="text" name="nationality" id="nationality" placeholder="Enter Your Nationality" class="form-control {{ ($errors->has('nationality')) ? 'is-invalid' : '' }}">
                                                    @if($errors->has('nationality'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('nationality') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-2">
                                                                <label for="date_of_birth" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Date of Birth <span class="text-danger">*</span></label>
                                                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control {{ ($errors->has('date_of_birth')) ? 'is-invalid' : '' }}">
                                                                @if($errors->has('date_of_birth'))
                                                                    <div class="invalid-feedback">
                                                                        {{ $errors->first('date_of_birth') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label for="arrival_date" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Arrival Date <span class="text-danger">*</span></label>
                                                                <input type="datetime-local" name="arrival_date" id="arrival_date" class="form-control {{ ($errors->has('arrival_date')) ? 'is-invalid' : '' }}">
                                                                @if($errors->has('arrival_date'))
                                                                    <div class="invalid-feedback">
                                                                        {{ $errors->first('arrival_date') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label for="departure_date" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Departure Date <span class="text-danger">*</span></label>
                                                                <input type="datetime-local" name="departure_date" id="departure_date" class="form-control {{ ($errors->has('departure_date')) ? 'is-invalid' : '' }}">
                                                                @if($errors->has('departure_date'))
                                                                    <div class="invalid-feedback">
                                                                        {{ $errors->first('departure_date') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="residence_address" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Residence Address <span class="text-danger">*</span></label>
                                                                <input type="text" name="residence_address" id="residence_address" class="form-control {{ ($errors->has('residence_address')) ? 'is-invalid' : '' }}" placeholder="Enter Your Residence Address.">
                                                                @if($errors->has('residence_address'))
                                                                    <div class="invalid-feedback">
                                                                        {{ $errors->first('residence_address') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-group">
                                                                <label for="message" class="form-label" style="color: {{ isset($check_page_styles['font_color']) ? $check_page_styles['font_color'] : '' }}">Message</label>
                                                                <textarea placeholder="Write Your Message here..." name="message" id="message" class="w-100 form-control"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 text-center mt-2">
                                                    <button class="btn btn-sm" style="background-color: {{ isset($check_page_styles['button_color']) ? $check_page_styles['button_color'] : '#198754' }}; color: {{ isset($check_page_styles['button_text_color']) ? $check_page_styles['button_text_color'] : '#fff' }}">SUBMIT</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>

{{-- Page JS Function --}}
@section('page-js')

    <script type="text/javascript">

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 4000
        }

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        @if (Session::has('errors'))
            toastr.error('Please Check Form Carefully!')
        @endif

        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif


        // PDF Load
        var pdf_url = $('#pdf_url').val();
        var pdfFile = null;
        var scale = 1.7;

        if(pdf_url != undefined)
        {
            loadPdf();
        }

        function loadPdf()
        {
            pdfjsLib.getDocument($('#pdf_url').val()).then((pdf) =>
            {
                pdfFile = pdf;
                viewer = document.getElementById('canvas_container');
                for(page = 1; page <= pdf.numPages; page++)
                {
                    canvas = document.createElement("canvas");
                    canvas.className = 'pdf-page-canvas';
                    canvas.id = 'pdf-page-canvas-'+page;
                    viewer.appendChild(canvas);
                    render(page, canvas);
                }
            });
        }

        // Rendering Pdf Pages
        function render(currPage,currCanvas)
        {
            pdfFile.getPage(currPage).then((page) =>
            {
                var mycan = document.getElementById("pdf-page-canvas-"+currPage);
                var ctx = mycan.getContext('2d');

                var viewport = page.getViewport(scale);
                mycan.width = viewport.width;
                mycan.height = viewport.height;

                page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
            });
        }

    </script>

@endsection
