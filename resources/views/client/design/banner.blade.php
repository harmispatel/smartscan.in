@php
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

    // Language Settings
    $language_settings = clientLanguageSettings($shop_id);
    $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

    // Language Details
    $language_detail = App\Models\Languages::where('id',$primary_lang_id)->first();
    $lang_code = isset($language_detail->code) ? $language_detail->code : '';

    $description_key = $lang_code."_description";
    $image_key = $lang_code."_image";
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Banners'))

@section('content')

    {{-- Add Modal --}}
    <div class="modal fade" id="addBannerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBannerModalLabel">{{ __('Add Banner')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addBannerForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="image" class="form-label">{{ __('Image') }}</label>
                                <input type="file" name="image" id="image" class="form-control">
                                <code>{{ __('Banner Dimensions (1140*300)') }}</code>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="display" class="form-label">{{ __('Display') }}</label>
                                <select name="display" id="display" class="form-select">
                                    <option value="both">Both</option>
                                    <option value="image">Image</option>
                                    <option value="description">Description</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="display" class="form-label">{{ __('Background Color') }}</label>
                                <input type="color" name="background_color" id="background_color" value="#ffffff" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" id="saveBanner" onclick="saveBanner()">{{ __('Save')}}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editBannerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBannerModalLabel">{{ __('Edit Banner')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="banner_edit_div">

                </div>
                <div class="modal-footer">
                    <a class="btn btn-sm btn-success" onclick="updateBanner()">{{ __('Update') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Banners')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Banners') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

     {{-- Banners Section --}}
     <section class="section dashboard">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-3 p-0">
                            <button data-bs-toggle="modal" id="addBannerBtn" data-bs-target="#addBannerModal" class="btn btn-primary"><i class="bi bi-plus-circle"></i></button>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 12%">{{ __('Banner') }}</th>
                                            <th style="width: 75%">{{ __('Description') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($banners as $banner)
                                            @php
                                                $banner_image = isset($banner->$image_key) ? $banner->$image_key : '';
                                                $banner_description = isset($banner->$description_key) ? $banner->$description_key : '';
                                            @endphp

                                            <tr>
                                                <td>
                                                    @if(!empty($banner_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_image))
                                                        <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/banners/'.$banner_image) }}" class="w-100">
                                                    @else
                                                        <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" style="width: 249px; height: 160px;">
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        @php
                                                            $banner_description = strip_tags($banner_description);
                                                        @endphp
                                                        {{ substr($banner_description,0,170) }} ...
                                                    </div>
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-primary mt-2" onclick="editBanner({{ $banner->id }})"><i class="bi bi-pencil"></i></a>
                                                    <a class="btn btn-sm btn-danger mt-2" onclick="deleteBanner({{ $banner->id }})"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="3">
                                                    <h4>{{ __('Records Not Found !') }}</h4>
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
        </div>
     </section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        var addBanEditor;
        var editBanEditor;
        var addKey=0;

        // Reset New BannerForm when Click on add New Banner
        $('#addBannerBtn').on('click',function()
        {
            // Reset NewCategoryForm
            $('#addBannerForm').trigger('reset');

            // Remove Validation Class
            $('#addBannerForm #image').removeClass('is-invalid');

            // Clear all Toastr Messages
            toastr.clear();

            $('.ck-editor').remove();
            addBanEditor = "";
            addKey = 0;

            var cat_textarea = $('#addBannerForm #description')[0];

            // Text Editor
            CKEDITOR.ClassicEditor.create(cat_textarea,
            {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                        'bulletedList', 'numberedList', 'todoList', '|',
                        'outdent', 'indent', '|',
                        'undo', 'redo',
                        '-',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                        'alignment', '|',
                        'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                        'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                        'sourceEditing'
                    ],
                    shouldNotGroupWhenFull: true
                },
                list: {
                    properties: {
                        styles: true,
                        startIndex: true,
                        reversed: true
                    }
                },
                'height':500,
                fontSize: {
                    options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                    supportAllValues: true
                },
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ]
                },
                htmlEmbed: {
                    showPreviews: true
                },
                link: {
                    decorators: {
                        addTargetToExternalLinks: true,
                        defaultProtocol: 'https://',
                        toggleDownloadable: {
                            mode: 'manual',
                            label: 'Downloadable',
                            attributes: {
                                download: 'file'
                            }
                        }
                    }
                },
                mention: {
                    feeds: [
                        {
                            marker: '@',
                            feed: [
                                '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                                '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                                '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                                '@sugar', '@sweet', '@topping', '@wafer'
                            ],
                            minimumCharacters: 1
                        }
                    ]
                },
                removePlugins: [
                    'CKBox',
                    'CKFinder',
                    'EasyImage',
                    'RealTimeCollaborativeComments',
                    'RealTimeCollaborativeTrackChanges',
                    'RealTimeCollaborativeRevisionHistory',
                    'PresenceList',
                    'Comments',
                    'TrackChanges',
                    'TrackChangesData',
                    'RevisionHistory',
                    'Pagination',
                    'WProofreader',
                    'MathType'
                ]
            }).then( editor => {
                addBanEditor = editor;
            });

        });

        // Remove Some Fetaures when Close Edit Modal
        $('#editBannerModal .btn-close').on('click',function(){
            editBanEditor = "";
            $('.ck-editor').remove();
            $('#editBannerModal #banner_edit_div').html('');
        });

        // Function for Save Banner
        function saveBanner()
        {
            const myFormData = new FormData(document.getElementById('addBannerForm'));
            myDesc = (addBanEditor?.getData()) ? addBanEditor.getData() : '';
            myFormData.set('description',myDesc);

            // Remove Validation Class
            $('#addBannerForm #image').removeClass('is-invalid');

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('banners.store') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#addBannerForm').trigger('reset');
                        $('#addBannerModal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#addBannerForm').trigger('reset');
                        $('#addBannerModal').modal('hide');
                        toastr.error(response.message);
                    }
                },
                error: function(response)
                {
                    // All Validation Errors
                    const validationErrors = (response?.responseJSON?.errors) ? response.responseJSON.errors : '';

                    if (validationErrors != '')
                    {
                        // Image Error
                        var imageError = (validationErrors.image) ? validationErrors.image : '';
                        if (imageError != '')
                        {
                            $('#addBannerForm #image').addClass('is-invalid');
                            toastr.error(imageError);
                        }
                    }
                }
            });
        }

        // Function for Delete Banner
        function deleteBanner(bannerID)
        {
            swal({
                title: "Are you sure You want to Delete It ?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelBanner) =>
            {
                if (willDelBanner)
                {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("banners.delete") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            'id': bannerID,
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

        // Function for Edit Banners
        function editBanner(bannerID)
        {
            // Reset All Form
            $('#editBannerModal #banner_edit_div').html('');

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('banners.edit') }}",
                dataType: "JSON",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': bannerID,
                },
                success: function(response)
                {
                    if (response.success == 1)
                    {
                        $('#editBannerModal #banner_edit_div').html(response.data);
                        $('#editBannerModal').modal('show');

                        $('.ck-editor').remove();
                        editBanEditor = "";

                        var my_banner_textarea = $('#editBannerForm #description')[0];

                        CKEDITOR.ClassicEditor.create(my_banner_textarea,
                        {
                            toolbar: {
                                items: [
                                    'heading', '|',
                                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                                    'bulletedList', 'numberedList', 'todoList', '|',
                                    'outdent', 'indent', '|',
                                    'undo', 'redo',
                                    '-',
                                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                                    'alignment', '|',
                                    'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                                    'sourceEditing'
                                ],
                                shouldNotGroupWhenFull: true
                            },
                            list: {
                                properties: {
                                    styles: true,
                                    startIndex: true,
                                    reversed: true
                                }
                            },
                            'height':500,
                            fontSize: {
                                options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                                supportAllValues: true
                            },
                            htmlSupport: {
                                allow: [
                                    {
                                        name: /.*/,
                                        attributes: true,
                                        classes: true,
                                        styles: true
                                    }
                                ]
                            },
                            htmlEmbed: {
                                showPreviews: true
                            },
                            link: {
                                decorators: {
                                    addTargetToExternalLinks: true,
                                    defaultProtocol: 'https://',
                                    toggleDownloadable: {
                                        mode: 'manual',
                                        label: 'Downloadable',
                                        attributes: {
                                            download: 'file'
                                        }
                                    }
                                }
                            },
                            mention: {
                                feeds: [
                                    {
                                        marker: '@',
                                        feed: [
                                            '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                                            '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                                            '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                                            '@sugar', '@sweet', '@topping', '@wafer'
                                        ],
                                        minimumCharacters: 1
                                    }
                                ]
                            },
                            removePlugins: [
                                'CKBox',
                                'CKFinder',
                                'EasyImage',
                                'RealTimeCollaborativeComments',
                                'RealTimeCollaborativeTrackChanges',
                                'RealTimeCollaborativeRevisionHistory',
                                'PresenceList',
                                'Comments',
                                'TrackChanges',
                                'TrackChangesData',
                                'RevisionHistory',
                                'Pagination',
                                'WProofreader',
                                'MathType'
                            ]
                        }).then( editor => {
                            editBanEditor = editor;
                        });

                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }

        // Update Banner By Language Code
        function updateByCode(next_lang_code)
        {
            const myFormData = new FormData(document.getElementById('editBannerForm'));
            myDesc = (editBanEditor?.getData()) ? editBanEditor.getData() : '';
            myFormData.set('description',myDesc);
            myFormData.append('next_lang_code',next_lang_code);

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('banners.update-by-lang') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editBannerModal #banner_edit_div').html('');
                        $('#editBannerModal #banner_edit_div').html(response.data);

                        $('.ck-editor').remove();
                        editBanEditor = "";

                        var my_banner_textarea = $('#editBannerForm #description')[0];

                        CKEDITOR.ClassicEditor.create(my_banner_textarea,
                        {
                            toolbar: {
                                items: [
                                    'heading', '|',
                                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                                    'bulletedList', 'numberedList', 'todoList', '|',
                                    'outdent', 'indent', '|',
                                    'undo', 'redo',
                                    '-',
                                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                                    'alignment', '|',
                                    'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                                    'sourceEditing'
                                ],
                                shouldNotGroupWhenFull: true
                            },
                            list: {
                                properties: {
                                    styles: true,
                                    startIndex: true,
                                    reversed: true
                                }
                            },
                            'height':500,
                            fontSize: {
                                options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                                supportAllValues: true
                            },
                            htmlSupport: {
                                allow: [
                                    {
                                        name: /.*/,
                                        attributes: true,
                                        classes: true,
                                        styles: true
                                    }
                                ]
                            },
                            htmlEmbed: {
                                showPreviews: true
                            },
                            link: {
                                decorators: {
                                    addTargetToExternalLinks: true,
                                    defaultProtocol: 'https://',
                                    toggleDownloadable: {
                                        mode: 'manual',
                                        label: 'Downloadable',
                                        attributes: {
                                            download: 'file'
                                        }
                                    }
                                }
                            },
                            mention: {
                                feeds: [
                                    {
                                        marker: '@',
                                        feed: [
                                            '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                                            '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                                            '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                                            '@sugar', '@sweet', '@topping', '@wafer'
                                        ],
                                        minimumCharacters: 1
                                    }
                                ]
                            },
                            removePlugins: [
                                'CKBox',
                                'CKFinder',
                                'EasyImage',
                                'RealTimeCollaborativeComments',
                                'RealTimeCollaborativeTrackChanges',
                                'RealTimeCollaborativeRevisionHistory',
                                'PresenceList',
                                'Comments',
                                'TrackChanges',
                                'TrackChangesData',
                                'RevisionHistory',
                                'Pagination',
                                'WProofreader',
                                'MathType'
                            ]
                        }).then( editor => {
                            editBanEditor = editor;
                        });
                    }
                    else
                    {
                        $('#editBannerModal').modal('hide');
                        $('#editBannerModal #banner_edit_div').html('');
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

        // Update Banner
        function updateBanner()
        {
            const myFormData = new FormData(document.getElementById('editBannerForm'));
            myDesc = (editBanEditor?.getData()) ? editBanEditor.getData() : '';
            myFormData.set('description',myDesc);

            // Clear all Toastr Messages
            toastr.clear();

            $.ajax({
                type: "POST",
                url: "{{ route('banners.update') }}",
                data: myFormData,
                dataType: "JSON",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        $('#editBannerModal').modal('hide');
                        $('#editBannerModal #banner_edit_div').html('');
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        $('#editBannerModal').modal('hide');
                        $('#editBannerModal #banner_edit_div').html('');
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


        // Function for Delete Banner Image
        function deleteBannerImage(bannerID,languageCode)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('banners.delete.image') }}",
                data: {
                    "_token" : "{{ csrf_token() }}",
                    "lang_code" : languageCode,
                    "banner_id" : bannerID,
                },
                dataType: "JSON",
                success: function (response)
                {
                    if(response.success == 1)
                    {
                        toastr.success(response.message);
                        $('.banner-img').remove();
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }

    </script>

@endsection
