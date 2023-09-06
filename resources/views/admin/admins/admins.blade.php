@extends('admin.layouts.admin-layout')

@section('title',__('Admins'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Admins')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Admins')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('admins.add') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Admins Section --}}
    <section class="section dashboard">
        <div class="row">
            {{-- Error Message Section --}}
            @if (session()->has('error'))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- Success Message Section --}}
            @if (session()->has('success'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- Admins Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="clientsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        <th>{{ __('Name')}}</th>
                                        <th>{{ __('email')}}</th>
                                        <th>{{ __('Profile')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if(!empty($user->image))
                                                    <img src="{{ $user->image }}" alt="Profile" width="60">
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $status = $user->status;
                                                    $checked = ($status == 1) ? 'checked' : '';
                                                    $checkVal = ($status == 1) ? 0 : 1;
                                                @endphp
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" onchange="changeStatus({{ $checkVal }},{{ $user->id }})" id="statusBtn" {{ $checked }}>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admins.edit',$user->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($user->id === 1)
                                                    <button class="btn btn-sm btn-danger" disabled>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('admins.destroy',$user->id) }}" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="6">{{ __('Admins Not Found!')}}</td>
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

        // Function for Change Status of Admin
        function changeStatus(status, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('clients.status') }}",
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
                        toastr.error("Internal Server Error!");
                        locattion.reload();
                    }
                }
            });
        }


    </script>
@endsection
