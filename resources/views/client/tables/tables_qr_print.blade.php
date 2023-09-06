@php
    $shop_id = (isset($shop_details['id'])) ? $shop_details['id'] : '';
    $shop_slug = (isset($shop_details['shop_slug'])) ? $shop_details['shop_slug'] : '';

    $shop_settings = getClientSettings($shop_id);
    $shop_logo = (isset($shop_settings['shop_view_header_logo'])) ? $shop_settings['shop_view_header_logo'] : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $shop_details['name'] }}</title>
    <link rel="stylesheet" href="{{ asset('public/client/assets/css/bootstrap.min.css') }}">
</head>
<body>
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row text-center">
                    @foreach ($shop_tables as $shop_table)
                        <div class="w-50 mt-3" style="margin-bottom: 60px;">
                            <div class="shop-logo">
                                @if(!empty($shop_logo) && file_exists('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$shop_logo))
                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$shop_logo) }}" width="100">
                                @endif
                            </div>
                            <div class="heder-text mt-1">
                                <h5 class="m-0">Scan It & Order It.</h5>
                            </div>
                            <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/tables/'.$shop_table['qr_code']) }}" width="260">
                            <p class="m-0"><strong>Table No. : {{ $shop_table['table_no'] }}</strong></p>
                            <h6 class="m-0">smartqrscan.com</h6>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-12 text-center mt-3 mb-3">
                <button class="btn btn-primary" onclick="window.print();">Print</button>
            </div>
        </div>
    </div>
</body>
</html>
