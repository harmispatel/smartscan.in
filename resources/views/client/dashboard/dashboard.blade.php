@php
    $primary_code = isset($primary_language_detail['code']) ? $primary_language_detail['code'] : '';
    $primary_name = isset($primary_language_detail['name']) ? $primary_language_detail['name'] : '';

    $name_key = $primary_code."_name";

    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

    // Subscrption ID
    $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);

@endphp

@extends('client.layouts.client-layout')

@section('title', __('Dashboard'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Dashboard') }}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">{{ __('Dashboard') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Dashboard Section --}}
    <section class="section dashboard">
        <div class="row">
            {{-- Errors Message --}}
            @if (session()->has('errors'))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('errors') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <div class="col-md-12">
                <div class="row">
                    <!-- Categories Card -->
                    <div class="col-md-3">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title p-0"><a href="{{ route('categories') }}">{{ __('Categories')}}</a></h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-restaurant-2-line"></i>
                                    </div>
                                    <div class="ps-3">
                                        <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                            - {{ isset($category['total_category']) ? $category['total_category'] : 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($package_permissions['page']) && !empty($package_permissions['page']) && $package_permissions['page'] == 1)
                        <!-- Page Card -->
                        <div class="col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title p-0"><a href="{{ route('categories','page') }}">{{ __('Pages')}}</a></h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-files"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                                - {{ isset($category['total_page']) ? $category['total_page'] : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($package_permissions['link']) && !empty($package_permissions['link']) && $package_permissions['link'] == 1)
                        <!-- Link Card -->
                        <div class="col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title p-0"><a href="{{ route('categories','link') }}">{{ __('Links')}}</a></h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-hash"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                                - {{ isset($category['total_link']) ? $category['total_link'] : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($package_permissions['gallery']) && !empty($package_permissions['gallery']) && $package_permissions['gallery'] == 1)
                        <!-- Galleries Card -->
                        <div class="col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title p-0"><a href="{{ route('categories','gallery') }}">{{ __('Image Galleries')}}</a></h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-images"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                                - {{ isset($category['gallery']) ? $category['gallery'] : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($package_permissions['pdf_file']) && !empty($package_permissions['pdf_file']) && $package_permissions['pdf_file'] == 1)
                        <!-- PDF Card -->
                        <div class="col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title p-0"><a href="{{ route('categories','pdf_page') }}">{{ __('PDF')}}</a></h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                                - {{ isset($category['pdf_page']) ? $category['pdf_page'] : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($package_permissions['check_in']) && !empty($package_permissions['check_in']) && $package_permissions['check_in'] == 1)
                        <!-- Check In Page Card -->
                        <div class="col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title p-0"><a href="{{ route('categories','check_in') }}">{{ __('Check In Pages')}}</a></h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-check-fill"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                                - {{ isset($category['check_in']) ? $category['check_in'] : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Card -->
                    <div class="col-md-3">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title p-0"><a href="{{ route('items') }}">{{ __('Items')}}</a></h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-restaurant-2-line"></i>
                                    </div>
                                    <div class="ps-3">
                                        <span class="text-success pt-1"><i class="bi bi-arrow-up-circle"></i> {{ __('Total')}}
                                            - {{ isset($item['total']) ? $item['total'] : 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Category & Items --}}
            <div class="col-md-12 mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card top-selling overflow-auto">
                            <div class="card-body pb-0">
                                <h5 class="card-title">{{ __('Recent Categories')}}</h5>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Preview')}}</th>
                                            <th scope="col">{{ __('Category')}}</th>
                                            <th scope="col">{{ __('Updated At')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($categories) > 0)
                                            @foreach ($categories as $cat)
                                                @php
                                                    $check_cat_type_permission = checkCatTypePermission($cat->category_type,$shop_id);
                                                    $cat_image = isset($cat->categoryImages[0]['image']) ? $cat->categoryImages[0]['image'] : '';
                                                @endphp
                                                @if($check_cat_type_permission == 1)
                                                    <tr>
                                                        <th scope="row">
                                                            @if($cat->category_type == 'page' || $cat->category_type == 'gallery' || $cat->category_type == 'link' || $cat->category_type == 'check_in' || $cat->category_type == 'parent_category' || $cat->category_type == 'pdf_page')
                                                                @if(!empty($cat->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat->cover))
                                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat['cover']) }}" width="35">
                                                                @else
                                                                    <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="35">
                                                                @endif
                                                            @else
                                                                @if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
                                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image) }}" width="35">
                                                                @else
                                                                    <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="35">
                                                                @endif
                                                            @endif
                                                        </th>
                                                        <td>
                                                            @if($cat['parent_category'] == 0 && empty($cat['parent_id']))
                                                                <a href="{{ route('categories',$cat['category_type']) }}">{{ $cat->$name_key }}</a>
                                                            @elseif ($cat['category_type'] == 'product_category' && !empty($cat['parent_id']))
                                                                <a href="{{ route('categories',$cat['parent_id']) }}">{{ $cat->$name_key }}</a>
                                                            @else
                                                                <a href="{{ route('categories') }}">{{ $cat->$name_key }}</a>
                                                            @endif
                                                        </td>
                                                        <td>{{ date('d-m-Y h:i:s',strtotime($cat->updated_at)) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <th scope="row" colspan="3">{{ __('Categories Not Found!')}}</th>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card top-selling overflow-auto">
                            <div class="card-body pb-0">
                                <h5 class="card-title">{{ __('Recent Items')}}</h5>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Preview')}}</th>
                                            <th scope="col">{{ __('Category')}}</th>
                                            <th scope="col">{{ __('Item')}}</th>
                                            <th scope="col">{{ __('Updated At')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($items) > 0)
                                            @foreach ($items as $val)
                                                <tr>
                                                    <th scope="row">
                                                        @if(!empty($val['image']))
                                                            <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$val['image']) }}" width="35" height="35">
                                                        @else
                                                            <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" width="35" height="35">
                                                        @endif
                                                    </th>
                                                    <td>{{ isset($val->category[$name_key]) ? $val->category[$name_key] :  "" }}</td>
                                                    <td>
                                                        <a href="{{ route('items',$val['category_id']) }}">{{ $val->$name_key }}</a>
                                                    </td>
                                                    <td>{{ date('d-m-Y h:i:s',strtotime($val->updated_at)) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <th scope="row" colspan="4">{{ __('Items Not Found!')}}</th>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
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

    </script>

@endsection
