@extends('client.layouts.client-layout')

@section('title', __('Order Attributes'))

@section('content')

    {{-- Add Modal --}}
    <div class="modal fade" id="addOptionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOptionModalLabel">{{ __('Add Attribute')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addOptionForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="title" class="form-label">{{ __('Title') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="title" id="title" class="form-control" placeholder="Enter Attribute Title">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="multiple_selection" class="form-label">{{ __('Multiple Selection') }}</label>
                            </div>
                            <div class="col-md-9">
                                <label class="switch ms-2">
                                    <input type="checkbox" id="multiple_selection" name="multiple_selection" value="1">
                                    <span class="slider round">
                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="enabled_price" class="form-label">{{ __('Enabled Prices') }}</label>
                            </div>
                            <div class="col-md-9">
                                <label class="switch ms-2">
                                    <input type="checkbox" id="enabled_price" name="enabled_price" value="1" checked onchange="togglePrices('addOptionModal')">
                                    <span class="slider round">
                                        <i class="fa-solid fa-circle-check check_icon"></i>
                                        <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Order Attributes') }}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-12 mb-2" id="option_sec">
                                    </div>
                                    <div class="col-md-12">
                                        <a class="btn btn-sm btn-primary" onclick="addOption('addOptionForm')">{{ __('Add Option') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" id="saveOption" onclick="saveOption()">{{ __('Save')}}</a>
                </div>
            </div>
        </div>
    </div>


    {{-- Edit Modal --}}
    <div class="modal fade" id="editOptionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOptionModalLabel">{{ __('Edit Attribute')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="option_edit_div">

                </div>
                <div class="modal-footer">
                    <a class="btn btn-sm btn-success" onclick="updateOption()">{{ __('Update') }}</a>
                </div>
            </div>
        </div>
    </div>


    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Order Attributes')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Order Attributes') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Options Section --}}
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

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-3 p-0">
                            <button data-bs-toggle="modal" id="addOptBtn" data-bs-target="#addOptionModal" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('Create') }}</button>
                        </div>
                        <div class="option_main">
                            <ul>
                                @forelse ($options as $option)
                                    <li>
                                        <div class="option_box">
                                            <div class="option_title">
                                                <a class="text-primary" onclick="editOption({{ $option->id }})">{{ $option->title }}</a>
                                                <p class="m-0">
                                                    @if(isset($option['optionPrices']) && count($option['optionPrices']) > 0)
                                                        @foreach ($option['optionPrices'] as $opt_price)
                                                            {{ $opt_price->name }},
                                                        @endforeach
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <a onclick="editOption({{ $option->id }})" class="btn btn-sm btn-primary opt_edit_btn"><i class="bi bi-pencil"></i></a>
                                                <a onclick="deleteOption({{ $option->id }})" class="btn btn-sm btn-danger opt_del_btn"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center">Records Not Found!</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection


{{-- Custom Script --}}
@section('page-js')
    <script type="text/javascript">

        // Reset AddItem Modal & Form
        $('#addOptBtn').on('click',function()
        {
            // Reset addItemForm
            $('#addItemForm').trigger('reset');

            // Clear all Toastr Messages
            toastr.clear();

            // Clear Options Element
            $('#option_sec').html('');
        });


        // Function for add New Option
        function addOption(formType)
        {
            var count = $('#'+formType+' #option_sec').children('.option').length + 1;
            var html = "";

            html += '<div class="row mb-2 option" id="option_'+count+'">';
                html += '<div class="col-md-6">';
                    html += '<input type="text" name="option[name][]" class="form-control" placeholder="Attribute Name">';
                html += '</div>';
                html += '<div class="col-md-4">';
                    html += '<input type="number" name="option[price][]" class="form-control opt-price" placeholder="Attribute Price" value="0.00">';
                html += '</div>';
                html += '<div class="col-md-2">';
                    html += '<a class="btn btn-sm btn-danger" onclick="$(\'#option_'+count+'\').remove()"><i class="fa fa-trash"></i></a>';
                html += '</div>';
            html += '</div>';

            $('#'+formType+' #option_sec').append(html);
        }


        // Function for Save Option
        function saveOption()
        {
            const myFormData = new FormData(document.getElementById('addOptionForm'));

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('options.store') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#addOptionForm').trigger('reset');
                        $('#addOptionModal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#addOptionForm').trigger('reset');
                        $('#addOptionModal').modal('hide');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    $.each(response.responseJSON.errors, function (i, error) {
                        toastr.error(error);
                    });
                }
            });
        }


        // Function for Delete Options
        function deleteOption(optID)
        {
            swal({
                title: "Are you sure You want to Delete It ?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelOpt) =>
            {
                if (willDelOpt)
                {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("options.delete") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            'id': optID,
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
                                }, 1300);
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


        // Function for Edit Options
        function editOption(optID)
        {
            // Reset All Form
            $('#editOptionModal #option_edit_div').html('');

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('options.edit') }}",
                dataType: "JSON",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': optID,
                },
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        $('#editOptionModal #option_edit_div').html(response.data);
                        $('#editOptionModal').modal('show');
                        if(response.enable_price == 'checked')
                        {
                            $('.opt-price').show();
                        }
                        else
                        {
                            $('.opt-price').hide();
                        }
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }


        // Update Options By Language Code
        function updateByCode(next_lang_code)
        {
            const myFormData = new FormData(document.getElementById('editOptionForm'));
            myFormData.append('next_lang_code',next_lang_code);

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('options.update-by-lang') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editOptionModal #option_edit_div').html('');
                        $('#editOptionModal #option_edit_div').html(response.data);

                        if(response.enable_price == 'checked')
                        {
                            $('.opt-price').show();
                        }
                        else
                        {
                            $('.opt-price').hide();
                        }
                    }
                    else
                    {
                        $('#editOptionModal').modal('hide');
                        $('#editOptionModal #option_edit_div').html('');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    $.each(response.responseJSON.errors, function (i, error) {
                        toastr.error(error);
                    });
                }
            });
        }

        // Update Option
        function updateOption()
        {
            const myFormData = new FormData(document.getElementById('editOptionForm'));

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('options.update') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editOptionModal').modal('hide');
                        $('#editOptionModal #option_edit_div').html('');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#editOptionModal').modal('hide');
                        $('#editOptionModal #option_edit_div').html('');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    $.each(response.responseJSON.errors, function (i, error) {
                        toastr.error(error);
                    });
                }
            });

        }


        // Delete Option Price
        function deleteOptionPrice(priceID,optKey)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('options.price.delete') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    "price_id" : priceID,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editOptionForm #option_'+optKey).remove();
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }


        // Show-Hide Pre Selection
        function togglePreSelection(){
            var isCheck = $('#editOptionForm #multiple_selection').is(":checked");
            if(isCheck == false)
            {
                $('#editOptionForm .pre-select').hide();
            }
            else
            {
                $('#editOptionForm .pre-select').show();
            }
        }


        // Show-Hide Prices
        function togglePrices(ModalName)
        {
            const isChecked = $('#'+ModalName+' #enabled_price').prop('checked');
            if(isChecked == true)
            {
                $('.opt-price').show();
            }
            else
            {
                $('.opt-price').hide();
            }
        }

    </script>
@endsection
