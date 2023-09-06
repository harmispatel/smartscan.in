@extends('admin.layouts.admin-layout')

@section('title', __('Subscriptions'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Subscriptions')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Subscriptions')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('subscriptions.add') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Subscriptions Section --}}
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

            {{-- Subscriptions Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="subscriptionsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        <th>{{ __('Name')}}</th>
                                        <th>{{ __('Icon')}}</th>
                                        <th>{{ __('Price')}}</th>
                                        <th>{{ __('Duration')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($subscriptions as $subscription)
                                        <tr>
                                            <td>{{ $subscription->id }}</td>
                                            <td>{{ $subscription->name }}</td>
                                            <td>
                                                @if(!empty($subscription->icon) && file_exists('public/admin_uploads/subscriptions/'.$subscription->icon))
                                                    <img src="{{ asset('public/admin_uploads/subscriptions/'.$subscription->icon) }}" width="60" style="border: 2px solid gray;" class="rounded-circle">
                                                @else
                                                    <img src="{{ asset('public/admin_images/not-found/not-found4.png') }}" width="60" style="border: 2px solid gray;" class="rounded-circle">
                                                @endif
                                            </td>
                                            <td>€ {{ $subscription->price }}</td>
                                            <td>{{ $subscription->duration }} Months</td>
                                            <td>
                                                @if($subscription->status == 1)
                                                    <span class="badge bg-success">{{ __('Active')}}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('InActive')}}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('subscriptions.edit',$subscription->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a onclick="deleteSubscription({{ $subscription->id }})" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">{{ __('Subscriptions Not Found!')}}</td>
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

        // Function for Delete Subscription
        function deleteSubscription(subscriptionID)
        {
            swal({
                title: "Are you sure You want to Delete It ?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelSubscription) =>
            {
                if (willDelSubscription)
                {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("subscriptions.destroy") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            'id': subscriptionID,
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
