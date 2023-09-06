<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Smart QR SCAN</title>
    <link href="{{ asset('public/admin_images/favicons/smartqrscan.ico') }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,400;0,500;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/css/toastr.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/css/swiper.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/frontend/css/frontend.css') }}" >
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            @if(isset($junction['logo']) && !empty($junction['logo']) && file_exists('public/admin_uploads/junctions_logo/'.$junction['logo']))
                <a class="navbar-brand m-0" href="{{ route('junction',$junction['junction_slug']) }}">
                    <img src="{{ asset('public/admin_uploads/junctions_logo/'.$junction['logo']) }}" height="70">
                </a>
            @endif
            <a class="navbar-brand m-0" href="{{ route('junction',$junction['junction_slug']) }}">{{ $junction['junction_name']; }}</a>
        </div>
    </nav>
    <section class="junctions-shops-lists">
        <div class="section-title">
            <h1 class="text-center">Our Shops</h1>
        </div>
        <div class="container">
            <div class="row justify-content-center">
                @php
                    $shop_ids = (isset($junction['shop_ids']) && !empty($junction['shop_ids'])) ? unserialize($junction['shop_ids']) : [];
                @endphp
                @if(count($shop_ids) > 0)
                    @foreach ($shop_ids as $shopid)
                        @php
                            $shop_details = \App\Models\Shop::where('id',$shopid)->first();
                            $shop_id = (isset($shop_details['id'])) ? $shop_details['id'] : '';
                            $shop_name = (isset($shop_details['name'])) ? $shop_details['name'] : '';
                            $shop_slug = (isset($shop_details['shop_slug'])) ? $shop_details['shop_slug'] : '';
                            $shop_settings = getClientSettings($shopid);
                            $shop_logo = (isset($shop_settings['shop_view_header_logo'])) ? $shop_settings['shop_view_header_logo'] : '';
                        @endphp
                        @if (!empty($shop_id))
                            <div class="col-md-3">
                                <div class="junction-shop">
                                    <a href="{{ route('restaurant',$shop_slug) }}" target="_blank">
                                        <div class="shop-logo">
                                            @if(!empty($shop_logo) && file_exists('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$shop_logo))
                                                <img class="w-100" height="70" src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$shop_logo) }}">
                                            @else
                                                <img class="w-100" height="70" src="{{  asset('public/admin_images/not-found/no-logo-available.jpg') }}">
                                            @endif
                                        </div>
                                        <hr>
                                        <h3>{{ $shop_name }}</h3>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <div class="loader">
        {{-- <img src="{{ asset('public/admin_images/logos/qr_gif.gif') }}" width="200px"> --}}
        <div class="restaurant-loader">
            <div class="restaurant-loader-inner"></div>
        </div>
    </div>

    <script src="{{ asset('public/client/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/js/swiper.min.js"></script>
    <script>
        $(document).ready(function ()
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
        });
    </script>

</body>
</html>
