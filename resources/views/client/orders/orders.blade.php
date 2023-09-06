@php

    $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';

    $shop_settings = getClientSettings($shop_id);

    // Order Settings
    $order_setting = getOrderSettings($shop_id);

    // Optional Fields
    $email_field = (isset($order_setting['email_field']) && $order_setting['email_field'] == 1) ? $order_setting['email_field'] : 0;
    $floor_field = (isset($order_setting['floor_field']) && $order_setting['floor_field'] == 1) ? $order_setting['floor_field'] : 0;
    $door_bell_field = (isset($order_setting['door_bell_field']) && $order_setting['door_bell_field'] == 1) ? $order_setting['door_bell_field'] : 0;
    $full_name_field = (isset($order_setting['full_name_field']) && $order_setting['full_name_field'] == 1) ? $order_setting['full_name_field'] : 0;
    $instructions_field = (isset($order_setting['instructions_field']) && $order_setting['instructions_field'] == 1) ? $order_setting['instructions_field'] : 0;
    $live_address_field = (isset($order_setting['live_address_field']) && $order_setting['live_address_field'] == 1) ? $order_setting['live_address_field'] : 0;

    // Default Printer
    $default_printer = (isset($order_setting['default_printer']) && !empty($order_setting['default_printer'])) ? $order_setting['default_printer'] : 'Microsoft Print to PDF';
    // Printer Paper
    $printer_paper = (isset($order_setting['printer_paper']) && !empty($order_setting['printer_paper'])) ? $order_setting['printer_paper'] : 'A4';
    // Printer Tray
    $printer_tray = (isset($order_setting['printer_tray']) && !empty($order_setting['printer_tray'])) ? $order_setting['printer_tray'] : '';
    // Auto Print
    $auto_print = (isset($order_setting['auto_print']) && !empty($order_setting['auto_print'])) ? $order_setting['auto_print'] : 0;
    // Enable Print
    $enable_print = (isset($order_setting['enable_print']) && !empty($order_setting['enable_print'])) ? $order_setting['enable_print'] : 0;
    // Print Font Size
    $printFontSize = (isset($order_setting['print_font_size']) && !empty($order_setting['print_font_size'])) ? $order_setting['print_font_size'] : 20;

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Orders'))

