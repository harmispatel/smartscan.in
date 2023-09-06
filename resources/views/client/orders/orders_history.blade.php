@php
    $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';

    $shop_settings = getClientSettings($shop_id);

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Orders History'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Orders History')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Orders History') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Orders Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12 mb-2">
                <div class="text-end">
                    <a class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Clear Filters." href="{{ route('client.orders.history') }}"><i class="bi bi-trash"></i></a>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('client.orders.history') }}" method="POST" id="filterForm">
                            @csrf
                            <input type="hidden" name="start_date" id="start_date" value="{{ $StartDate }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $EndDate }}">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select name="filter_by_day" id="filter_by_day" class="form-select">
                                        <option value="">-- Filter by Day --</option>
                                        <option value="today" {{ ($day_filter == 'today') ? 'selected' : '' }}>Today</option>
                                        <option value="this_week" {{ ($day_filter == 'this_week') ? 'selected' : '' }}>This Week</option>
                                        <option value="last_week" {{ ($day_filter == 'last_week') ? 'selected' : '' }}>Last Week</option>
                                        <option value="this_month" {{ ($day_filter == 'this_month') ? 'selected' : '' }}>This Month</option>
                                        <option value="last_month" {{ ($day_filter == 'last_month') ? 'selected' : '' }}>Last Month</option>
                                        <option value="last_six_month" {{ ($day_filter == 'last_six_month') ? 'selected' : '' }}>Last Six Month</option>
                                        <option value="this_year" {{ ($day_filter == 'this_year') ? 'selected' : '' }}>This Year</option>
                                        <option value="last_year" {{ ($day_filter == 'last_year') ? 'selected' : '' }}>Last Year</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="filter_by_status" id="filter_by_status" class="form-select">
                                        <option value="">-- Filter by Status --</option>
                                        <option value="accepted" {{ ($status_filter == 'accepted') ? 'selected' : '' }}>Accepted</option>
                                        <option value="rejected" {{ ($status_filter == 'rejected') ? 'selected' : '' }}>Rejected</option>
                                        <option value="completed" {{ ($status_filter == 'completed') ? 'selected' : '' }}>Completed</option>
                                        <option value="pending" {{ ($status_filter == 'pending') ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="filter_by_payment_method" id="filter_by_payment_method" class="form-select">
                                        <option value="">-- Filter by Payment --</option>
                                        <option value="cash" {{ ($payment_method == 'cash') ? 'selected' : '' }}>Cash</option>
                                        <option value="cash_pos" {{ ($payment_method == 'cash_pos') ? 'selected' : '' }}>Cash POS</option>
                                        {{-- <option value="every_pay" {{ ($payment_method == 'every_pay') ? 'selected' : '' }}>Credit/Debit Card</option> --}}
                                        <option value="paypal" {{ ($payment_method == 'paypal') ? 'selected' : '' }}>PayPal</option>
                                        <option value="upi_payment" {{ ($payment_method == 'upi_payment') ? 'selected' : '' }}>UPI Payment</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="custom_dates" class="form-control" id="custom_dates" />
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped" id="order_history">
                                <thead>
                                    <tr>
                                        <th>{{ __('Order No.') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Mobile No.') }}</th>
                                        <th>{{ __('Total Price') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>
                                                @if($order->order_status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif ($order->order_status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif ($order->order_status == 'accepted')
                                                    <span class="badge bg-primary">Accepted</span>
                                                @elseif ($order->order_status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if((isset($order['firstname']) && !empty($order['firstname'])) || (isset($order['lastname']) && !empty($order['lastname'])))
                                                    {{ $order['firstname'] }} {{ $order['lastname'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($order['phone']) && !empty($order['phone']))
                                                    {{ $order['phone'] }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ Currency::currency($currency)->format($order->order_total) }}
                                            </td>
                                            <td>
                                                {{ date('d-m-Y h:i:s',strtotime($order->created_at)) }}
                                            </td>
                                            <td>
                                                <a href="{{ route('view.order',encrypt($order->id)) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View Order"><i class="bi bi-eye"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="mt-3">
                                <div class="col-md-12">
                                    <h5><strong>{{ $total_text }}</strong> : {{ Currency::currency($currency)->format($total) }}</h5>
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
    <script type="text/javascript">

        const startDate = moment(@json($start_date));
        const endDate = moment(@json($end_date));
        $(function() {
            $('#custom_dates').daterangepicker({
                startDate : startDate,
                endDate : endDate,
            },
            function (start, end, label)
            {
                var start_date = start.format("YYYY-MM-DD");
                var end_date = end.format("YYYY-MM-DD");
                $('#start_date').val(start_date);
                $('#end_date').val(end_date);
                $('#filterForm').submit();
            })
        });

        $('#order_history').DataTable();

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


        // Submit Filter Form
        $('#filter_by_day').on('change',function(){
            $('#start_date').val('');
            $('#end_date').val('');
            $('#filterForm').submit();
        });

        $('#filter_by_payment_method, #filter_by_status').on('change',function(){
            $('#filterForm').submit();
        });

    </script>
@endsection
