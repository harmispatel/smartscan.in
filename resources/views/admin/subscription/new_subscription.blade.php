@extends('admin.layouts.admin-layout')

@section('title', __('New Subscription'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Subscriptions')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('subscriptions') }}">{{ __('Subscriptions')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('New Subscription')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('subscriptions') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- New Subscription add Section --}}
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

            {{-- Subscription Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('subscriptions.store') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            @csrf
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name" class="form-label">{{ __('Title')}}</label>
                                            <input type="text" name="title" id="title" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}" placeholder="Enter Subscription Title" value="{{ old('title') }}">
                                            @if($errors->has('title'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('title') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="price" class="form-label">{{ __('Price')}}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">€</span>
                                                <input type="number" name="price" id="price" class="form-control {{ ($errors->has('price')) ? 'is-invalid' : '' }}" placeholder="Enter Price" value="{{ old('price') }}">
                                                @if($errors->has('price'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('price') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-group">
                                            <label for="duration" class="form-label">{{ __('Duration')}}</label>
                                            <input type="number" name="duration" id="duration" class="form-control {{ ($errors->has('duration')) ? 'is-invalid' : '' }}" placeholder="Enter Duration" value="{{ old('duration') }}">
                                            @if($errors->has('duration'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('duration') }}
                                                </div>
                                            @endif
                                        </div>
                                        <code>{{ __('Enter Duration in Months')}}</code>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="icon" class="form-label">{{ __('Icon')}}</label>
                                            <input type="file" name="icon" id="icon" class="form-control {{ ($errors->has('icon')) ? 'is-invalid' : '' }}">
                                            @if($errors->has('icon'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('icon') }}
                                                </div>
                                            @endif
                                        </div>
                                        <code>Valid Dimensions of Icon is up to (200*200)</code>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-group">
                                            <label for="status" class="form-label">{{ __('Status')}}</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" value="1" checked>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <div class="form-group">
                                            <label for="description" class="form-label">{{ __('Description')}}</label>
                                            <textarea name="description" id="description" rows="5" placeholder="Enter Subscription Description" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <b>{{ __('Permissions') }}</b>
                                            </div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-md-6">
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="add_edit_clone_theme" role="switch" id="add_edit_clone_theme" value="1">
                                                        <label for="add_edit_clone_theme" class="form-label ms-2">Add, Edit & Clone Themes</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="page" role="switch" id="page" value="1">
                                                        <label for="page" class="form-label ms-2">Page</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="banner" role="switch" id="banner" value="1">
                                                        <label for="banner" class="form-label ms-2">Banner</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="pdf_file" role="switch" id="pdf_file" value="1">
                                                        <label for="pdf_file" class="form-label ms-2">PDF</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="link" role="switch" id="link" value="1">
                                                        <label for="link" class="form-label ms-2">Link</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="gallery" role="switch" id="gallery" value="1">
                                                        <label for="gallery" class="form-label ms-2">Gallery</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="check_in" role="switch" id="check_in" value="1">
                                                        <label for="check_in" class="form-label ms-2">Check In</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="ordering" role="switch" id="ordering" value="1">
                                                        <label for="ordering" class="form-label ms-2">Ordering</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="special_icons" role="switch" id="special_icons" value="1">
                                                        <label for="special_icons" class="form-label ms-2">Special Icons</label>
                                                    </div>
                                                </div>
                                            </div>
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
