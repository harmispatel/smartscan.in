@extends('admin.layouts.admin-layout')

@section('title', __('Food Junctions'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Food Junctions')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Food Junctions')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('food.junctions.create') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Food Junctions Section --}}
    <section class="section dashboard">
        <div class="row">
            {{-- Food Junctions Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="foodJunctionsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        <th>{{ __('Name')}}</th>
                                        <th>{{ __('Url')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('QR Code')}}</th>
                                        <th>{{ __('Logo')}}</th>
                                        <th class="text-center">{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($foodjunctions as $junction)
                                        @php
                                            $new_url = URL::to('/')."/junction/".$junction->junction_slug;
                                        @endphp
                                        <tr>
                                            <td>{{ $junction->id }}</td>
                                            <td>{{ $junction->junction_name }}</td>
                                            <td><a target="_blank" href="{{ route('junction',$junction->junction_slug) }}">{{ $new_url }}</a></td>
                                            <td>
                                                @php
                                                    $status = $junction->status;
                                                    $checked = ($status == 1) ? 'checked' : '';
                                                    $checkVal = ($status == 1) ? 0 : 1;
                                                @endphp
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" onchange="changeStatus({{ $checkVal }},{{ $junction->id }})" id="statusBtn" {{ $checked }}>
                                                </div>
                                            </td>
                                            <td>
                                                <img src="{{ asset('public/admin_uploads/junctions_qr/'.$junction->junction_qr) }}" alt="QR" width="60">
                                            </td>
                                            <td>
                                                @if(!empty($junction->logo) && file_exists('public/admin_uploads/junctions_logo/'.$junction->logo))
                                                    <img src="{{ asset('public/admin_uploads/junctions_logo/'.$junction->logo) }}" width="60">
                                                @else
                                                    <img src="{{ asset('public/admin_images/not-found/not-found4.png') }}" width="60">
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('food.junctions.edit',$junction->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                                <a href="{{ route('food.junctions.destroy',$junction->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">Junctions Not Found !</td>
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

        // Error Messages
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        // Success Messages
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        // Function for Change Status of Client
        function changeStatus(status, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('food.junctions.status') }}",
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
                    }
                    else
                    {
                        toastr.error("Internal Serve Errors");
                        locattion.reload();
                    }
                }
            });
        }

    </script>
@endsection
