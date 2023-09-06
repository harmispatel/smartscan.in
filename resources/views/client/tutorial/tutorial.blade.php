@extends('client.layouts.client-layout')

@section('title', __('Tutorial'))

@section('content')



    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Tutorial') }}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tutorial') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <section class="toturial_main">
            <div class="container">
                <div class="sec_title">
                    <h1>{{ __('Tutorial') }}</h1>
                </div>
                <div class="toturial_inr">
                    <div class="accordion" id="accordionExample">
                        @forelse ($tutorial as $item)
                            @php
                                $title = str_replace(' ', '_', "$item->title");
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="{{ $item->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#{{ $title }}" aria-expanded="false"
                                        aria-controls="{{ $item->title }}">
                                        <i class="fa-solid fa-angle-right"></i>
                                        {{ $item->title }}
                                    </button>
                                </h2>
                                <div id="{{ $title }}" class="accordion-collapse collapse"
                                    aria-labelledby="{{ $item->id }}" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">

                                        <p>{!! $item->text !!}</p>
                                        @if (!empty($item->video))
                                            @php
                                                $file_ext = pathinfo($item->video, PATHINFO_EXTENSION);
                                            @endphp
                                            @if ($file_ext == 'mp4' || $file_ext == 'mov')
                                                <video class="w-100"
                                                    src="{{ asset('public/client_uploads/tutorial/' . $item->video) }}"
                                                    width="100px" autoplay muted loop>
                                                </video>
                                            @else
                                                <img class="w-100"
                                                    src="{{ asset('public/client_uploads/tutorial/' . $item->video) }}"
                                                    width="100px" />
                                                {{-- <a href="{{ route('design.cover.delete') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a> --}}
                                            @endif
                                        @else
                                            <img class="w-100"
                                                src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}"
                                                width="100px" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <h3 class="text-center">{{ __('Tutorial Not Found!') }}</h3>
                        @endforelse

                    </div>
                </div>
            </div>
        </section>
    </div>


@endsection
