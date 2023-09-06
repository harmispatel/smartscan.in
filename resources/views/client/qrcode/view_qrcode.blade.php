@php
    $qr_image = isset($shop_details['qr_code']) ? $shop_details['qr_code'] : '';
    $shop_name = isset(Auth::user()->hasOneShop->shop['name']) ? Auth::user()->hasOneShop->shop['name'] : '';
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
    $new_shop_url = URL::to('/')."/".$shop_slug;

    $qr_setting = (isset($qr_data->value) && !empty($qr_data->value)) ? unserialize($qr_data->value) : '';

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Qr Code'))

@section('content')

    <section class="qr_main">
        <div class="sec_title">
            <h2>{{ __('Qr Code')}}</h2>
        </div>
        <form action="{{ route('qrcode.update.settings') }}" id="QrForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="qr_img position-relative">
                        <div class="text-center" id="qrImg">
                            @if(!empty($qr_image) && file_exists('public/admin_uploads/shops_qr/'.$qr_image))
                                <img src="{{ asset('public/admin_uploads/shops_qr/'.$qr_image) }}">
                            @endif
                        </div>
                        <input type="hidden" name="download_path" id="download_path" value="{{ asset('public/admin_uploads/shops_qr/'.$qr_image) }}">
                        <div>
                            <select name="file_type" id="file_type" class="form-select">
                                <option value="png">PNG</option>
                                <option value="svg">SVG</option>
                            </select>
                        </div>
                        <div class="qr_btn_group d-flex align-center justify-content-center mt-4 mb-3">
                            <a href="{{ asset('public/admin_uploads/shops_qr/'.$qr_image) }}" class="btn qr_btn btn-primary me-3" download>{{ __('Download')}}</a>
                            {{-- <a onclick="downloadImage()" class="btn qr_btn btn-primary me-3">{{ __('Download')}}</a> --}}
                            <a class="btn qr_btn btn-primary" onclick="printImg('{{ asset('public/admin_uploads/shops_qr/'.$qr_image) }}')">{{ __('Print')}}</a>
                        </div>
                        <div class="qr_loader" style="display: none;">
                            <img src="{{ asset('public/client_images/loader/loader1.gif') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="qr_sidebar">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <button class="nav-link active text-start" id="v-pills-Color-tab" data-bs-toggle="pill" data-bs-target="#v-pills-Color" type="button" role="tab" aria-controls="v-pills-Color" aria-selected="false">{{ __('Color')}}</button>
                                    <button class="nav-link text-start" id="v-pills-Style-tab" data-bs-toggle="pill" data-bs-target="#v-pills-Style" type="button" role="tab" aria-controls="v-pills-Style" aria-selected="false">{{ __('Style')}}</button>
                                    <button class="nav-link text-start" id="v-pills-Size-tab" data-bs-toggle="pill" data-bs-target="#v-pills-Size" type="button" role="tab" aria-controls="v-pills-Size" aria-selected="false">{{ __('Size')}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="qr_content">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active" id="v-pills-Color" role="tabpanel" aria-labelledby="v-pills-Color-tab">
                                        <div class="qr_color_content">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">{{ __('Select Color Type')}}</label>
                                                    <select class="form-select form-control qr_setting" name="color_type" id="color_type">
                                                        <option value="">Only Single Color</option>
                                                        <option value="vertical" {{ (isset($qr_setting['color_type']) && ($qr_setting['color_type'] == 'vertical')) ? 'selected' : '' }}> Vertical</option>
                                                        <option value="horizontal" {{ (isset($qr_setting['color_type']) && ($qr_setting['color_type'] == 'horizontal')) ? 'selected' : '' }}> Horizontal</option>
                                                        <option value="diagonal" {{ (isset($qr_setting['color_type']) && ($qr_setting['color_type'] == 'diagonal')) ? 'selected' : '' }}> Diagonal</option>
                                                        <option value="inverse_diagonal" {{ (isset($qr_setting['color_type']) && ($qr_setting['color_type'] == 'inverse_diagonal')) ? 'selected' : '' }}> Inverse Diagonal</option>
                                                        <option value="radial" {{ (isset($qr_setting['color_type']) && ($qr_setting['color_type'] == 'radial')) ? 'selected' : '' }}> Radial</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Select First Color')}}</label>
                                                        <input type="color" name="first_color" id="first_color" class="form-control form-control-color qr_setting" value="{{ (isset($qr_setting['first_color'])) ? $qr_setting['first_color'] : '' }}" title="Choose your First Color">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3" id="sec_color_div" style="display: none;">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Select Second Color')}}</label>
                                                        <input type="color" name="second_color" id="second_color" class="form-control form-control-color qr_setting" value="{{ (isset($qr_setting['second_color'])) ? $qr_setting['second_color'] : '' }}" title="Choose your Second Color">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3" id="color_trans_div">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Color Transparent')}}</label>
                                                        <input type="range" min="25" max="100" value="{{ (isset($qr_setting['color_transparent'])) ? $qr_setting['color_transparent'] : 100 }}" class="form-range qr_setting" name="color_transparent" id="color_transparent">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Select Background Color')}}</label>
                                                        <input type="color" class="form-control form-control-color qr_setting" name="background_color" id="background_color" value="{{ (isset($qr_setting['background_color'])) ? $qr_setting['background_color'] : "#ffffff" }}" title="Choose your Background Color">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Background Color Transparent')}}</label>
                                                        <input type="range" min="25" max="100" value="{{ (isset($qr_setting['background_color_transparent'])) ? $qr_setting['background_color_transparent'] : 25 }}" class="form-range qr_setting" name="background_color_transparent" id="background_color_transparent">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="v-pills-Style" role="tabpanel" aria-labelledby="v-pills-Style-tab">
                                        <div class="qr_color_content">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">{{ __('Select Qr Style')}}</label>
                                                    <select class="form-select qr_setting" name="qr_style" id="qr_style">
                                                        <option value="square" {{ (isset($qr_setting['qr_style']) && ($qr_setting['qr_style'] == 'square')) ? 'selected' : '' }}> Square</option>
                                                        <option value="dot" {{ (isset($qr_setting['qr_style']) && ($qr_setting['qr_style'] == 'dot')) ? 'selected' : '' }}> Dot</option>
                                                        <option value="round" {{ (isset($qr_setting['qr_style']) && ($qr_setting['qr_style'] == 'round')) ? 'selected' : '' }}> Round</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">{{ __('Select Eye Style')}}</label>
                                                    <select class="form-select qr_setting" name="eye_style" id="eye_style">
                                                        <option value="square" {{ (isset($qr_setting['eye_style']) && ($qr_setting['eye_style'] == 'square')) ? 'selected' : '' }}> Square</option>
                                                        <option value="circle" {{ (isset($qr_setting['eye_style']) && ($qr_setting['eye_style'] == 'circle')) ? 'selected' : '' }}> Circle</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Select Eye Inner Color')}}</label>
                                                        <input type="color" class="form-control qr_setting form-control-color" name="eye_inner_color" id="eye_inner_color" value="{{ (isset($qr_setting['eye_inner_color'])) ? $qr_setting['eye_inner_color'] : 100 }}" title="Choose your color">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('Select Eye Outer Color')}}</label>
                                                        <input type="color" name="eye_outer_color" id="eye_outer_color" class="form-control qr_setting form-control-color" value="{{ (isset($qr_setting['eye_outer_color'])) ? $qr_setting['eye_outer_color'] : 100 }}" title="Choose your color">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="v-pills-Size" role="tabpanel" aria-labelledby="v-pills-Size-tab">
                                        <div class="qr_color_content">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ __('QR Size')}}</label>
                                                        <input type="range" name="qr_size" id="qr_size" min="100" max="320" value="{{ (isset($qr_setting['qr_size'])) ? $qr_setting['qr_size'] : 100 }}" step="10" class="form-range qr_setting">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="qr_sidebar_btn_group mt-4 d-flex align-items-center">
                        <button class="btn btn-primary me-3">{{ __('Save')}}</button>
                        <a onclick="location.reload();" class="btn btn btn-outline-danger ">{{ __('Reset')}}</a>
                    </div>
                </div>
                <div class="col-md-12 mt-4 text-center">
                    <a href="{{ $new_shop_url }}" target="_blank">{{ $new_shop_url }}</a>
                </div>
            </div>
        </form>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        $('document').ready(function(){

            // Toggle Gradient Second Color Div & color Transparent Div
            var currSelectedVal = $('#color_type :selected').val();
            if(currSelectedVal == '')
            {
                $('#sec_color_div').hide();
                $('#color_trans_div').show();
            }
            else
            {
                $('#sec_color_div').show();
                $('#color_trans_div').hide();
            }

        });

        // Toggle Gradient Second Color Div & color Transparent Div
        $('#color_type').on('change',function(){
            var currVal = $(this).val();

            if(currVal == '')
            {
                $('#sec_color_div').hide();
                $('#color_trans_div').show();
            }
            else
            {
                $('#sec_color_div').show();
                $('#color_trans_div').hide();
            }

        });

        // Change Settings of QrCode
        $('.qr_setting').on('change',function(){

            myFormData = new FormData(document.getElementById("QrForm"));

            $.ajax({
                type: "POST",
                url: "{{ route('qrcode.settings') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                beforeSend: function(){
                    $(".qr_loader").show();
                },
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $(".qr_loader").hide();
                        $('#qrImg').html('');
                        $('#qrImg').append(response.data);
                    }
                    else
                    {
                        $('#QrForm').trigger('reset');
                        toastr.error(response.message);
                    }
                }
            });

        });

        // Function for Print QrCode
        function printImg(url) {
            var win = window.open('');
            win.document.write('<img src="' + url + '" onload="window.print();window.close()" />');
            win.focus();
        }

        // Success Message
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif


        $('#file_type').on('change',function()
        {
            var val = $(this).val();
            var og_path = $('#download_path').val();
            var change_path = og_path.replace('png',val);
            $('.qr_btn').attr('href',change_path);
        });

    </script>

@endsection

