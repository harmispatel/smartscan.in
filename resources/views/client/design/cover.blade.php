@php
    $client_settings = getClientSettings();
    $intro_status = isset($client_settings['intro_icon_status']) ? $client_settings['intro_icon_status'] : '';
    $intro_duration = isset($client_settings['intro_icon_duration']) ? $client_settings['intro_icon_duration'] : '';
    $shop_intro_icon = isset($client_settings['shop_intro_icon']) ? $client_settings['shop_intro_icon'] : '';

    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Cover'))

@section('content')

    <section class="logo_sec">
        <div class="row">
            <div class="col-md-12">
                <div class="add_logo_sec">
                    <div class="add_logo_sec_header d-flex align-items-center justify-content-between">
                        <h2>{{ __('Cover')}}</h2>
                        <label class="switch">
                            <input type="checkbox" id="intro_icon_status" name="intro_icon_status" {{ ($intro_status == 1) ? 'checked' : '' }}>
                            <span class="slider round">
                                <i class="fa-solid fa-circle-check check_icon"></i>
                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                            </span>
                        </label>
                    </div>
                    <div class="add_logo_sec_body">
                        <form id="introIconForm" action="javascript:void(0)" method="POST" enctype="multipart/form-data">
                            @csrf
                            <code>Intro image will appear before your digital menu for the duration specified image Specs : File size < 2MB, Recommended image size : (1920 x 1080) pixels.</code>
                            <div class="add_logo_sec_body_inr">
                                <div>
                                    <label for="shop_intro_icon" class="position-relative" style="cursor: pointer;">
                                        @if(!empty($shop_intro_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_intro_icon))
                                            @php
                                                $file_ext = pathinfo($shop_intro_icon, PATHINFO_EXTENSION);
                                            @endphp
                                            @if($file_ext == 'mp4' || $file_ext == 'mov')
                                                <video src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_intro_icon) }}" width="200px" autoplay muted loop>
                                                </video>
                                                <a href="{{ route('design.cover.delete') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a>
                                            @else
                                                <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/intro_icons/'.$shop_intro_icon) }}" width="200px"/>
                                                <a href="{{ route('design.cover.delete') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a>
                                            @endif
                                        @else
                                            <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="200px"/>
                                        @endif
                                        <div class="logo-loader" style="display: none;">
                                            <img src="{{ asset('public/client_images/loader/loader1.gif') }}">
                                        </div>
                                    </label>
                                    <input type="file" id="shop_intro_icon" name="shop_intro_icon" style="display: none;" />
                                    <div class="form-group mt-2">
                                        <label class="form-label">{{ __('Intro duration in seconds')}}</label>
                                        <input type="number" class="form-control" name="intro_icon_duration" id="intro_icon_duration" value="{{ $intro_duration }}">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        // Change Intro Status
        $('#intro_icon_status').on('change',function()
        {
            // Clear all Toastr Messages
            toastr.clear();

            var check_status = $(this).is(':checked');
            if(check_status == true)
            {
                var status = 1;
            }
            else
            {
                var status = 0;
            }

            $.ajax({
                type: "POST",
                url: "{{ route('design.intro.status') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    "status" : status,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    }
                }
            });

        });


        // Change Intro Icon Duration
        $('#intro_icon_duration').on('change',function()
        {
            var duration = $(this).val();

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('design.intro.duration') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    "duration" : duration,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    }
                }
            });

        });


        // Upload Intro Icon
        $("#shop_intro_icon").on('change',function()
        {
            myFormData = new FormData(document.getElementById("introIconForm"));

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('design.intro.icon') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                beforeSend: function(){
                    $(".logo-loader").show();
                },
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                        $(".logo-loader").hide();
                        setTimeout(() => {
                            location.reload();
                        }, 1300);
                    }
                    else
                    {
                        $('#introIconForm').trigger('reset');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    // All Validation Errors
                    const validationErrors = (response?.responseJSON?.errors) ? response.responseJSON.errors : '';

                    if (validationErrors != '')
                    {
                        // Image Error
                        var imageError = (validationErrors.shop_intro_icon) ? validationErrors.shop_intro_icon : '';
                        if (imageError != '')
                        {
                            toastr.error(imageError);
                        }
                    }
                }
            });

        });

    </script>

@endsection
