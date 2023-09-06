@extends('admin.layouts.admin-layout')

@section('title',__('New Clients'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Clients')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients') }}">{{ __('Clients')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('New Clients')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('clients.list') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- New Clients add Section --}}
    <section class="section dashboard">
        <div class="row">
            {{-- Error Message Section --}}
            @if (session()->has('error'))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- Success Message Section --}}
            @if (session()->has('success'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- Clients Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            @csrf
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3>{{ __('Client Details')}}</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="firstname" class="form-label">{{ __('First Name')}}</label>
                                            <input type="text" name="firstname" id="firstname" class="form-control {{ ($errors->has('firstname')) ? 'is-invalid' : '' }}" placeholder="Enter First Name" value="{{ old('firstname') }}">
                                            @if($errors->has('firstname'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('firstname') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="lastname" class="form-label">{{ __('Last Name')}}</label>
                                            <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Enter Last Name" value="{{ old('lastname') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="email" class="form-label">{{ __('Client Email')}}</label>
                                            <input type="text" name="email" id="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}" placeholder="Enter Client Email" value="{{ old('email') }}">
                                            @if($errors->has('email'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('email') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="password" class="form-label">{{ __('Password')}}</label>
                                            <input type="password" name="password" id="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}" placeholder="Enter Password">
                                            @if($errors->has('password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">{{ __('Confirm Password')}}</label>
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control {{ ($errors->has('confirm_password')) ? 'is-invalid' : '' }}" placeholder="Confirm Password">
                                            @if($errors->has('confirm_password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('confirm_password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label">{{ __('Status')}}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" value="1" checked>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="favourite" class="form-label">{{ __('Favorite')}}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="favourite" role="switch" id="favourite" value="1">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h3>{{ __('Shop Details')}}</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="shop_name" class="form-label">{{ __('Shop Name')}}</label>
                                            <input type="text" name="shop_name" id="shop_name" class="form-control {{ ($errors->has('shop_name')) ? 'is-invalid' : '' }}" placeholder="Enter Shop Name" value="{{ old('shop_name') }}">
                                            @if($errors->has('shop_name'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('shop_name') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="shop_url" class="form-label">{{ __('Shop URL')}}</label>
                                            <input type="text" name="shop_url" id="shop_url" class="form-control {{ ($errors->has('shop_url')) ? 'is-invalid' : '' }}" placeholder="Enter Shop URL" value="{{ old('shop_url') }}">
                                            <code>Enter Shop Url in Only String.</code>
                                            @if($errors->has('shop_url'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('shop_url') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="shop_logo" class="form-label">{{ __('Shop Logo')}}</label>
                                            <input type="file" name="shop_logo" id="shop_logo" class="form-control">
                                        </div>
                                        <code>Upload Shop Logo (150*80) or (150*150)</code>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="primary_language" class="form-label">{{ __('Primary Language')}}</label>
                                            <select name="primary_language" id="primary_language" class="form-select {{ ($errors->has('primary_language')) ? 'is-invalid' : '' }}">
                                                <option value="">Select Primary Language</option>
                                                @if(count($languages) > 0)
                                                    @foreach ($languages as $language)
                                                        <option value="{{ $language->id }}" {{ (old('primary_language') == $language->id) ? 'selected' : ''}}>{{ $language->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if($errors->has('primary_language'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('primary_language') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <label for="shop_description" class="form-label">{{ __('Shop Description')}}</label>
                                            <textarea name="shop_description" id="shop_description" rows="5" class="form-control">{{ old('shop_description') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <h3>{{ __('Subscription Details')}}</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="subscription" class="form-label">{{ __('Subscriptions')}}</label>
                                            <select name="subscription" id="subscription" class="form-control {{ ($errors->has('subscription')) ? 'is-invalid' : '' }}">
                                                <option value="">Select Subscription</option>
                                                @if (count($subscriptions) > 0)
                                                    @foreach ($subscriptions as $subscription)
                                                        <option value="{{ $subscription->id }}" {{ (old('subscription') == $subscription->id) ? 'selected' : ''}}>{{ $subscription->name }} ({{$subscription->duration}} Months)</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if($errors->has('subscription'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('subscription') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success">{{ __('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')
    <script type="text/javascript">

    </script>
@endsection
