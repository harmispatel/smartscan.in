@extends('admin.layouts.admin-layout')

@section('title',"Import - Export")

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Import / Export') }}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Import / Export') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Clients Section --}}
    <section class="section dashboard">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>{{ __('Import and Export') }}</h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <a class="btn btn-primary me-2" href="{{ asset('public/csv_demo/demo-type-1.xlsx') }}" download><i class="fa fa-download"></i> {{ __('Demo CSV With Parent') }}</a>
                                        <a class="btn btn-primary me-2" href="{{ asset('public/csv_demo/demo-type-2.xlsx') }}" download><i class="fa fa-download"></i> {{ __('Demo CSV Without Parent') }}</a>
                                        <form id="exportForm" action="{{ route('admin.export.data') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="shop_id" id="shop_id">
                                            <a onclick="exportData()" class="btn btn-secondary"><i class="fa-solid fa-file-export"></i> {{ __('Export CSV') }}</a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('admin.import.data') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shop" class="form-label">{{ __('Client Shop') }}</label>
                                        <select name="shop" id="shop" class="form-select form-control {{ ($errors->has('shop')) ? 'is-invalid' : '' }}">
                                            <option value="">Choose Shop</option>
                                            @if(count($shops) > 0)
                                                @foreach ($shops as $shop)
                                                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @if($errors->has('shop'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('shop') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="import" class="form-label">{{ __('Import Data') }}</label>
                                        <input type="file" name="import" id="import" class="form-control {{ ($errors->has('import')) ? 'is-invalid' : '' }}">
                                        @if($errors->has('import'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('import') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <button class="btn btn-sm btn-success">{{ __('Import') }}</button>
                                        <a class="btn btn-danger btn-sm" onclick="deleteShopData()">{{ __('Delete Category & Items') }}</a>
                                        <a class="btn btn-danger btn-sm" onclick="deleteOrders()">{{ __('Delete Orders') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="col-md-12 mt-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title p-0">
                            <h2>{{ __('Instructions') }}</h2>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul style="list-style: none;padding: 0; margin: 0;" class="text-muted">
                                    <li class="fw-bold">{{ __('Simple Menu Without Sub Categories.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Below PARENT field (Cell B2) insert 0.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Below PARENT NAME field (Cell C2) leave it blank.') }}</li>
                                    <br>

                                    <li class="fw-bold">{{ __('Menu With Sub Categories for HOTELS or LARGE MENUS.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('It is mandatory to insert below TYPE field (Cell A2) The Element Type.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('It Is also mandatory to insert below PARENT (Cell B2) ONLY values of 1 or 0.') }}
                                        <ol class="ms-3" type="a">
                                            <li>{{ __('For Main Category insert 1 (Cell B2).') }}</li>
                                            <li>{{ __('For Sub Category insert 0 (Cell B2).') }}</li>
                                        </ol>
                                    </li>
                                    <br>

                                    <li class="fw-bold">{{ __('Sub Categories') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('It is mandatory to insert the name* of the Primary Category to which it belongs, below PARENT NAME (Cell C2).') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('*English Language (Cell A4 0r B4)') }}</li>
                                    <br>

                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Items will be inserted ONLY in Main Categories & Sub Categories') }}</li>
                                    <br>

                                    <li class="fw-bold">{{ __('For TYPE field, there are only 7 Types of Elements.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Available Element Types:') }}
                                        <ol class="ms-3">
                                            <li>parent_category</li>
                                            <li>product_category</li>
                                            <li>page</li>
                                            <li>link</li>
                                            <li>gallery</li>
                                            <li>check_in</li>
                                            <li>pdf_page</li>
                                        </ol>
                                    </li>
                                    <br>

                                    <li class="fw-bold">{{ __('Other Pages') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('To create a page, insert: page in TYPE field (Cell A2).') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('To create a PDF page, insert: pdf_page in TYPE field (Cell A2).') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('To create an Image Gallery Page, insert: gallery in TYPE field (Cell A2).') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('To create a Check-In Page, insert: check_in in TYPE field (Cell A2).') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('To create a Link Page, insert: link in TYPE field (Cell A2). You can also insert the link bellow LINK field (Cell D2).') }}</li>
                                    <br>

                                    <li class="fw-bold">{{ __('To Import') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Data in Excel file should be in the same format as the DEMO Excel file!') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Before Import Your CSV Click on Download Demo CSV Button and Check DEMO CSV.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('Before Import Catalogue Choose the correct business! Then Delete Old Catalogue if any.') }}</li>
                                    <li><i class="bi bi-arrow-right-circle text-success"></i> {{ __('After Delete Catalogue Choose the correct business! Then Choose CSV File & Click on Import Button.') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('page-js')

    <script type="text/javascript">

        $('#shop').select2();

        // Success Message
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        // Error Message
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif


        // Function for Delete Shop Data
        function deleteShopData()
        {
            var shopID = $('#shop :selected').val();

            if(shopID == '')
            {
                toastr.error('Please Select Shop!');
                return false;
            }
            else
            {

                swal({
                    title: "Are you sure You want to Delete It ?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelData) =>
                {
                    if (willDelData)
                    {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('client.delete.data') }}",
                            data: {
                                "_token" : "{{ csrf_token() }}",
                                "shop_id" : shopID,
                            },
                            dataType: "JSON",
                            success: function (response)
                            {
                                if(response.success == 1)
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
        }



        // Function for Delete Shop Orders
        function deleteOrders()
        {
            var shopID = $('#shop :selected').val();

            if(shopID == '')
            {
                toastr.error('Please Select Shop!');
                return false;
            }
            else
            {

                swal({
                    title: "Are you sure You want to Delete It ?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelData) =>
                {
                    if (willDelData)
                    {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('client.delete.orders') }}",
                            data: {
                                "_token" : "{{ csrf_token() }}",
                                "shop_id" : shopID,
                            },
                            dataType: "JSON",
                            success: function (response)
                            {
                                if(response.success == 1)
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
        }


        // Function for Submit Export Form
        function exportData()
        {
            shopID = $('#shop_id').val();
            if(shopID == '')
            {
                toastr.error("Please Select Shop to Export Data!");
                return false;
            }
            else
            {
                $('#exportForm').submit();
            }
        }

        // Paste Shop ID in Export Form
        $('#shop').on('change',function()
        {
            var shopID = $(this).val();
            $('#shop_id').val(shopID);
        });

    </script>

@endsection
