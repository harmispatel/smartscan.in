@php
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : "";
    $primary_lang_details = clientLanguageSettings($shop_id);

    $language = getLangDetails(isset($primary_lang_details['primary_language']) ? $primary_lang_details['primary_language'] : '');
    $language_code = isset($language['code']) ? $language['code'] : '';
    $name_key = $language_code."_name";

    $shop_settings = getClientSettings($shop_id);

    // Order Settings
    $order_setting = getOrderSettings($shop_id);
    // Default Printer
    $default_printer = (isset($order_setting['default_printer']) && !empty($order_setting['default_printer'])) ? $order_setting['default_printer'] : 'Microsoft Print to PDF';
    // Printer Paper
    $printer_paper = (isset($order_setting['printer_paper']) && !empty($order_setting['printer_paper'])) ? $order_setting['printer_paper'] : 'A4';
    // Printer Tray
    $printer_tray = (isset($order_setting['printer_tray']) && !empty($order_setting['printer_tray'])) ? $order_setting['printer_tray'] : '';
    // Auto Print
    $auto_print = (isset($order_setting['auto_print']) && !empty($order_setting['auto_print'])) ? $order_setting['auto_print'] : 0;
    $enable_print = (isset($order_setting['enable_print']) && !empty($order_setting['enable_print'])) ? $order_setting['enable_print'] : 0;
    // Print Font Size
    $printFontSize = (isset($order_setting['print_font_size']) && !empty($order_setting['print_font_size'])) ? $order_setting['print_font_size'] : 20;

    $discount_type = (isset($order->discount_type) && !empty($order->discount_type)) ? $order->discount_type : 'percentage';

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';


    // Optional Fields
    $email_field = (isset($order_settings['email_field']) && $order_settings['email_field'] == 1) ? $order_settings['email_field'] : 0;
    $floor_field = (isset($order_settings['floor_field']) && $order_settings['floor_field'] == 1) ? $order_settings['floor_field'] : 0;
    $door_bell_field = (isset($order_settings['door_bell_field']) && $order_settings['door_bell_field'] == 1) ? $order_settings['door_bell_field'] : 0;
    $full_name_field = (isset($order_settings['full_name_field']) && $order_settings['full_name_field'] == 1) ? $order_settings['full_name_field'] : 0;
    $instructions_field = (isset($order_settings['instructions_field']) && $order_settings['instructions_field'] == 1) ? $order_settings['instructions_field'] : 0;
    $live_address_field = (isset($order_settings['live_address_field']) && $order_settings['live_address_field'] == 1) ? $order_settings['live_address_field'] : 0;

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Order Details'))

