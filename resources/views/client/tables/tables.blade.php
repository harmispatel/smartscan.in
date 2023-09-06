@php
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Shop Tables'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Shop Tables')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Shop Tables')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('shop.tables.create') }}" class="btn btn-sm btn-primary me-2">
                    <i class="bi bi-plus-lg"></i>
                </a>
                @if(count($shop_tables) > 0)
                    <a href="{{ route('shop.tables.printqr') }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fa fa-print"></i>
                    </a>
                @else
                    <button class="btn btn-sm btn-primary" disabled>
                        <i class="fa fa-print"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Shop Tables Section --}}
    <section class="section dashboard">
        <div class="row">

            {{-- Shop Tables Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="shopTables">
                                <thead>
                                    <tr>
                                        {{-- <th>{{ __('Id')}}</th> --}}
                                        <th>{{ __('Table No.')}}</th>
                                        <th>{{ __('Qr Code')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shop_tables as $shop_table)
                                        <tr>
                                            {{-- <td>{{ $shop_table->id }}</td> --}}
                                            <td>{{ $shop_table->table_no }}</td>
                                            <td>
                                                @if(!empty($shop_table->qr_code) && file_exists('public/client_uploads/shops/'.$shop_slug.'/tables/'.$shop_table->qr_code))
                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/tables/'.$shop_table->qr_code) }}" width="70">
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                $status = $shop_table->status;
                                                $checked = ($status == 1) ? 'checked' : '';
                                                $checkVal = ($status == 1) ? 0 : 1;
                                            @endphp
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" onchange="changeStatus({{ $checkVal }},{{ $shop_table->id }})" id="statusBtn" {{ $checked }}>
                                            </div>
                                            </td>
                                            <td>
                                                <a href="{{ asset('public/client_uploads/shops/'.$shop_slug.'/tables/'.$shop_table->qr_code) }}" class="btn btn-sm btn-primary" download=""><i class="bi bi-download"></i></a>
                                                <a onclick="deleteShopTable({{ $shop_table->id }})" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
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

        $('#shopTables').DataTable();

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            timeOut: 4000
        }

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        // Function for Change Status of Client
        function changeStatus(status, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('shop.tables.status') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'status': status,
                    'id': id
                },
                dataType: 'JSON',
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        toastr.success("Status has been Changed SuccessFully");
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    }
                    else
                    {
                        toastr.error("Internal Serve Errors");
                        location.reload();
                    }
                }
            });
        }

        // Function for Delete Shop Table
        function deleteShopTable(tableID)
        {
            swal({
                title: "Are you sure You want to Delete It ?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelShopTable) =>
            {
                if (willDelShopTable)
                {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("shop.tables.destroy") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            'id': tableID,
                        },
                        dataType: 'JSON',
                        success: function(response)
                        {
                            if (response.success == 1)
                            {
                                swal(response.message, {
                                    icon: "success",
                                });
                                setTimeout(() => {
                                    location.reload();
                                }, 1200);
                            }
                            else
                            {
                                toastr.error(response.message);
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

    </script>
@endsection
