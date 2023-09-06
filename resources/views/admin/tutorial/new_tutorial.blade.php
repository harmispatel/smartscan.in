@extends('admin.layouts.admin-layout')

@section('title', __('New Tutorial'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Tutorial')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tutorial') }}">{{ __('Tutorial')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('New Tutorial')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('tutorial') }}" class="btn btn-sm new-amenity btn-primary">
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
                    <form class="form" action="{{ route('tutorial.store') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            @csrf
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name" class="form-label">{{ __('Title')}}</label>
                                            <span class="text-danger">*</span>
                                            <input type="text" name="title" id="title" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}" placeholder="Enter Tutorial Title" value="{{ old('title') }}">
                                            @if($errors->has('title'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('title') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="status" class="form-label">{{ __('Image/Video')}}</label>
                                            <span class="text-danger">*</span>
                                            <input type="file" name="file" class="form-control {{ ($errors->has('file')) ? 'is-invalid' : '' }}">
                                            @if($errors->has('file'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('file') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <label for="text" class="form-label">{{ __('Text')}}</label>
                                            <span class="text-danger">*</span>
                                            <textarea name="text" id="text" rows="5" placeholder="Enter Tutorial Text" class="form-control {{ ($errors->has('text')) ? 'is-invalid' : '' }}"></textarea>
                                            @if($errors->has('text'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('text') }}
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
        $( document ).ready(function() {
        tinymce.init({
                selector: 'textarea'
            });
        });
    </script>
@endsection