@section('content')

    <input type="hidden" name="default_printer" id="default_printer" value="{{ $default_printer }}">
    <input type="hidden" name="printer_paper" id="printer_paper" value="{{ $printer_paper }}">
    <input type="hidden" name="printer_tray" id="printer_tray" value="{{ $printer_tray }}">

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Order Details')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('client.orders') }}">{{ __('Orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Details') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Order Details Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12 mb-3" id="print-data" style="display: none;"></div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-2">
                                <h3>{{ __('Order') }} : #{{ $order->id }}</h3>
                            </div>
                            <div class="col-md-6 mb-2 text-end">
                                @if($enable_print == 1)
                                    <a class="btn btn-sm btn-primary ms-3" onclick="printReceipt({{ $order->id }})"><i class="bi bi-printer"></i> Print</a>
                                @endif
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                            <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="client-order-info">
                                                            <div class="">
                                                                <i class="bi bi-calendar-date"></i>&nbsp;{{ __('Order Date') }}
                                                            </div>
                                                            <div class="fw-bold">
                                                                {{ date('d-m-Y h:i:s',strtotime($order->created_at)) }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="client-order-info">
                                                            <div class="">
                                                                <i class="bi bi-credit-card"></i>&nbsp;{{ __('Payment Method') }}
                                                            </div>
                                                            <div class="fw-bold">
                                                                {{ $order->payment_method }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="client-order-info">
                                                            <div class="">
                                                                <i class="bi bi-truck"></i>&nbsp;{{ __('Shipping Method') }}
                                                            </div>
                                                            <div class="fw-bold">
                                                                {{ $order->checkout_type }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($order->checkout_type == 'table_service')
                                                    <tr>
                                                        <td class="text-muted">
                                                            <div class="client-order-info">
                                                                <div class="">
                                                                    <i class="bi bi-table"></i>&nbsp;{{ __('Table No.') }}
                                                                </div>
                                                                <div class="fw-bold">
                                                                    {{ $order->table }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @if($order->checkout_type != 'table_service')
                                <div class="col-md-6 mb-2">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                                <tbody class="fw-semibold text-gray-600">
                                                    @if($order->checkout_type == 'takeaway' || $order->checkout_type == 'room_delivery' || $order->checkout_type == 'delivery')
                                                        <tr>
                                                            <td class="text-muted">
                                                                <div class="client-order-info">
                                                                    <div class="">
                                                                        <i class="bi bi-person-circle"></i>&nbsp;{{ __('Customer') }}
                                                                    </div>
                                                                    <div class="fw-bold">
                                                                        {{ $order->firstname }} {{ $order->lastname }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($order->checkout_type == 'takeaway' || $order->checkout_type == 'delivery')
                                                        <tr>
                                                            <td class="text-muted">
                                                                <div class="client-order-info">
                                                                    <div class="">
                                                                        <i class="bi bi-envelope"></i>&nbsp;{{ __('Email') }}
                                                                    </div>
                                                                    <div class="fw-bold text-break">
                                                                        {{ $order->email }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">
                                                                <div class="client-order-info">
                                                                    <div class="">
                                                                        <i class="bi bi-telephone"></i>&nbsp;{{ __('Mobile No.') }}
                                                                    </div>
                                                                    <div class="fw-bold">
                                                                        {{ $order->phone }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($order->checkout_type == 'room_delivery')
                                                        <tr>
                                                            <td class="text-muted">
                                                                <div class="client-order-info">
                                                                    <div class="">
                                                                        <i class="bi bi-house"></i>&nbsp;{{ __('Room No.') }}
                                                                    </div>
                                                                    <div class="fw-bold text-break">
                                                                        {{ $order->room }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">
                                                                <div class="client-order-info">
                                                                    <div class="">
                                                                        <i class="bi bi-bicycle"></i>&nbsp;{{ __('Delivery Time') }}
                                                                    </div>
                                                                    <div class="fw-bold text-break">
                                                                        {{ $order->delivery_time }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($order->checkout_type == 'delivery')
                                <div class="col-md-12 mt-2 mb-2">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                                <tbody class="fw-semibold text-gray-600">
                                                    <tr>
                                                        <td class="text-muted">
                                                            <div class="client-order-info">
                                                                <div class="">
                                                                    <i class="bi bi-map"></i>&nbsp;{{ __('Address') }}
                                                                </div>
                                                                <div class="fw-bold">
                                                                    {{ $order->address }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">
                                                            <div class="client-order-info">
                                                                <div class="">
                                                                    <i class="bi bi-building"></i>&nbsp;{{ __('Floor') }}
                                                                </div>
                                                                <div class="fw-bold">
                                                                    {{ $order->floor }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">
                                                            <div class="client-order-info">
                                                                <div class="">
                                                                    <i class="bi bi-building"></i>&nbsp;{{ __('Door Bell') }}
                                                                </div>
                                                                <div class="fw-bold">
                                                                    {{ $order->door_bell }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">
                                                            <div class="client-order-info">
                                                                <div class="">
                                                                    <i class="bi bi-card-text"></i>&nbsp;{{ __('Comments') }}
                                                                </div>
                                                                <div class="fw-bold ps-5">
                                                                    {{ $order->instructions }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($order->order_status == 'rejected')
                                <div class="col-md-12 mt-2 mb-2">
                                    <strong>Order Rejection Reason : </strong> {{ $order->reject_reason }}
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="text-start" style="width:60%">{{ __('Item') }}</th>
                                                <th class="text-center">{{ __('Qty.') }}</th>
                                                <th class="text-end">{{ __('Item Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @if(isset($order->order_items) && count($order->order_items) > 0)
                                                @foreach ($order->order_items as $ord_item)
                                                    @php
                                                        $item_dt = itemDetails($ord_item['item_id']);
                                                        $item_image = (isset($item_dt['image']) && !empty($item_dt['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image']) : asset('public/client_images/not-found/no_image_1.jpg');
                                                        $options_array = (isset($ord_item['options']) && !empty($ord_item['options'])) ? unserialize($ord_item['options']) : '';
                                                        if(count($options_array) > 0)
                                                        {
                                                            $options_array = implode(', ',$options_array);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="text-start">
                                                            <div class="d-flex align-items-center">
                                                                <a class="symbol symbol-50px">
                                                                    <span class="symbol-label" style="background-image:url({{ $item_image }});"></span>
                                                                </a>
                                                                <div class="ms-5">
                                                                    <a class="fw-bold" style="color: #7e8299">
                                                                        {{ ($ord_item->item_name) }}
                                                                    </a>
                                                                    @if(!empty($options_array))
                                                                        <div class="fs-7" style="color: #a19e9e;">{{ $options_array }}</div>
                                                                    @else
                                                                        <div class="fs-7" style="color: #a19e9e;"></div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $ord_item['item_qty'] }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ $ord_item['sub_total_text'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="2" class="text-dark fs-5 text-end">
                                                    {{ __('Sub Total') }}
                                                </td>
                                                <td class="text-dark fs-5 text-end">{{ Currency::currency($currency)->format($order->order_subtotal) }}</td>
                                            </tr>

                                            @if($order->discount_per > 0)
                                                <tr>
                                                    <td colspan="2" class="text-dark fs-5 text-end">
                                                        {{ __('Discount') }}
                                                    </td>
                                                    @if($order->discount_type == 'fixed')
                                                        <td class="text-dark fs-5 text-end">- {{ Currency::currency($currency)->format($order->discount_per) }}</td>
                                                    @else
                                                        <td class="text-dark fs-5 text-end">- {{ $order->discount_per }}%</td>
                                                    @endif
                                                </tr>
                                                {{-- <tr>
                                                    <td colspan="3" class="text-dark fs-5 fw-bold text-end">
                                                        {{ Currency::currency($currency)->format($order->discount_value) }}
                                                    </td>
                                                </tr> --}}
                                            @endif

                                            @if($order->cgst > 0 && $order->sgst > 0)
                                                <tr>
                                                    @php
                                                        $gst_amt = $order->cgst + $order->sgst;
                                                        $gst_amt = $order->gst_amount / $gst_amt;
                                                    @endphp
                                                    <td colspan="2" class="text-dark fs-5 text-end">
                                                        {{ __('CGST.') }} ({{ $order->cgst }}%)</td>
                                                    <td class="text-dark fs-5 text-end">+ {{ Currency::currency($currency)->format($order->cgst * $gst_amt) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-dark fs-5 text-end">
                                                        {{ __('SGST.') }} ({{ $order->sgst }}%)
                                                    </td>
                                                    <td class="text-dark fs-5 text-end">+ {{ Currency::currency($currency)->format($order->sgst * $gst_amt) }}</td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td colspan="3" class="text-dark fs-5 fw-bold text-end">
                                                    {{ Currency::currency($currency)->format($order->order_total) }}
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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

    <script type="text/javascript">

        var enablePrint = "{{ $enable_print }}";
        var printFontSize = "{{ $printFontSize }}";

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 4000
        }

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        if(enablePrint == 1)
        {
            JSPM.JSPrintManager.license_url = "{{ route('jspm') }}";
            JSPM.JSPrintManager.auto_reconnect = true;
            JSPM.JSPrintManager.start();
        }

        function printReceipt(ordID)
        {
            if(jspmWSStatus())
            {
                $.ajax({
                    type: "POST",
                    url: "{{ route('order.receipt') }}",
                    data: {
                        "_token":"{{ csrf_token() }}",
                        "order_id" : ordID,
                    },
                    dataType: "JSON",
                    success: function (response)
                    {
                        if(response.success == 1)
                        {
                            if (jspmWSStatus())
                            {
                                $('#print-data').html('');
                                $('#print-data').append(response.data);
                                $('#print-data').show();
                                // $('.ord-rec-body').attr('style','font-size:'+printFontSize+'px; font-family:Arial, sans-serif;');
                                // $('.ord-rec-body-start').attr('style','font-size:43px!important; font-family:Arial;');


                                html2canvas(document.getElementById('print-data'), { scale: 5 }).then(function (canvas)
                                {
                                    //Create a ClientPrintJob
                                    var cpj = new JSPM.ClientPrintJob();

                                    //Set Printer info
                                    var myPrinter = new JSPM.InstalledPrinter($('#default_printer').val());
                                    myPrinter.paperName = $('#printer_paper').val();
                                    myPrinter.trayName = $('#printer_tray').val();
                                    cpj.clientPrinter = myPrinter;

                                    //Set content to print...
                                    var b64Prefix = "data:image/png;base64,";
                                    var imgBase64DataUri = canvas.toDataURL("image/png");
                                    var imgBase64Content = imgBase64DataUri.substring(b64Prefix.length, imgBase64DataUri.length);

                                    var myImageFile = new JSPM.PrintFile(imgBase64Content, JSPM.FileSourceType.Base64, 'invoice.png', 1);

                                    //add file to print job
                                    cpj.files.push(myImageFile);

                                    // Send print job to printer!
                                    cpj.sendToClient();
                                });
                                $('#print-data').hide();
                            }
                        }
                        else
                        {
                            toastr.error(response.message);
                        }
                    }
                });
            }
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

    </script>
@endsection
