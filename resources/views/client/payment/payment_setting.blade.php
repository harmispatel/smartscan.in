@extends('client.layouts.client-layout')

@section('title', __('Payment Settings'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Payment Settings')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Payment Settings') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Payment Settings Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('update.payment.settings') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h3>{{ __('Cash') }}</h3>
                                        <label class="switch me-2">
                                            <input type="checkbox" value="1" name="cash" id="cash" {{ (isset($payment_settings['cash']) && $payment_settings['cash'] == 1) ? 'checked' : '' }}>
                                            <span class="slider round" data-bs-toggle="tooltip" title="Active / InActive Cash">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h3>{{ __('Cash POS') }}</h3>
                                        <label class="switch me-2">
                                            <input type="checkbox" value="1" name="cash_pos" id="cash_pos" {{ (isset($payment_settings['cash_pos']) && $payment_settings['cash_pos'] == 1) ? 'checked' : '' }}>
                                            <span class="slider round" data-bs-toggle="tooltip" title="Active / InActive Cash POS">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h3>{{ __('EveryPay') }}</h3>
                                        <label class="switch me-2">
                                            <input type="checkbox" value="1" name="every_pay" id="every_pay" {{ (isset($payment_settings['every_pay']) && $payment_settings['every_pay'] == 1) ? 'checked' : '' }}>
                                            <span class="slider round" data-bs-toggle="tooltip" title="Active / InActive EveryPay">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="everypay_mode" class="form-label">{{ __('EveryPay Mode') }}</label>
                                    <select name="everypay_mode" id="everypay_mode" class="form-select">
                                        <option value="1" {{ (isset($payment_settings['everypay_mode']) && $payment_settings['everypay_mode'] == 1) ? 'selected' : '' }}>Sandbox</option>
                                        <option value="0" {{ (isset($payment_settings['everypay_mode']) && $payment_settings['everypay_mode'] == 0) ? 'selected' : '' }}>Live</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="every_pay_public_key" class="form-label">{{ __('Public Key') }}</label>
                                    <input type="text" name="every_pay_public_key" id="every_pay_public_key" class="form-control {{ ($errors->has('every_pay_public_key')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['every_pay_public_key'])) ? $payment_settings['every_pay_public_key'] : '' }}">
                                    @if($errors->has('every_pay_public_key'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('every_pay_public_key') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="every_pay_private_key" class="form-label">{{ __('Private Key') }}</label>
                                    <input type="text" name="every_pay_private_key" id="every_pay_private_key" class="form-control {{ ($errors->has('every_pay_private_key')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['every_pay_private_key'])) ? $payment_settings['every_pay_private_key'] : '' }}">
                                    @if($errors->has('every_pay_private_key'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('every_pay_private_key') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <hr> --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h3>PayPal</h3>
                                        <label class="switch me-2">
                                            <input type="checkbox" value="1" name="paypal" id="paypal" {{ (isset($payment_settings['paypal']) && $payment_settings['paypal'] == 1) ? 'checked' : '' }}>
                                            <span class="slider round" data-bs-toggle="tooltip" title="Active / InActive PayPal">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="paypal_mode" class="form-label">Paypal Mode</label>
                                    <select name="paypal_mode" id="paypal_mode" class="form-select">
                                        <option value="sandbox" {{ (isset($payment_settings['paypal_mode']) && $payment_settings['paypal_mode'] == 'sandbox') ? 'selected' : '' }}>Sandbox</option>
                                        <option value="live" {{ (isset($payment_settings['paypal_mode']) && $payment_settings['paypal_mode'] == 'live') ? 'selected' : '' }}>Live</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="paypal_public_key" class="form-label">{{ __('Public Key') }}</label>
                                    <input type="text" name="paypal_public_key" id="paypal_public_key" class="form-control {{ ($errors->has('paypal_public_key')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['paypal_public_key'])) ? $payment_settings['paypal_public_key'] : '' }}">
                                    @if($errors->has('paypal_public_key'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('paypal_public_key') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="paypal_private_key" class="form-label">{{ __('Private Key') }}</label>
                                    <input type="text" name="paypal_private_key" id="paypal_private_key" class="form-control {{ ($errors->has('paypal_private_key')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['paypal_private_key'])) ? $payment_settings['paypal_private_key'] : '' }}">
                                    @if($errors->has('paypal_private_key'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('paypal_private_key') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h3>UPI Payment</h3>
                                        <label class="switch me-2">
                                            <input type="checkbox" value="1" name="upi_payment" id="upi_payment" {{ (isset($payment_settings['upi_payment']) && $payment_settings['upi_payment'] == 1) ? 'checked' : '' }}>
                                            <span class="slider round" data-bs-toggle="tooltip" title="Active / InActive UPI">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="upi_id" class="form-label">{{ __('UPI ID') }}</label>
                                    <input type="text" name="upi_id" id="upi_id" class="form-control {{ ($errors->has('upi_id')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['upi_id'])) ? $payment_settings['upi_id'] : '' }}">
                                    @if($errors->has('upi_id'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('upi_id') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="payee_name" class="form-label">{{ __('Payee Name') }}</label>
                                    <input type="text" name="payee_name" id="payee_name" class="form-control {{ ($errors->has('payee_name')) ? 'is-invalid' : '' }}" value="{{ (isset($payment_settings['payee_name'])) ? $payment_settings['payee_name'] : '' }}">
                                    @if($errors->has('payee_name'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('payee_name') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="upi_qr" class="form-label">{{ __('UPI QR') }}</label>
                                    <input type="file" name="upi_qr" id="upi_qr" class="form-control {{ ($errors->has('upi_qr')) ? 'is-invalid' : '' }}" value="">
                                    @if($errors->has('upi_qr'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('upi_qr') }}
                                        </div>
                                    @endif
                                    <div class="form-group mt-3">
                                        @if(isset($payment_settings['upi_qr']) && !empty($payment_settings['upi_qr']) && file_exists('public/admin_uploads/upi_qr/'.$payment_settings['upi_qr']))
                                            <img src="{{ asset('public/admin_uploads/upi_qr/'.$payment_settings['upi_qr']) }}" width="150">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection


{{-- Custom Script --}}
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

        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

    </script>
@endsection
