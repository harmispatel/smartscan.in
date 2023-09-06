@php
    $client_settings = getClientSettings();
    $logo = isset($client_settings['shop_view_header_logo']) ? $client_settings['shop_view_header_logo'] : '';
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Logo'))

@section('content')

    <section class="logo_sec">
        <div class="row">
            <div class="col-md-12">
                <div class="add_logo_sec">
                    <div class="add_logo_sec_header">
                        <h2>{{ __('Logo')}}</h2>
                    </div>
                    <div class="add_logo_sec_body">
                        <p>{{ __('Logo will appear on the top of your menu')}}</p>
                        <form id="logoForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="add_logo_sec_body_inr position-relative">
                                <label for="shop_view_header_logo" class="position-relative" style="cursor: pointer;">
                                    @if(!empty($logo) && file_exists('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo))
                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/top_logos/'.$logo) }}" width="200px"/>
                                        <a href="{{ route('design.logo.delete') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a>
                                    @else
                                        <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="200px"/>
                                    @endif
                                    <div class="logo-loader" style="display: none;">
                                        <img src="{{ asset('public/client_images/loader/loader1.gif') }}">
                                    </div>
                                </label>
                                <input type="file" id="shop_view_header_logo" name="shop_view_header_logo" style="display: none;" />
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

        $("#shop_view_header_logo").on('change',function()
        {
            myFormData = new FormData(document.getElementById("logoForm"));

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('design.logo.upload') }}",
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
                        $('#logoForm').trigger('reset');
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
                        var imageError = (validationErrors.shop_view_header_logo) ? validationErrors.shop_view_header_logo : '';
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
