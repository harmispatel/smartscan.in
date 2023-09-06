<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Every Pay Payment</title>
    <link rel="icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/3929344?s=280&v=4">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/css/toastr.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>
    <style>
        .padding
        {
            padding: 5rem !important;
        }

        .form-control:focus {
            box-shadow: 10px 0px 0px 0px #ffffff !important;
            border-color: #4ca746;
        }
    </style>
</head>
<body>
    <div class="padding">
        <div class="row">
            <form action="{{ route('everypay.payment',$shop_slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="total_amount" value="{{ $total_amount }}">
                <div class="container-fluid d-flex justify-content-center">
                    <div class="col-sm-8 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <span>Every Pay</span>
                                    </div>
                                    <div class="col-md-6 text-right" style="margin-top: -5px;">
                                        <img src="https://img.icons8.com/color/36/000000/visa.png">
                                        <img src="https://img.icons8.com/color/36/000000/mastercard.png">
                                        <img src="https://img.icons8.com/color/36/000000/amex.png">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="cc-number" class="form-label">{{ __('CARD NUMBER') }}</label>
                                            <input id="cc-number" name="card_number" type="tel" class="input-lg form-control cc-number" autocomplete="cc-number" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cc-exp" class="form-label">{{ __('CARD EXPIRY') }}</label>
                                            <input id="cc-exp" name="card_expiry" type="tel" class="input-lg form-control cc-exp" autocomplete="cc-exp" placeholder="&bull;&bull; / &bull;&bull;" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cc-cvc" class="form-label">{{ __('CARD CVC') }}</label>
                                            <input id="cc-cvc" name="card_cvc" type="tel" class="input-lg form-control cc-cvc" autocomplete="off" placeholder="&bull;&bull;&bull;&bull;" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="numeric" class="form-label">{{ __('CARD HOLDER NAME') }}</label>
                                            <input type="text" name="card_holder" class="input-lg form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button class="btn btn-success">{{ __('Pay') }} {{ $total_amount_text }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.payment/3.0.0/jquery.payment.min.js"></script>
    <script>

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            timeOut: 4000
        }

        // Error Messages
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        $(function($)
        {
            $('[data-numeric]').payment('restrictNumeric');
            $('.cc-number').payment('formatCardNumber');
            $('.cc-exp').payment('formatCardExpiry');
            $('.cc-cvc').payment('formatCardCVC');
        });
    </script>
</body>
</html>
