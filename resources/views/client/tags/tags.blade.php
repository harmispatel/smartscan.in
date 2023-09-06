@php
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : "";
    $primary_lang_details = clientLanguageSettings($shop_id);

    $language = getLangDetails(isset($primary_lang_details['primary_language']) ? $primary_lang_details['primary_language'] : '');
    $language_code = isset($language['code']) ? $language['code'] : '';
    $name_key = $language_code."_name";
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Tags'))

@section('content')

    {{-- Tags Edit Modal --}}
    <div class="modal fade" id="editTagModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTagModalLabel">{{ __('Edit Tag')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="tag_edit_div">
                </div>
                <div class="modal-footer">
                    <a class="btn btn-sm btn-success" onclick="updateTag()">{{ __('Update') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tags Add Modal --}}
    <div class="modal fade" id="addTagModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addTagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTagModalLabel">{{ __('Add Tag')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addTagForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="tag_name" class="form-label">{{ __('Tag Name') }}</label>
                                    <input type="text" name="tag_name" id="tag_name" class="form-control" placeholder="Enter Tag Name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-success" id="saveTag" onclick="saveTag()">{{ __('Save') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Tags')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active"><a>{{ __('Tags')}}</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

     {{-- Tags Section --}}
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

            <div class="col-md-12 mb-3 text-end">
                <a class="btn btn-sm btn-primary" id="NewTagBtn" data-bs-toggle="modal"  data-bs-target="#addTagModal"><i class="fa fa-plus"></i></a>
            </div>

            {{-- Tags Card --}}
            <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped w-100" id="tagsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Id')}}</th>
                                    <th>{{ __('Name')}}</th>
                                    <th>{{ __('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tags as $tag)
                                    @php
                                        $tag_name = (isset($tag->$name_key) && !empty($tag->$name_key)) ? $tag->$name_key : "";
                                    @endphp
                                    <tr>
                                        <td>{{ $tag->id }}</td>
                                        <td>{{ $tag_name }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" onclick="editTag({{ $tag->id }})">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="3">
                                            {{ __('Tags Not Found !')}}
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


        // Reset Modal & Form
        $('#NewTagBtn').on('click',function(){

            // Reset NewCategoryForm
            $('#addTagForm').trigger('reset');

            // Remove Validation Class
            $('#addTagForm #tag_name').removeClass('is-invalid');

            // Clear all Toastr Messages
            toastr.clear();
        });

        // Function for Save Tag
        function saveTag()
        {
            const myFormData = new FormData(document.getElementById('addTagForm'));

            // Remove Validation Class
            $('#addTagForm #tag_name').removeClass('is-invalid');

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('tags.store') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#addTagForm').trigger('reset');
                        $('#addTagModal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#addTagForm').trigger('reset');
                        $('#addTagModal').modal('hide');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    // All Validation Errors
                    const validationErrors = (response?.responseJSON?.errors) ? response.responseJSON.errors : '';

                    if (validationErrors != '')
                    {
                        // Name Error
                        var nameError = (validationErrors.tag_name) ? validationErrors.tag_name : '';
                        if (nameError != '')
                        {
                            $('#addTagForm #tag_name').addClass('is-invalid');
                            toastr.error(nameError);
                        }
                    }
                }
            });
        }


        // Function for Edit Tag
        function editTag(tagID)
        {
            // Reset All Form
            $('#editTagModal #tag_edit_div').html('');

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('tags.edit') }}",
                dataType: "JSON",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': tagID,
                },
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        $('#editTagModal #tag_edit_div').html(response.data);
                        $('#editTagModal').modal('show');
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }


        // Update Tag By Language Code
        function updateByCode(next_lang_code)
        {
            const myFormData = new FormData(document.getElementById('editTagForm'));
            myFormData.append('next_lang_code',next_lang_code);

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('tags.update-by-lang') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editTagModal #tag_edit_div').html('');
                        $('#editTagModal #tag_edit_div').html(response.data);
                    }
                    else
                    {
                        $('#editTagModal').modal('hide');
                        $('#editTagModal #tag_edit_div').html('');
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


        // Update Tag
        function updateTag()
        {
            const myFormData = new FormData(document.getElementById('editTagForm'));

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('tags.update') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editTagModal').modal('hide');
                        $('#editTagModal #tag_edit_div').html('');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#editTagModal').modal('hide');
                        $('#editTagModal #tag_edit_div').html('');
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

    </script>

@endsection
