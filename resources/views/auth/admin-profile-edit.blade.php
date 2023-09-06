@extends('admin.layouts.admin-layout')

@section('title', 'Edit Profile')

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Edit Profile')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Profile')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Edit User Section --}}
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

            {{-- Userz Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                                            <label for="firstname" class="form-label">{{ __('First Name')}}</label>
                                            <input type="text" name="firstname" id="firstname" class="form-control {{ ($errors->has('firstname')) ? 'is-invalid' : '' }}" value="{{ $user->firstname }}">
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
                                            <input type="text" name="lastname" id="lastname" class="form-control" value="{{ $user->lastname }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="email" class="form-label">{{ __('Email')}}</label>
                                            <input type="text" name="email" id="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}" value="{{ $user->email }}">
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
                                            <input type="password" name="password" id="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}" value="">
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
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control {{ ($errors->has('confirm_password')) ? 'is-invalid' : '' }}" value="">
                                            @if($errors->has('confirm_password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('confirm_password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="profile_picture" class="form-label">{{ __('Profile Picture')}}</label>
                                            <input type="file" name="profile_picture" id="profile_picture" class="form-control {{ ($errors->has('profile_picture')) ? 'is-invalid' : '' }}" value="">
                                            @if($errors->has('profile_picture'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('profile_picture') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Preview')}}</label>
                                            <div>
                                                @if(!empty($user->image))
                                                    <img src="{{ $user->image }}" width="100">
                                                @else
                                                    <img src="{{ asset('public/admin_images/not-found/not-found2.png') }}" width="100">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success">{{ __('Update')}}</button>
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
