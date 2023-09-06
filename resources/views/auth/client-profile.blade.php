@extends('client.layouts.client-layout')

@section('title', 'Profile')

@section('content')

{{-- Page Title --}}
<div class="pagetitle">
    <h1>{{ __('Profile') }}</h1>
    <div class="row">
        <div class="col-md-8">
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">{{ __('Profile') }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

    {{-- Profile Section --}}
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

            {{-- Profile Card --}}
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        @if(!empty($user['image']))
                            <img src="{{ $user['image'] }}" alt="Profile" class="rounded-circle w-50">
                        @else
                            <img src="{{ asset('public/admin_images/demo_images/profiles/profile1.jpg') }}" alt="Profile" class="rounded-circle w-50">
                        @endif
                        <h3>{{ $user->firstname }} {{ $user->lastname }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card position-relative">
                    <a href="{{ route('client.profile.edit',encrypt($user->id)) }}" class="btn btn-sm btn-primary edit-profile-btn"><i class="bi bi-pencil"></i></a>
                    <div class="card-body pt-3">
                        <h5 class="card-title">{{ __('Profile Details') }}</h5>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('Role') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ ($user->user_type == 1) ? 'Admin' : 'Client' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('FirstName') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ $user->firstname }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('LastName') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ $user->lastname }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('Shop Name') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ $user->hasOneShop->shop['name'] }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('Email') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ $user->email }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-3 col-md-4 label"><b>{{ __('Joined At') }}</b></div>
                            <div class="col-lg-9 col-md-8">{{ date('d-M-Y',strtotime($user->created_at)) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection
