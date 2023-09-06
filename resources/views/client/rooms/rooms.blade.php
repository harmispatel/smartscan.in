@php
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Rooms'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Rooms')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Rooms')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('rooms.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </a>
                @if(count($rooms) > 0)
                    <a href="{{ route('rooms.printqr') }}" target="_blank" class="btn btn-sm btn-primary">
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

    {{-- Rooms Section --}}
    <section class="section dashboard">
        <div class="row">

            {{-- Rooms Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="rooms">
                                <thead>
                                    <tr>
                                        {{-- <th>{{ __('Id')}}</th> --}}
                                        <th>{{ __('Room No.')}}</th>
                                        <th>{{ __('Qr Code')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rooms as $room)
                                        <tr>
                                            {{-- <td>{{ $room->id }}</td> --}}
                                            <td>{{ $room->room_no }}</td>
                                            <td>
                                                @if(!empty($room->qr_code) && file_exists('public/client_uploads/shops/'.$shop_slug.'/rooms/'.$room->qr_code))
                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/rooms/'.$room->qr_code) }}" width="100">
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                $status = $room->status;
                                                $checked = ($status == 1) ? 'checked' : '';
                                                $checkVal = ($status == 1) ? 0 : 1;
                                            @endphp
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" onchange="changeStatus({{ $checkVal }},{{ $room->id }})" id="statusBtn" {{ $checked }}>
                                            </div>
                                            </td>
                                            <td>
                                                <a href="{{ asset('public/client_uploads/shops/'.$shop_slug.'/rooms/'.$room->qr_code) }}" class="btn btn-sm btn-primary" download=""><i class="bi bi-download"></i></a>
                                                <a onclick="deleteRoom({{ $room->id }})" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="5">
                                                Records Not Found!
                                            </td>
                                        </tr>
                                    @endforelse
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

        $('#rooms').DataTable();

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

        // Function for Change Status of Room
        function changeStatus(status, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('rooms.status') }}",
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

        // Function for Delete Room
        function deleteRoom(roomID)
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
                        url: '{{ route("rooms.destroy") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            'id': roomID,
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
