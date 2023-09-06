@php
    $shop_settings = getClientSettings($shop_details['id']);
    $shop_theme_id = isset($shop_settings['shop_active_theme']) ? $shop_settings['shop_active_theme'] : '';

    // Theme
    $theme = \App\Models\Theme::where('id',$shop_theme_id)->first();
    $theme_name = isset($theme['name']) ? $theme['name'] : '';

    // Theme Settings
    $theme_settings = themeSettings($shop_theme_id);

    $payment_settings = getPaymentSettings($shop_details['id']);
    $upi_id = (isset($payment_settings['upi_id'])) ? $payment_settings['upi_id'] : '';
    $payee_name = (isset($payment_settings['payee_name'])) ? $payment_settings['payee_name'] : '';
    $upi_qr = (isset($payment_settings['upi_qr'])) ? $payment_settings['upi_qr'] : '';

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

    // Order Settings
    $order_settings = getOrderSettings($shop_details['id']);
    $customer_details = (isset($order_settings['customer_details']) && !empty($order_settings['customer_details'])) ? $order_settings['customer_details'] : 0;

    $local = session('locale','en');

@endphp

<!DOCTYPE html>
<html lang="{{ $local }}" dir="{{ ($local == 'ar') ? "rtl" : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link href="{{ asset('public/admin_images/favicons/smartqrscan.ico') }}" rel="icon">
    @include('shop.shop-css')
</head>
<body class="{{ (!empty($theme_name) && $theme_name == 'Default Dark Theme') ? 'dark' : '' }} custom-scroll">

    {{-- Item Details Modal --}}
    <div class="modal fade" id="itemDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="item_dt_div">
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Details Modal --}}
    <div class="modal fade" id="customerDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body" id="cust_details_mod">
                    <form method="POST" id="custDetailsForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="shop_id" id="shop_id" value="{{ $shop_details['id'] }}">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h3>Enter Below Details</h3>
                            </div>
                            <div class="mt-3 col-md-12">
                                <label for="user_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="user_name" id="user_name" class="form-control" placeholder="Enter Your Name">
                            </div>
                            <div class="mt-3 col-md-12">
                                <label for="mobile_no" class="form-label">Mobile No. <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_no" id="mobile_no" class="form-control" placeholder="Enter Your Mobile Number" maxlength="10">
                            </div>
                            <div class="mt-3 col-md-12">
                                <a class="btn btn-success" onclick="SaveCustDetails()">Submit</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div class="modal fade" id="PaymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="PaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Pay Using UPI / QR </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="payment_detail_modal">
                    <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>{{ __('QR Code & Button For UPI Payment')}}</h5>
                                <p>Please Click on Pay Now Button for Confirm your order or Scan QR code to complete Payment.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(!empty($upi_qr) && file_exists('public/admin_uploads/upi_qr/'.$upi_qr))
                                    <img src="{{ asset('public/admin_uploads/upi_qr/'.$upi_qr) }}" width="200">
                                @endif
                                <p><strong>UPI ID : {{ $upi_id }}</strong></p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="payment_amount">Enter Amount</label>
                                <input type="number" name="payment_amount" id="payment_amount" class="form-control">
                            </div>
                            <div class="col-md-12 text-center mt-2">
                                <a pay-type="gpay" id="gpay_btn" class="btn me-2 mt-2 pay-btn" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/gpay.png') }}" height="40"></a>
                                <a pay-type="phonepe" id="phonepe_btn" class="btn me-2 mt-2 pay-btn" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/phonepe.png') }}" height="40"></a>
                                <a pay-type="paytm" id="paytm_btn" class="btn mt-2 pay-btn" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/paytm.png') }}" height="40"></a>
                                {{-- <a href="upi://pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am=0&cu={{ $currency }}" id="upi_btn" class="btn btn-success">Pay Now</a> --}}
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    @include('shop.shop-navbar')

    {{-- Main Content --}}
    <main id="main" class="main shop-main">
        @yield('content')
    </main>

    {{-- JS --}}
    @include('shop.shop-js')

    {{-- Custom JS --}}
    @yield('page-js')

</body>
</html>