@section('content')

    <input type="hidden" name="default_printer" id="default_printer" value="{{ $default_printer }}">
    <input type="hidden" name="printer_paper" id="printer_paper" value="{{ $printer_paper }}">
    <input type="hidden" name="printer_tray" id="printer_tray" value="{{ $printer_tray }}">
    <input type="hidden" name="auto_print" id="auto_print" value="{{ $auto_print }}">

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Orders')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Orders') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Orders Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12 mb-3" id="print-data" style="display: none;"></div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body" id="order">
                        @forelse ($orders as $order)
                            @php
                                $discount_type = (isset($order->discount_type) && !empty($order->discount_type)) ? $order->discount_type : 'percentage';
                            @endphp
                            <div class="order">
                                <div class="order-btn d-flex align-items-center justify-content-end">
                                    <div class="d-flex align-items-center flex-wrap">{{ __('Estimated time of arrival') }} <input type="number" name="estimated_time" onchange="changeEstimatedTime(this)" id="estimated_time" value="{{ $order->estimated_time }}" class="form-control mx-1 estimated_time" style="width: 100px!important" ord-id="{{ $order->id }}" {{ ($order->order_status == 'accepted') ? 'disabled' : '' }}> {{ __('Minutes') }}.
                                    </div>
                                    @if($order->order_status == 'pending')
                                        <a class="btn btn-sm btn-primary ms-3" onclick="acceptOrder({{ $order->id }})"><i class="bi bi-check-circle" data-bs-toggle="tooltip" title="Accept"></i> {{ __('Accept') }}</a>
                                        <a class="btn btn-sm btn-danger ms-3" onclick="rejectOrder({{ $order->id }})"><i class="bi bi-x-circle" data-bs-toggle="tooltip" title="Reject"></i> {{ __('Reject') }}</a>
                                    @elseif($order->order_status == 'accepted')
                                        <a class="btn btn-sm btn-success ms-3" onclick="finalizedOrder({{ $order->id }})"><i class="bi bi-check-circle" data-bs-toggle="tooltip" title="Complete"></i> {{ __('Finalize') }}</a>
                                    @endif

                                    @if($enable_print == 1)
                                        <a class="btn btn-sm btn-primary ms-3" onclick="printReceipt({{ $order->id }})"><i class="bi bi-printer"></i> Print</a>
                                    @endif
                                </div>
                                <div class="order-info">
                                    <ul>
                                        <li><strong>#{{ $order->id }}</strong></li>
                                        <li><strong>{{ __('Order Date') }} : </strong>{{ date('d-m-Y h:i:s',strtotime($order->created_at)) }}</li>
                                        <li><strong>{{ __('Order Type') }} : </strong>{{ $order->checkout_type }}</li>
                                        <li><strong>{{ __('Payment Method') }} : </strong>{{ $order->payment_method }}</li>
                                        @if($order->checkout_type == 'takeaway')
                                            <li><strong>{{ __('Customer') }} : </strong> {{ $order->firstname }} {{ $order->lastname }}</li>
                                            <li><strong>{{ __('Telephone') }} : </strong> {{ $order->phone }}</li>

                                            @if($email_field == 1)
                                                <li><strong>{{ __('Email') }} : </strong> {{ $order->email }}</li>
                                            @endif
                                        @elseif($order->checkout_type == 'table_service')
                                            <li><strong>{{ __('Table No.') }} : </strong> {{ $order->table }}</li>
                                        @elseif($order->checkout_type == 'room_delivery')
                                            <li><strong>{{ __('Customer') }} : </strong> {{ $order->firstname }} {{ $order->lastname }}</li>
                                            <li><strong>{{ __('Room No.') }} : </strong> {{ $order->room }}</li>
                                            @if(!empty($order->delivery_time ))
                                                <li><strong>{{ __('Delivery Time') }} : </strong> {{ $order->delivery_time }}</li>
                                            @endif
                                        @elseif($order->checkout_type == 'delivery')
                                            <li><strong>{{ __('Customer') }} : </strong> {{ $order->firstname }} {{ $order->lastname }}</li>
                                            <li><strong>{{ __('Telephone') }} : </strong> {{ $order->phone }}</li>

                                            @if($email_field == 1)
                                                <li><strong>{{ __('Email') }} : </strong> {{ $order->email }}</li>
                                            @endif

                                            <li><strong>{{ __('Address') }} : </strong> {{ $order->address }}</li>

                                            @if($floor_field == 1)
                                                <li><strong>{{ __('Floor') }} : </strong> {{ $order->floor }}</li>
                                            @endif

                                            @if($door_bell_field == 1)
                                                <li><strong>{{ __('Door Bell') }} : </strong> {{ $order->door_bell }}</li>
                                            @endif

                                            @if($live_address_field == 1)
                                                <li><strong>{{ __('Google Map') }} : </strong> <a href="https://maps.google.com?q={{ $order->address }}" target="_blank">Address Link</a></li>
                                            @endif

                                            @if($instructions_field == 1)
                                                <li><strong>{{ __('Comments') }} : </strong> {{ $order->instructions }}</li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                                <hr>
                                <div class="order-info mt-2">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <table class="table">
                                                <tr>
                                                    <td><b>{{ __('Sub Total') }}</b></td>
                                                    <td class="text-end">{{ Currency::currency($currency)->format($order->order_subtotal) }}</td>
                                                </tr>

                                                @if($order->discount_per > 0)
                                                    <tr>
                                                        <td><b>{{ __('Discount') }}</b></td>
                                                        @if($discount_type == 'fixed')
                                                            <td class="text-end">- {{ Currency::currency($currency)->format($order->discount_per) }}</td>
                                                        @else
                                                            <td class="text-end">- {{ $order->discount_per }}%</td>
                                                        @endif
                                                    </tr>
                                                @endif

                                                @if($order->cgst > 0 && $order->sgst > 0)
                                                    <tr>
                                                        @php
                                                            $gst_amt = $order->cgst + $order->sgst;
                                                            $gst_amt = $order->gst_amount / $gst_amt;
                                                        @endphp
                                                        <td><b>{{ __('CGST.') }} ({{ $order->cgst }}%)</b></td>
                                                        <td class="text-end">+ {{ Currency::currency($currency)->format($order->cgst * $gst_amt) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>{{ __('SGST.') }} ({{ $order->sgst }}%)</b></td>
                                                        <td class="text-end">+ {{ Currency::currency($currency)->format($order->sgst * $gst_amt) }}</td>
                                                    </tr>
                                                @endif

                                                <tr class="text-end">
                                                    <td colspan="2"><b>{{ Currency::currency($currency)->format($order->order_total) }}</b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="order-items">
                                    <div class="row">
                                        @if(count($order->order_items) > 0)
                                            <div class="col-md-8">
                                                <table class="table">
                                                    @foreach ($order->order_items as $ord_item)
                                                        <tr>
                                                            @php
                                                                $sub_total = ( $ord_item['sub_total'] / $ord_item['item_qty']);
                                                                $option = unserialize($ord_item['options']);
                                                            @endphp
                                                            <td>
                                                                <b>{{ $ord_item['item_qty'] }} x {{ $ord_item['item_name'] }}</b>
                                                                @if(!empty($option))
                                                                    <br> {{ implode(', ',$option) }}
                                                                @endif
                                                            </td>
                                                            <td width="25%" class="text-end">{{ Currency::currency($currency)->format($sub_total) }}</td>
                                                            <td width="25%" class="text-end">{{ $ord_item['sub_total_text'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h3>Orders Not Available</h3>
                                </div>
                            </div>
                        @endforelse
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

                                    var myImageFile = new JSPM.PrintFile(imgBase64Content, JSPM.FileSourceType.Base64, 'ORD-INVOICE.PNG', 1);

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


        // Change Estimated Time
        function changeEstimatedTime(ele)
        {
            var time = $(ele).val();
            var ord_id = $(ele).attr('ord-id');

            $.ajax({
                type: "POST",
                url: "{{ route('change.order.estimate') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    'estimate_time' : time,
                    'order_id' : ord_id,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success != 1)
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1300);
                    }
                }
            });
        }


        // Function for Accept Order
        function acceptOrder(ordID)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('accept.order') }}",
                data: {
                    "_token":"{{ csrf_token() }}",
                    "order_id":ordID,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        var auto_print = $('#auto_print').val();

                        toastr.success(response.message);

                        if(auto_print == 1 && enablePrint == 1)
                        {
                            printReceipt(ordID);
                            setTimeout(() => {
                                location.reload();
                            }, 2500);
                        }
                        else
                        {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }

                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1300);
                    }
                }
            });
        }


        // Function for Reject Order
        function rejectOrder(ordID)
        {

            swal({
                title: "Enter Reason for Reject Order.",
                icon: "info",
                buttons: true,
                dangerMode: true,
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Enter Your Reason",
                        type: "text",
                    },
                },
                closeOnClickOutside: false,
            })
            .then((reasonResponse) =>
            {
                if (reasonResponse == '')
                {
                    swal("Please Enter Reason to Reject Order!", {
                        icon: "info",
                    });
                    return false;
                }
                else if(reasonResponse == null)
                {
                    return false;
                }
                else
                {
                    swal({
                        title: "Are you sure You want to Reject this Order ?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willRejectThisOrder) =>
                    {
                        if (willRejectThisOrder)
                        {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('reject.order') }}",
                                data: {
                                    "_token":"{{ csrf_token() }}",
                                    "order_id":ordID,
                                    "reject_reason":reasonResponse,
                                },
                                dataType: "JSON",
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
                                        }, 1300);
                                    }
                                }
                            });
                        }
                        else
                        {
                            swal("Cancelled", "", "error");
                        }
                    });
                }
            });
        }


        // Function for Finalized Order
        function finalizedOrder(ordID)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('finalized.order') }}",
                data: {
                    "_token":"{{ csrf_token() }}",
                    "order_id":ordID,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1300);

                    }
                    else
                    {
                        toastr.error(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1300);
                    }
                }
            });
        }

        // Function for get New Orders
        setInterval(() =>
        {
            getNewOrders();
        }, 10000);


        function getNewOrders()
        {
            $.ajax({
                type: "GET",
                url: "{{ route('new.orders') }}",
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#order').html('');
                        $('#order').append(response.data);
                    }
                }
            });
        }

    </script>
@endsection
