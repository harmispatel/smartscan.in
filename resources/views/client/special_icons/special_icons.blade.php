@php
    $shop_slug = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

    // Subscrption ID
    $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Special Icons'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Special Icons')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Special Icons')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                @if(isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1)
                    <a href="{{ route('special.icons.add') }}" class="btn btn-sm new-amenity btn-primary">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Special Icons Section --}}
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

            {{-- Ingredients Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="ingredientsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        <th>{{ __('Name')}}</th>
                                        <th>{{ __('Icon')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        @if(isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1)
                                            <th>{{ __('Actions')}}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($special_icons as $specialIcon)
                                        @php
                                            $parent_id = (isset($specialIcon->parent_id)) ? $specialIcon->parent_id : NULL;
                                        @endphp
                                        @if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_id != NULL)
                                            <tr>
                                                <td>{{ $specialIcon->id }}</td>
                                                <td>{{ $specialIcon->name }}</td>
                                                <td>
                                                    @if(!empty($specialIcon->icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$specialIcon->icon))
                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$specialIcon->icon) }}" width="40" height="40">
                                                    @else
                                                        <img src="{{ asset('public/admin_images/not-found/not-found4.png') }}" width="40">
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $specialIcon->status;
                                                        $checked = ($status == 1) ? 'checked' : '';
                                                        $checkVal = ($status == 1) ? 0 : 1;
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" onchange="changeStatus({{ $checkVal }},{{ $specialIcon->id }})" id="statusBtn" {{ $checked }}>
                                                    </div>
                                                </td>
                                                @if(isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1)
                                                    <td>
                                                        <a href="{{ route('special.icons.edit',$specialIcon->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="{{ route('special.icons.destroy',$specialIcon->id) }}" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="6">{{ __('Special Icons Not Found!')}}</td>
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

        // Function for Change Status of Client
        function changeStatus(status, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('special.icons.status') }}",
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

    </script>
@endsection
