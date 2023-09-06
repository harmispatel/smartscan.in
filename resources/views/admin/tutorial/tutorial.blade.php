@extends('admin.layouts.admin-layout')

@section('title', __('Tutorial'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Tutorial')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tutorial')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('tutorial.add') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Subscriptions Section --}}
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

            {{-- Subscriptions Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="subscriptionsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        <th>{{ __('Title')}}</th>
                                        <th>{{ __('Image/Video')}}</th>
                                        <th>{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tutorial as $tutorialvalue)
                                        <tr>
                                            <td>{{ $tutorialvalue->id }}</td>
                                            <td>{{ $tutorialvalue->title }}</td>
                                            <td>
                                                @if(!empty($tutorialvalue->video))
                                                @php
                                                    $file_ext = pathinfo($tutorialvalue->video, PATHINFO_EXTENSION);
                                                @endphp
                                                @if($file_ext == 'mp4' || $file_ext == 'mov')
                                                    <video  src="{{ asset('public/client_uploads/tutorial/'.$tutorialvalue->video)}}" width="100px" autoplay muted loop>
                                                    </video>
                                                @else
                                                    <img  src="{{ asset('public/client_uploads/tutorial/'.$tutorialvalue->video) }}" width="100px"/>
                                                    {{-- <a href="{{ route('design.cover.delete') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a> --}}
                                                @endif
                                            @else
                                                <img  src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="100px"/>
                                            @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('tutorial.edit',$tutorialvalue->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('tutorial.destroy',$tutorialvalue->id) }}" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">{{ __('Tutorial Not Found!')}}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
