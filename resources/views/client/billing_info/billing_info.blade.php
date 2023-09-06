@extends('client.layouts.client-layout')

@section('title',__('Billing Info'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Billing Information')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Billing Information')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">

        <div class="row justify-content-center">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body position-relative">
                        <a href="{{ route('billing.info.edit') }}" class="btn btn-primary btn-sm billing_edit_btn"><i class="fa fa-edit"></i></a>
                        <div class="card-title text-center">
                            <h3>{{ __('Billing Information') }}</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th>{{ __('First Name') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->firstname }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Last Name') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->lastname }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Email') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Mobile No.') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->mobile }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Telephone') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->telephone }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Company Name') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->company }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Address') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->address }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('City') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->city }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Country') }}</th>
                                                <td>:</td>
                                                <td>{{ isset($user->hasOneCountry['name']) ? $user->hasOneCountry['name'] : '' }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Zip') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->zipcode }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('GST No.') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->gst_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('SGST.') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->sgst }} %</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('CGST.') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->cgst }} %</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('VAT ID') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->vat_id }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('G.E.M.I ID') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->gemi_id }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Tax Office') }}</th>
                                                <td>:</td>
                                                <td>{{ $user->tax_office }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

    </script>

@endsection
