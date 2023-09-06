@extends('admin.layouts.admin-layout')

@section('title', __('Edit Special Icons'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Special Icons')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('ingredients') }}">{{ __('Special Icons')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Special Icons')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('ingredients') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Edit Ingredient Section --}}
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

            {{-- Ingredients Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('ingredients.update') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            @csrf
                            <div class="container">
                                <div class="row">
                                    <input type="hidden" name="ingredient_id" id="ingredient_id" value="{{ $ingredient->id }}">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name" class="form-label">{{ __('Name')}}</label>
                                            <input type="text" name="name" id="name" class="form-control {{ ($errors->has('name')) ? 'is-invalid' : '' }}" placeholder="Enter Special Icon Name" value="{{ $ingredient->name }}">
                                            @if($errors->has('name'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('name') }}
                                                </div>
                                            @endif
                                        </div>
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
                                        <div class="form-group mt-2">
                                            <code>Valid Dimensions of Icon is up to (80*80)</code>
                                        </div>
                                        <div class="form-group mt-2">
                                            @if(!empty($ingredient->icon) && file_exists('public/admin_uploads/ingredients/'.$ingredient->icon))
                                                <img src="{{ asset('public/admin_uploads/ingredients/'.$ingredient->icon) }}">
                                            @else
                                                <img src="{{ asset('public/admin_images/not-found/not-found4.png') }}" width="40">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="status" class="form-label">{{ __('Status')}}</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" value="1" {{ ($ingredient->status == 1) ? 'checked' : '' }}>
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
