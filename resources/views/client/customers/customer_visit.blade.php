@php
    $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';

    $shop_settings = getClientSettings($shop_id);

    // Shop Currency
    $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Visited Customers'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Visited Customers')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Visited Customers') }}</li>
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
                    <a class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Clear Filters." href="{{ route('customers.visit') }}"><i class="bi bi-trash"></i></a>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('customers.visit') }}" method="POST" id="filterForm">
                            @csrf
                            <input type="hidden" name="start_date" id="start_date" value="{{ $StartDate }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $EndDate }}">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <input type="text" name="custom_dates" class="form-control" id="custom_dates" />
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped" id="customer_visits">
                                <thead>
                                    <tr>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Mobile No.') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($customer_visits) > 0)
                                        @foreach ($customer_visits as $customer)
                                            <tr>
                                                <td>{{ $customer->name }}</td>
                                                <td>{{ $customer->mobile_no }}</td>
                                                <td>{{ date('d-m-Y h:i:s',strtotime($customer->created_at)) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
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

        $('#customer_visits').DataTable({
            order: [[2, 'desc']]
        });

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

    </script>
@endsection
