@php
    $payment_settings = getPaymentSettings($shop_details['id']);
    $upi_id = (isset($payment_settings['upi_id'])) ? $payment_settings['upi_id'] : '';
    $payee_name = (isset($payment_settings['payee_name'])) ? $payment_settings['payee_name'] : '';
    $upi_qr = (isset($payment_settings['upi_qr'])) ? $payment_settings['upi_qr'] : '';

@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UPI Payment</title>
    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/client/assets/css/bootstrap.min.css') }}">

    <!-- custom css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/client/assets/css/custom.css') }}">

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"/>

    {{-- Bootstarp Icons --}}
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">

    {{-- Toastr CSS --}}
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/css/toastr.min.css') }}">

    {{-- Swiper --}}
    <link rel="stylesheet" href="{{ asset('public/client/assets/css/swiper-bundle.min.css') }}">

    {{-- Masonary --}}
    <link rel="stylesheet" href="{{ asset('public/client/assets/css/lightbox.css') }}">
</head>
<body>

    {{-- Payment Modal --}}
    <div class="modal fade" id="PaymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="PaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body" id="payment_div">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>{{ __('QR Code & Button For UPI Payment')}}</h5>
                                <p>Please Click on Pay Now Button for Confirm your order or Scan QR code to complete Payment.</p>
                            </div>
                            <div class="col-md-12 text-center">
                                <h3>Total Amount : {{ Currency::currency($currency)->format($amount) }}</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(!empty($upi_qr) && file_exists('public/admin_uploads/upi_qr/'.$upi_qr))
                                    <img src="{{ asset('public/admin_uploads/upi_qr/'.$upi_qr) }}" width="200">
                                @endif
                                <p><strong>UPI ID : {{ $upi_id }}</strong></p>
                            </div>
                            <div class="col-md-12 text-center mt-2">
                                <a href="gpay://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am={{ $amount }}&cu={{ $currency }}&url={{ URL::to('/') }}" id="upi_btn" class="btn me-2 mt-2" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/gpay.png') }}" height="40"></a>
                                <a href="phonepe://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am={{ $amount }}&cu={{ $currency }}&url={{ URL::to('/') }}" id="upi_btn" class="btn me-2 mt-2" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/phonepe.png') }}" height="40"></a>
                                <a href="paytm://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am={{ $amount }}&cu={{ $currency }}&url={{ URL::to('/') }}" id="paytm_btn" class="btn mt-2" style="border: 1px solid green"><img src="{{ asset('public/admin_images/logos/paytm.png') }}" height="40"></a>
                                {{-- <a href="upi://pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am={{ $amount }}&cu={{ $currency }}&url=https://google.com" id="upi_btn" class="btn btn-success">Pay Now</a> --}}
                            </div>
                            <div class="col-md-12 mt-2 text-center">
                                <p class="p-1 m-0"><strong>Your Order ID is : {{ $order_id }}</strong></p>
                            </div>
                            <div class="col-md-12 text-center mt-1">
                                <p>You will be automatically redirected to the website after <strong class="countdown">1 minute.</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="mt-5 mb-5">
        <div class="container px-3 my-5 clearfix">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 text-center mb-3">
                            <h3>Thank You for Your Order <i class="bi bi-hand-thumbs-up"></i></h3>
                        </div>
                        <div class="col-md-12 text-center mb-3 pending-div">
                            <img src="{{ asset('public/client_images/loader/loader1.gif') }}" width="80">
                        </div>
                        <div class="col-md-12 text-center mb-3 pending-div">
                            <p>{{ __('A store Employee checks your order.') }}</p>
                        </div>
                        <div class="col-md-12 text-center mb-3 success-div" style="display: none;">
                            <i class="bi bi-check-circle text-success fs-2"></i>
                            <p>Your order has been accepted and will be at your location in 30 minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Bootstrap --}}
    <script src="{{ asset('public/client/assets/js/bootstrap.min.js') }}"></script>

    {{-- Jquery --}}
    <script src="{{ asset('public/client/assets/js/jquery.min.js') }}"></script>

    <script src="{{ asset('public/client/assets/js/swiper-bundle.min.js') }}"></script>

    {{-- Toastr --}}
    <script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('public/client/assets/js/custom.js') }}"></script>

    {{-- Masonary --}}
    <script src="{{ asset('public/client/assets/js/lightbox.js') }}"></script>

    <script>

        const redirectURL = @json($success_url);

        $(document).ready(function () {
            $('#PaymentModal').modal('show');
            // $('#PaymentModal #upi_btn')[0].click();
        });

        setInterval(() => {
            window.location.href = redirectURL;
        }, 90000);

        var timer2 = "1:30";
        var interval = setInterval(function()
        {
            var timer = timer2.split(':');

            var minutes = parseInt(timer[0], 10);
            var seconds = parseInt(timer[1], 10);
            --seconds;
            minutes = (seconds < 0) ? --minutes : minutes;
            if (minutes < 0)
            {
                clearInterval(interval);
                return false;
            }
            seconds = (seconds < 0) ? 59 : seconds;
            seconds = (seconds < 10) ? '0' + seconds : seconds;
            $('.countdown').html(minutes + ':' + seconds);
            timer2 = minutes + ':' + seconds;
        }, 1000);

    </script>
</body>
</html>
