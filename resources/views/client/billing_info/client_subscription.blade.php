@extends('client.layouts.client-layout')

@section('title',__('Subscription'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Subscription')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Subscription')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">

        <div class="row justify-content-center">

            <div class="col-md-10 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <h3>{{ __('Current Plan')}}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('Business Name')}}</th>
                                        <th>{{ __('Plan')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Remainig Days')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            {{ isset(Auth::user()->hasOneShop->shop['name']) ? Auth::user()->hasOneShop->shop['name'] : '' }}
                                        </td>
                                        <td>
                                            {{ isset(Auth::user()->hasOneSubscription->subscription['name']) ? Auth::user()->hasOneSubscription->subscription['name'] : '' }}
                                        </td>
                                        <td>
                                            @php
                                                $sub_status = (isset(Auth::user()->hasOneSubscription->subscription['status']) && Auth::user()->hasOneSubscription->subscription['status'] == 1) ? 'active' : 'nonactive';
                                            @endphp
                                            @if($sub_status == 'active')
                                                <span class="badge bg-success">{{ __('Active')}}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('NonActive')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                if($expire_date > 60)
                                                {
                                                    $color = 'success';
                                                }
                                                else {
                                                    $color = 'danger';
                                                }
                                            @endphp
                                            <strong class="text-{{ $color }}">Your Subscription will Expire In {{ $expire_date }} Days.</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-10 mt-3">
                <div class="row">
                    <div class="col-md-4">
                        <h4>{{ __('Payment Methods') }}</h4>
                        <p>{{ __('Select Your Payment Method') }}</p>
                    </div>
                    <div class="col-md-8">
                        <p>{{ __('For bank transfer subscriptions contact') }} <a href="mailto:harmistechnology@gmail.com">harmistechnology@gmail.com</a></p>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection


{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

    </script>

@endsection
