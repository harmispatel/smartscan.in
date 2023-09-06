@php
    $enable_print = (isset($order_settings['enable_print']) && !empty($order_settings['enable_print'])) ? $order_settings['enable_print'] : 0;

    $admin_settings = getAdminSettings();
    $google_map_api = (isset($admin_settings['google_map_api'])) ? $admin_settings['google_map_api'] : '';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Order Settings'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Order Settings')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Settings') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Options Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="orderSettingsForm">
                            @csrf

                            {{-- Settings --}}
                            <div class="row">
                                <h3>{{ __('Settings') }}</h3>
                                <code>{{ __('If none of the settings bellow is enabled add-to-cart button will no be visible.') }}</code>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="delivery" id="delivery" class="ord-setting" {{ (isset($order_settings['delivery']) && $order_settings['delivery'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If delivery is disabled guests will not be able to make orders for delivery.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="delivery" class="form-label">{{ __('Delivery') }}</label>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="takeaway" id="takeaway" class="ord-setting" {{ (isset($order_settings['takeaway']) && $order_settings['takeaway'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If takeaway is disabled guests will not be able to make orders for takeaway.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="takeaway" class="form-label">{{ __('Takeaway') }}</label>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="room_delivery" id="room_delivery" class="ord-setting" {{ (isset($order_settings['room_delivery']) && $order_settings['room_delivery'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If room delivery is disabled guests will not be able to make orders for room delivery.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="room_delivery" class="form-label">{{ __('Room Delivery') }}</label>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="table_service" id="table_service" class="ord-setting" {{ (isset($order_settings['table_service']) && $order_settings['table_service'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If table service is disabled guests will not be able to make orders for table service.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="table_service" class="form-label">{{ __('Table Service') }}</label>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="only_cart" id="only_cart" class="ord-setting" {{ (isset($order_settings['only_cart']) && $order_settings['only_cart'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If only cart is enabled guests will not be able to continue to checkout page.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="only_cart" class="form-label">{{ __('Only Cart') }}</label>
                                </div>
                            </div>
                            <hr>

                            {{-- Other Settings --}}
                            <div class="row">
                                <h3>{{ __('Other Settings') }}</h3>
                            </div>
                            {{-- <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="auto_order_approval" id="auto_order_approval" class="ord-setting" {{ (isset($order_settings['auto_order_approval']) && $order_settings['auto_order_approval'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="auto_order_approval" class="form-label">{{ __('Auto Order Approval') }}</label>
                                </div>
                            </div> --}}
                            <div class="row mt-2">
                                <div class="col-md-6 mt-3">
                                    <label for="min_amount_for_delivery" class="form-label">{{ __('Minimum amount needed for delivery, if left null any amount is acceptable.') }}</label>
                                    <input type="number" name="min_amount_for_delivery" id="min_amount_for_delivery" class="form-control ord-setting" value="{{ (isset($order_settings['min_amount_for_delivery'])) ? $order_settings['min_amount_for_delivery'] : '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <label for="discount_type" class="form-label">{{ __('Discount Type') }}</label>
                                    <select name="discount_type" id="discount_type" class="form-select ord-setting">
                                        <option value="percentage" {{ (isset($order_settings['discount_type']) && $order_settings['discount_type'] == 'percentage') ? 'selected' : '' }}>{{ __('Percentage %') }}</option>
                                        <option value="fixed" {{ (isset($order_settings['discount_type']) && $order_settings['discount_type'] == 'fixed') ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <label for="discount_percentage" class="form-label">{{ __('Enter the desired discount value, discount applies to the total amount! Leave blank to disable.') }}</label>
                                    <input type="number" name="discount_percentage" id="discount_percentage" class="form-control ord-setting" value="{{ (isset($order_settings['discount_percentage'])) ? $order_settings['discount_percentage'] : '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <label for="order_arrival_minutes" class="form-label">{{ __('Default estimated minutes until order arrival') }}</label>
                                    <input type="number" name="order_arrival_minutes" id="order_arrival_minutes" class="form-control ord-setting" value="{{ (isset($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mt-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="customer_details" id="customer_details" class="ord-setting" {{ (isset($order_settings['customer_details']) && $order_settings['customer_details'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round" data-bs-toggle="tooltip" title="If customer details is enabled users will not be able to explore sites without enter his details.">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="only_cart" class="form-label">{{ __('Customer Details') }}</label>
                                </div>
                            </div>
                            <hr>

                            {{-- Notifaction Settings --}}
                            <div class="row">
                                <h3>{{ __('Notification Settings') }}</h3>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="play_sound" id="play_sound" class="ord-setting" {{ (isset($order_settings['play_sound']) && $order_settings['play_sound'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="play_sound" class="form-label">{{ __('Play Sound') }}</label>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-6 mt-2">
                                    <label for="notification_sound" class="form-label">{{ __('Notification Sound') }}</label>
                                    <select name="notification_sound" id="notification_sound" class="form-select">
                                        <option value="buzzer-01.mp3" {{ ($order_settings['notification_sound'] == 'buzzer-01.mp3') ? 'selected' : '' }}>Buzzer 1</option>
                                        <option value="buzzer-02.mp3" {{ ($order_settings['notification_sound'] == 'buzzer-02.mp3') ? 'selected' : '' }}>Buzzer 2</option>
                                        <option value="buzzer-03.mp3" {{ ($order_settings['notification_sound'] == 'buzzer-03.mp3') ? 'selected' : '' }}>Buzzer 3</option>
                                        <option value="buzzer-04.mp3" {{ ($order_settings['notification_sound'] == 'buzzer-04.mp3') ? 'selected' : '' }}>Buzzer 4</option>
                                        <option value="buzzer-05.mp3" {{ ($order_settings['notification_sound'] == 'buzzer-05.mp3') ? 'selected' : '' }}>Buzzer 5</option>
                                    </select>
                                </div>
                            </div>
                            <hr>

                            {{-- Printer Settings --}}
                            <div class="row">
                                <h3>{{ __('Print Settings') }}</h3>
                                <div class="col-md-12">
                                    <label><i class="bi bi-arrow-right-circle me-1 text-success"></i>{{ __('Before you enable print option.') }}</label> <br>
                                    <label><i class="bi bi-arrow-right-circle me-1 text-success"></i>{{ __('You have to install the client software JS Print Manager Ver. 6.x.x') }}</label> <br>
                                    <label><i class="bi bi-arrow-right-circle me-1 text-success"></i>{{ __('Download or Visit this') }} <a target="_incognito" href="https://www.neodynamic.com/downloads/jspm/">{{ __('Link') }}</a></label> <br>
                                    <a href="{{ url('/neodynamic-jspm/jspm6-6-win.zip') }}" class="btn btn-primary btn-sm mt-2 mb-2"><i class="bi bi-download"></i> Download</a> <br>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="enable_print" id="enable_print" class="ord-setting" {{ (isset($order_settings['enable_print']) && $order_settings['enable_print'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="enable_print" class="form-label">{{ __('Enable/Disable Print') }}</label>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="auto_print" id="auto_print" class="ord-setting" {{ (isset($order_settings['auto_print']) && $order_settings['auto_print'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="auto_print" class="form-label">{{ __('Auto Print') }}</label>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="printer_tray" class="form-label">{{ __('Printer Trays') }}</label>
                                    <select name="printer_tray" id="printer_tray" class="form-select">
                                        <option value="">not found</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="printer_paper" class="form-label">{{ __('Printer Papers') }}</label>
                                    <select name="printer_paper" id="printer_paper" class="form-select">
                                        <option value="">not found</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="default_printer" class="form-label">{{ __('Default Printer') }}</label>
                                    <select name="default_printer" id="default_printer" class="form-select">
                                        <option value="">not found</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="receipt_intro" class="form-label">{{ __('Receipt Intro') }}</label>
                                    <input type="text" name="receipt_intro" id="receipt_intro" class="form-control" value="{{ (isset($order_settings['receipt_intro'])) ? $order_settings['receipt_intro'] : '' }}">
                                </div>
                                <div class="col-md-6 mt-2">
                                    <label for="print_font_size" class="form-label">{{ __('Print Font Size') }}</label>
                                    <input type="number" name="print_font_size" id="print_font_size" value="{{ (isset($order_settings['print_font_size'])) ? $order_settings['print_font_size'] : '' }}" class="form-control">
                                </div>
                            </div>
                            <hr>


                            {{-- Checkout Optional Fields --}}
                            <div class="row">
                                <h3>{{ __('Checkout Optional Fields') }}</h3>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="email_field" id="email_field" class="ord-setting" {{ (isset($order_settings['email_field']) && $order_settings['email_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="email_field" class="form-label">{{ __('Email') }}</label>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="floor_field" id="floor_field" class="ord-setting" {{ (isset($order_settings['floor_field']) && $order_settings['floor_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="floor_field" class="form-label">{{ __('Floor') }}</label>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="door_bell_field" id="door_bell_field" class="ord-setting" {{ (isset($order_settings['door_bell_field']) && $order_settings['door_bell_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="door_bell_field" class="form-label">{{ __('Dool Bell') }}</label>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="full_name_field" id="full_name_field" class="ord-setting" {{ (isset($order_settings['full_name_field']) && $order_settings['full_name_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="full_name_field" class="form-label">{{ __('Full Name') }}</label>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="instructions_field" id="instructions_field" class="ord-setting" {{ (isset($order_settings['instructions_field']) && $order_settings['instructions_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="instructions_field" class="form-label">{{ __('Instructions') }}</label>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="live_address_field" id="live_address_field" class="ord-setting" {{ (isset($order_settings['live_address_field']) && $order_settings['live_address_field'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="live_address_field" class="form-label">{{ __('Live Address') }}</label>
                                </div>
                            </div>
                            <hr>

                            {{-- Delivery Settings --}}
                            <div class="row">
                                <h3>{{ __('Delivery / Takeaway Scheduler') }}</h3>
                                <div class="col-md-12 text-end">
                                    <label class="switch me-2">
                                        <input type="checkbox" value="1" name="scheduler_active" id="scheduler_active" class="ord-setting" {{ (isset($order_settings['scheduler_active']) && $order_settings['scheduler_active'] == 1) ? 'checked' : '' }}>
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                    <label for="scheduler_active" class="form-label">Activate</label>
                                </div>
                            </div>
                            @php
                                $schedule_arr = (isset($order_settings['schedule_array']) && !empty($order_settings['schedule_array'])) ? json_decode($order_settings['schedule_array'],true) : [];
                            @endphp
                            @if(count($schedule_arr) > 0)
                                <div class="row mt-3">
                                    <div class="col-md-12 sc_inner">
                                        <div class="sc_array_section" id="sc_array_section">
                                            @foreach($schedule_arr as $key => $sched)
                                                @php
                                                    $active_day = ($sched['enabled'] == 1) ? 'checked' : '';
                                                    $time_arr = $sched['timesSchedules'];
                                                @endphp
                                                <div class="p-2" id="{{ $key }}_sec">
                                                    <div class="text-center">
                                                        <input type="checkbox" class="me-2" name="" id="{{ $key }}" {{ $active_day }}> <label for="{{ $key }}">{{ $sched['name'] }}</label>
                                                    </div>
                                                    <div class="sch-sec">
                                                        @if(count($time_arr) > 0)
                                                            @foreach($time_arr as $tkey => $sc_time)
                                                                @php
                                                                    $time_key = $tkey + 1;
                                                                    $sc_start_time = $sc_time['startTime'];
                                                                    $sc_end_time = $sc_time['endTime'];
                                                                @endphp
                                                                <div class="sch_{{ $time_key }}">
                                                                    @if($time_key > 1)
                                                                        <div class="sch-minus">
                                                                            <i class="bi bi-dash-circle" onclick="$('#{{ $key }}_sec .sch_{{ $time_key }}').remove()"></i>
                                                                        </div>
                                                                    @endif
                                                                    <input type="time" class="form-control mt-2" name="startTime" id="startTime" value="{{ $sc_start_time }}">
                                                                    <input type="time" class="form-control mt-2" name="endTime" id="endTime" value="{{ $sc_end_time }}">
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="sch-plus">
                                                        <i class="bi bi-plus-circle" onclick="addNewSchedule('{{ $key }}_sec')"></i>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row mt-3">
                                    <div class="col-md-12 sc_inner">
                                        <div class="sc_array_section" id="sc_array_section">
                                            <div class="p-2" id="sunday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="sunday"> <label for="sunday">Sun</label>
                                                </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('sunday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="monday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="monday"> <label for="monday">Mon</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('monday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="tuesday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="tuesday"> <label for="tuesday">Tue</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('tuesday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="wednesday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="wednesday"> <label for="wednesday">Wed</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('wednesday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="thursday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="thursday"> <label for="thursday">Thu</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('thursday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="friday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="friday"> <label for="friday">Fri</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('friday_sec')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="saturday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="saturday"> <label for="saturday">Sat</label>
                                            </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('saturday_sec')"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <hr>

                            {{-- Delivery Range Settings --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>{{ __('Delivery Range Settings') }}</h3>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('remove.delivery.range') }}" class="btn btn-danger" data-bs-toggle="tooltip" title="Clear Delivery Range Settings"><i class="bi bi-trash"></i></a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <input type="hidden" name="new_coordinates" id="new_coordinates">
                                <div class="col-md-12">
                                    <div id="map" style="height: 500px;"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <button id="update-btn" class="btn btn-success" disabled><i class="bi bi-save"></i> Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection


{{-- Custom Script --}}
@section('page-js')

    <script src="{{ asset('public/admin/assets/js/jsprintmanager.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script async defer src='https://maps.googleapis.com/maps/api/js?key={{ $google_map_api }}&callback=initMap&libraries=drawing'></script>

    <script type="text/javascript">

        var map;
        var drawingManager;
        var selectedShape;
        const deliveryAreas = @json($deliveryAreas);
        var clientPrinters = null;
        var _this = this;
        var enablePrint = "{{ $enable_print }}";

        $(document).ready(function ()
        {
            // Get Curren Address
            navigator.geolocation.getCurrentPosition(
            function (position)
                {
                    initMap(position.coords.latitude, position.coords.longitude)
                },
                function errorCallback(error)
                {
                    console.log(error)
                }
            );
        });


        if(enablePrint == 1)
        {
            //WebSocket settings
            JSPM.JSPrintManager.license_url = "{{ route('jspm') }}";
            JSPM.JSPrintManager.auto_reconnect = true;
            JSPM.JSPrintManager.start();
            JSPM.JSPrintManager.WS.onStatusChanged = function () {
                if (jspmWSStatus()) {
                    //get client installed printers
                    JSPM.JSPrintManager.getPrintersInfo().then(function (myPrinters) {
                        clientPrinters = myPrinters;
                        var options = '';
                        for (var i = 0; i < clientPrinters.length; i++) {
                            options += '<option value="'+clientPrinters[i].name+'">' + clientPrinters[i].name + '</option>';
                        }
                        $('#default_printer').html(options);

                        // Set Default Printer
                        var def_printer = "{{ (isset($order_settings['default_printer'])) ? $order_settings['default_printer'] : '' }}";
                        $("#default_printer option[value='"+def_printer+"']").attr("selected", "selected");

                        _this.showSelectedPrinterInfo();
                    });
                }
            };
        }


        function showSelectedPrinterInfo()
        {
            // get selected printer index
            var idx = $("#default_printer")[0].selectedIndex;

            // get supported trays
            var options = '';
            for (var i = 0; i < clientPrinters[idx].trays.length; i++) {
                options += '<option value="'+clientPrinters[idx].trays[i]+'">' + clientPrinters[idx].trays[i] + '</option>';
            }
            $('#printer_tray').html(options);

            // get supported papers
            options = '';
            for (var i = 0; i < clientPrinters[idx].papers.length; i++) {
                options += '<option value="'+clientPrinters[idx].papers[i]+'">' + clientPrinters[idx].papers[i] + '</option>';
            }
            $('#printer_paper').html(options);

            // Set Default Paper
            var def_paper = "{{ (isset($order_settings['printer_paper'])) ? $order_settings['printer_paper'] : '' }}";
            $("#printer_paper option[value='"+def_paper+"']").attr("selected", "selected");

            // Set Default Tray
            var def_tray = "{{ (isset($order_settings['printer_tray'])) ? $order_settings['printer_tray'] : '' }}";
            $("#printer_tray option[value='"+def_tray+"']").attr("selected", "selected");
        }


        //Check JSPM WebSocket status
        function jspmWSStatus()
        {
            if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open)
                return true;
            else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
                alert('JSPrintManager (JSPM) is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm');
                return false;
            }
            else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Blocked) {
                alert('JSPM has blocked this website!');
                return false;
            }
        }


        // Function for Add Schedule Section
        function addNewSchedule(divID)
        {
            // sch-sec
            var html = '';
            var counter;
            counter = $('#'+divID+' .sch-sec').children('div').length + 1;

            html += '<div class="sch_'+counter+'">';
                html += '<div class="sch-minus">';
                    html += '<i class="bi bi-dash-circle" onclick="$(this).parent().parent().remove()"></i>';
                html += '</div>';
                html += '<input type="time" name="startTime" id="startTime" class="form-control mt-2">';
                html += '<input type="time" name="endTime" id="endTime" class="form-control mt-2">';
            html += '</div>';

            $('#'+divID+" .sch-sec").append(html);
        }

        // Enabled Update Btn
        $('input, #default_printer, #printer_paper, #printer_tray, #notification_sound, #discount_type').on('change',function(){
            $('#update-btn').removeAttr('disabled',true);
        });

        $('#map').on('click',function(){
            $('#update-btn').removeAttr('disabled',true);
        });

        // Function for Update Order Settings
        $('#update-btn').on("click",function(e)
        {
            e.preventDefault();

            var main_arr = {};
            var days_arr = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

            $.each(days_arr, function (indexInArray, day)
            {
                var dayName = $('#'+day+'_sec label').html();
                var checkedVal = $('#'+day+'_sec #'+day).is(":checked");
                var scheduleLength = $('#'+day+'_sec .sch-sec').children('div').length;
                var sch_all_childs = $('#'+day+'_sec .sch-sec').children('div');

                var time_arr = [];
                var inner_arr_1 = {};

                inner_arr_1['name'] = dayName;
                inner_arr_1['enabled'] = checkedVal;
                inner_arr_1['dayInWeek'] = indexInArray;

                for(var i=0;i<scheduleLength;i++)
                {
                    var inner_arr_2 = {};
                    var sch_child = sch_all_childs[i];
                    var className = sch_child.getAttribute('class');

                    inner_arr_2['startTime'] = $('#'+day+'_sec .sch-sec .'+className+' #startTime').val();
                    inner_arr_2['endTime'] = $('#'+day+'_sec .sch-sec .'+className+' #endTime').val();
                    time_arr.push(inner_arr_2);
                }

                inner_arr_1['timesSchedules'] = time_arr;
                main_arr[day] = inner_arr_1;
            });

            const myFormData = new FormData(document.getElementById('orderSettingsForm'));
            myFormData.append('schedule_array', JSON.stringify(main_arr));

            $.ajax({
                type: "POST",
                url: "{{ route('update.order.settings') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                }
            });

        });


        // Function for Map
        function initMap(lat=39.0742,long=21.8243)
        {
            // Set the center point of the map
            var center = {lat: lat, lng: long};

            // Create the map object
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: center
            });

            new google.maps.Marker({
                position: center,
                map,
            });

            // console.log(deliveryAreas);
            @foreach ($deliveryAreas as $deliveryArea)
                const polygon{{ $deliveryArea->id }} = new google.maps.Polygon({
                    paths:@json(unserialize($deliveryArea->coordinates)),
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#FF0000",
                    fillOpacity: 0.35,
                });
                polygon{{ $deliveryArea->id }}.setMap(map);
            @endforeach

            // Create a drawing manager
            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [
                        google.maps.drawing.OverlayType.POLYGON
                    ]
                },
                polygonOptions: {
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35
                }
            });

            // Set the drawing manager on the map
            drawingManager.setMap(map);

            // Add an event listener for when a polygon is completed
            google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
                selectedShape = polygon;
                $('#new_coordinates').val(getPolygonCoords());
            });

        }

        // Function to get the polygon coordinates
        function getPolygonCoords()
        {
            // Get the path of the selected shape
            var path = selectedShape.getPaths().getAt(0);

            var getCoordinates = $('#new_coordinates').val();
            var newCoordinate = [];

            if(getCoordinates == '')
            {
                var polygonCoords = [];
            }
            else
            {
                var polygonCoords = JSON.parse(getCoordinates);
            }

            // Loop through the path and get the coordinates
            for (var i = 0; i < path.getLength(); i++)
            {
                var latlngstr = path.getAt(i).toUrlValue(6);
                var latlngArr = latlngstr.split(',');
                var latLng = {};

                $.each(latlngArr, function (key, val)
                {
                    if(key == 0)
                    {
                        latLng['lat'] = parseFloat(val);
                    }
                    else
                    {
                        latLng['lng'] = parseFloat(val);
                    }
                });
                newCoordinate.push(latLng);
            }

            polygonCoords.push(newCoordinate);

            // Return the polygon coordinates
            return JSON.stringify(polygonCoords);
        }

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

    </script>
@endsection
