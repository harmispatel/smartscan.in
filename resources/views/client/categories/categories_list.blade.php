@php
    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
    $title = ($parent_cat_id == 'pdf_page') ? 'PDF' : ($parent_cat_id == 'check_in' ? 'Check In Pages' : ucfirst($parent_cat_id).'s');

    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

    // Get Language Settings
    $language_settings = clientLanguageSettings($shop_id);
    $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

    // Primary Language Details
    $primary_language_detail = \App\Models\Languages::where('id',$primary_lang_id)->first();
    $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
    $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

    $name_key = $primary_lang_code."_name";
@endphp

@extends('client.layouts.client-layout')

@section('title', __($title))

@section('content')

    {{-- Edit Modal --}}
    <div class="modal fade" id="editCategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">{{ __('Edit Category')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cat_lang_div">
                </div>
                <div class="modal-footer">
                    <a class="btn btn-sm btn-success" onclick="updateCategory()">{{ __('Update') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal fade" id="addCategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">{{ __('Create New category')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCategoryForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="parent_cat_id" id="parent_cat_id" value="">
                    <input type="hidden" name="schedule_array" id="schedule_array">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="input_label">
                                    <label for="tyep" class="form-label">{{ __('Type') }}</label>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group mb-3">
                                    <select name="category_type" id="category_type" class="form-select" onchange="changeElements('addCategoryForm')">

                                        @if(isset($parent_cat_id) && !empty($parent_cat_id) && $parent_cat_id == 'page')
                                            <option value="page">Page</option>
                                        @endif

                                        @if(isset($parent_cat_id) && !empty($parent_cat_id) && $parent_cat_id == 'link')
                                            <option value="link">Link</option>
                                        @endif

                                        @if(isset($parent_cat_id) && !empty($parent_cat_id) && $parent_cat_id == 'check_in')
                                            <option value="check_in">Check-In Page</option>
                                        @endif

                                        @if(isset($parent_cat_id) && !empty($parent_cat_id) && $parent_cat_id == 'pdf_page')
                                            <option value="pdf_page">PDF Category</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 cat_div" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label">{{ __('Category') }}</label>
                                </div>
                            </div>
                            <div class="col-md-10 cat_div" style="display: none;">
                                <div class="form-group mb-3" id="categories_div">
                                    <input type="radio" name="parent_cat" id="root" value="0" checked> <label for="root">Root</label> <br>
                                    @if(count($parent_categories) > 0)
                                        @foreach ($parent_categories as $key => $pcategory)
                                            <input type="radio" name="parent_cat" id="pcat_{{ $key }}" value="{{ $pcategory->id }}"> <label for="pcat_{{ $key }}">{{ $pcategory->name }}</label><br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input_label">
                                    <label class="form-label" for="name">{{ __('Name')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group mb-3">
                                    <input type="text" name="name" class="form-control" id="name" placeholder="Category Title">
                                </div>
                            </div>
                            <div class="col-md-2 url" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label" for="url">{{ __('URL')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 url" style="display: none;">
                                <div class="form-group mb-3">
                                    <input type="text" name="url" class="form-control" id="url" placeholder="Enter Your Link Url">
                                </div>
                            </div>
                            <div class="col-md-2 chk_page_styles" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label" for="background_color">{{ __('Background Color')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 chk_page_styles" style="display: none;">
                                <div class="form-group mb-3">
                                    <input type="color" name="checkin_styles[background_color]" class="form-control" id="background_color" placeholder="Enter Background Color">
                                </div>
                            </div>
                            <div class="col-md-2 chk_page_styles" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label" for="font_color">{{ __('Font Color')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 chk_page_styles" style="display: none;">
                                <div class="form-group mb-3">
                                    <input type="color" name="checkin_styles[font_color]" class="form-control" id="font_color" placeholder="Enter Background Color">
                                </div>
                            </div>
                            <div class="col-md-2 chk_page_styles" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label" for="button_color">{{ __('Button Color')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 chk_page_styles" style="display: none;">
                                <div class="form-group mb-3">
                                    <input type="color" name="checkin_styles[button_color]" class="form-control" id="button_color" placeholder="Enter Background Color">
                                </div>
                            </div>
                            <div class="col-md-2 chk_page_styles" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label" for="button_text_color">{{ __('Button Text Color')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 chk_page_styles" style="display: none;">
                                <div class="form-group mb-3">
                                    <input type="color" name="checkin_styles[button_text_color]" class="form-control" id="button_text_color" placeholder="Enter Background Color">
                                </div>
                            </div>
                            <div class="col-md-2 description">
                                <div class="input_label">
                                    <label class="form-label" for="description">{{ __('Description')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 description">
                                <div class="form-group mb-3">
                                    <textarea class="form-control" name="description" id="description" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-10 mt-2 mb-2 d-flex flex-wrap" id="images_div">
                            </div>
                            <div class="col-md-12 mul-image" id="img-val"></div>
                            <div class="col-md-2 mul-image">
                                <div class="input_label">
                                    <label class="form-label" for="image">{{ __('Image')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 mul-image">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div id="img-label">
                                                <label for="image">
                                                    Upload Images
                                                </label>
                                            </div>
                                           <input type="file" name="image" id="image" class="form-control" style="display: none;">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <code class="img-upload-label">Upload Image in (400*400) Dimensions</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 img-crop-sec mb-2" style="display: none">
                                <img src="" alt="" id="resize-image" class="w-100">
                                <div class="mt-3">
                                    <a class="btn btn-sm btn-success" onclick="saveCropper('addCategoryForm')">Save</a>
                                    <a class="btn btn-sm btn-danger" onclick="resetCropper()">Reset</a>
                                    <a class="btn btn-sm btn-secondary" onclick="cancelCropper('addCategoryForm')">Cancel</a>
                                </div>
                            </div>
                            <div class="col-md-4 img-crop-sec" style="display: none;">
                                <div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>
                            </div>
                            <div class="col-md-2 mt-2 cover" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label">{{ __('Thumbnail')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 mt-2 cover" id="cover_label" style="display: none;">
                                <div class="form-group">
                                    <input type="file" onchange="CoverPreview('addCategoryForm',this)" id="cover" name="cover" style="display: none">
                                    <div class="page-cover">
                                        <label for="cover" id="upload-page-cover-image">
                                            <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" class="w-100 h-100">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 mt-2 pdf" style="display: none;">
                                <div class="input_label">
                                    <label class="form-label">{{ __('PDF File')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 mt-2 pdf" id="pdf_label" style="display: none;">
                                <div class="form-group">
                                    <input type="file" onchange="PdfPreview('addCategoryForm',this)" id="pdf" name="pdf" style="display: none">
                                    <div class="pdf-file">
                                        <label for="pdf" id="upload-pdf-file">
                                            <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}" class="w-100 h-100">
                                        </label>
                                    </div>
                                    <h4 id="pdf-name" style="display: none;"></h4>
                                </div>
                            </div>
                            <div class="col-md-2 mt-3">
                                <div class="input_label">
                                    <label class="form-label" for="publish">{{ __('Published')}}</label>
                                </div>
                            </div>
                            <div class="col-md-10 mt-3">
                                <div class="form-group mb-3">
                                    <label class="switch">
                                        <input type="checkbox" id="publish" name="published" value="1">
                                        <span class="slider round">
                                            <i class="fa-solid fa-circle-check check_icon"></i>
                                            <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="input-label text-primary schedule-toggle">
                                    <i class="fa fa-clock" onclick="$('#schedule-main-div').toggle()"></i> <span>Scheduling Not Active</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3" id="schedule-main-div" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <select name="schedule_type" id="schedule_type" onchange="changeScheduleType('addCategoryModal')" class="form-select">
                                            <option value="time">Time</option>
                                            <option value="date">Date</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2 text-end">
                                        <label class="switch">
                                            <input type="checkbox" id="schedule" name="schedule" value="1" onchange="changeScheduleLabel('addCategoryForm')">
                                            <span class="slider round">
                                                <i class="fa-solid fa-circle-check check_icon"></i>
                                                <i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 sc_inner sc_time">
                                        <div class="sc_array_section" id="sc_array_section">
                                            <div class="p-2" id="sunday_sec">
                                               <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="sunday"> <label for="sunday">Sun</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('sunday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="monday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="monday"> <label for="monday">Mon</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('monday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="tuesday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="tuesday"> <label for="tuesday">Tue</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('tuesday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="wednesday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="wednesday"> <label for="wednesday">Wed</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('wednesday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="thursday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="thursday"> <label for="thursday">Thu</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('thursday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="friday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="friday"> <label for="friday">Fri</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('friday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                            <div class="p-2" id="saturday_sec">
                                                <div class="text-center">
                                                    <input type="checkbox" class="me-2" name="" id="saturday"> <label for="saturday">Sat</label>
                                               </div>
                                                <div class="sch-sec">
                                                    <div class="sch_1">
                                                        <input type="time" class="form-control mt-2" name="startTime" id="startTime">
                                                        <input type="time" class="form-control mt-2" name="endTime" id="endTime">
                                                    </div>
                                                </div>
                                                <div class="sch-plus">
                                                    <i class="bi bi-plus-circle" onclick="addNewSchedule('saturday_sec','addCategoryForm')"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 sc_date" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="start_date" class="form-label">Start Date</label>
                                                <input type="date" name="start_date" id="start_date" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="end_date" class="form-label">End Date</label>
                                                <input type="date" name="end_date" id="end_date" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn close-btn btn-secondary" data-bs-dismiss="modal">{{ __('Close')}}</button>
                        <a class="btn btn-primary" id="saveCategory" onclick="saveCategory()">{{ __('Save')}}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <input type="hidden" name="par_cat_id" id="par_cat_id" value="{{ $parent_cat_id }}"> --}}

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __($title)}}</h1>
    </div>

    {{-- Clients Section --}}
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
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>{{ __($title)}}</h3>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="btn btn-sm btn-primary" id="NewCategoryBtn"><i class="bi bi-plus-circle"></i> {{ __('Create') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="option_main">
                            <ul>
                                @forelse ($categories as $category)
                                    <li>
                                        <div class="option_box">
                                            <div class="option_title">
                                                @if(!empty($category->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover))
                                                    <img src="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover) }}">
                                                @else
                                                    <img src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}">
                                                @endif

                                                @if($parent_cat_id == 'link')
                                                    <a href="{{ $category->link_url }}" class="text-primary" style="font-size: 16px" target="_blank">{{ isset($category[$name_key]) ? $category[$name_key] : '' }}</a>
                                                @else
                                                    <a class="text-primary" style="font-size: 16px">{{ isset($category[$name_key]) ? $category[$name_key] : '' }}</a>
                                                @endif
                                            </div>
                                            <div>
                                                @if($parent_cat_id == 'pdf_page' || $parent_cat_id == 'link')
                                                    @if($parent_cat_id == 'pdf_page' && !empty($category->file) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->file))
                                                        <a target="_blank" class="opt_view_btn btn btn-sm btn-dark text-white" href="{{ asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->file) }}"><i class="bi bi-eye"></i></a>
                                                    @elseif($parent_cat_id == 'link' && !empty($category->link_url))
                                                        <a target="_blank" class="opt_view_btn btn btn-sm btn-dark text-white" href="{{ $category->link_url }}"><i class="bi bi-eye"></i></a>
                                                    @else
                                                        <button disabled class="opt_view_btn btn btn-sm btn-dark text-white"><i class="bi bi-eye"></i></button>
                                                    @endif
                                                @endif
                                                <a onclick="editCategory({{ $category->id }})" class="btn btn-sm btn-primary opt_edit_btn"><i class="bi bi-pencil"></i></a>
                                                <a onclick="deleteCategory({{ $category->id }})" class="btn btn-sm btn-danger opt_del_btn"><i class="bi bi-trash"></i></a>
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

{{-- Custom JS --}}
@section('page-js')

<script type="text/javascript">

    var cropper;
    var addCatEditor;
    var editCatEditor;
    var addKey=0;

    // Reset New CategoryForm
    $('#NewCategoryBtn').on('click',function()
    {
        // Reset NewCategoryForm
        $('#addCategoryForm').trigger('reset');

        // Reset Elements
        changeElements('addCategoryForm');

        // Remove Validation Class
        $('#addCategoryForm #name').removeClass('is-invalid');
        $('#addCategoryForm #url').removeClass('is-invalid');
        $('#addCategoryForm #start_date').removeClass('is-invalid');
        $('#addCategoryForm #end_date').removeClass('is-invalid');

        // Clear all Toastr Messages
        toastr.clear();

        $('.ck-editor').remove();
        addCatEditor = "";
        $('#addCategoryForm #images_div').html('');
        $('#addCategoryForm #img-val').html('');
        addKey = 0;

        var cat_textarea = $('#addCategoryForm #description')[0];

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
            addCatEditor = editor;
        });

        if(cropper)
        {
            cancelCropper('addCategoryForm');
        }

        // Refresh Schedule Content
        $('#addCategoryModal #schedule-main-div .sc_inner').load(document.URL + ' #sc_array_section');
        $('.schedule-toggle span').html('');
        $('.schedule-toggle span').append('Scheduling Not Active');
        $('#schedule-main-div').hide();

    });


    // Remove Some Fetaures when Close Add Modal
    $('#addCategoryModal .btn-close, #addCategoryModal .close-btn').on('click',function()
    {
        // Reset NewCategoryForm
        $('#addCategoryForm').trigger('reset');

        // Reset Elements
        changeElements('addCategoryForm');

        // Blank Multiple Image Div
        $('#addCategoryForm #images_div').html('');
        $('#addCategoryForm #img-val').html('');

        addKey = 0;
        addCatEditor = "";
        $('.ck-editor').remove();

        // Refresh Schedule Content
        $('.schedule-toggle span').html('');
        $('.schedule-toggle span').append('Scheduling Not Active');
        $('#schedule-main-div').hide();

    });


    // Remove Some Fetaures when Close Edit Modal
    $('#editCategoryModal .btn-close').on('click',function(){
        editCatEditor = "";
        $('.ck-editor').remove();
        if(cropper)
        {
            cropper.destroy();
        }
        $('#editCategoryModal #cat_lang_div').html('');
    });


    // Image Cropper Functionality for Add Model
    $('#addCategoryModal #image').on('change',function()
    {
        const myFormID = this.form.id;
        const currentFile = this.files[0];
        var img_crp_size = getImageCroppedSize(myFormID);
        var fitPreview = 0;

        if (currentFile)
        {
            var catImage = new Image();
            catImage.src = URL.createObjectURL(currentFile);
            catImage.onload = function()
            {
                if(this.width === img_crp_size.width && this.height === img_crp_size.height)
                {
                    fitPreview = 1;
                }

                fileSize = currentFile.size / 1024 / 1024;
                fileName = currentFile.name;
                fileType = fileName.split('.').pop().toLowerCase();

                if(fileSize > 2)
                {
                    toastr.error("File is to Big "+fileSize.toFixed(2)+"MiB. Max File size : 2 MiB.");
                    $('#'+myFormID+' #image').val('');
                    return false;
                }
                else
                {
                    if($.inArray(fileType, ['gif','png','jpg','jpeg']) == -1)
                    {
                        toastr.error("The Category Image must be a file of type: png, jpg, svg, jpeg");
                        $('#'+myFormID+' #image').val('');
                        return false;
                    }
                    else
                    {
                        if(cropper)
                        {
                            cropper.destroy();
                        }

                        $('#'+myFormID+' #resize-image').attr('src',"");
                        $('#'+myFormID+' #resize-image').attr('src',URL.createObjectURL(currentFile));
                        $('#'+myFormID+' .img-crop-sec').show();

                        const CrpImage = document.getElementById('resize-image');
                        cropper = new Cropper(CrpImage, {
                            aspectRatio: img_crp_size.ratio,
                            zoomable:false,
                            cropBoxResizable: false,
                            autoCropArea: fitPreview,
                            preview: '#'+myFormID+' .preview',
                        });
                    }
                }
            }
        }
    });


    // Save New Category
    function saveCategory()
    {
        var main_arr = {};
        var days_arr = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

        $.each(days_arr, function (indexInArray, day)
        {
            var dayName = $('#addCategoryForm #'+day+'_sec label').html();
            var checkedVal = $('#addCategoryForm #'+day+'_sec #'+day).is(":checked");
            var scheduleLength = $('#addCategoryForm #'+day+'_sec .sch-sec').children('div').length;
            var sch_all_childs = $('#addCategoryForm #'+day+'_sec .sch-sec').children('div');

            var time_arr = [];
            var inner_arr_1 = {};

            inner_arr_1['name'] = dayName;
            inner_arr_1['enabled'] = checkedVal;
            inner_arr_1['dayInWeek'] = indexInArray;

            for(var i=0;i<scheduleLength;i++)
            {
                var inner_arr_2 = {};
                var sch_child = sch_all_childs[i];
                var className = sch_child.getAttribute('class');
                inner_arr_2['startTime'] = $('#addCategoryForm #'+day+'_sec .sch-sec .'+className+' #startTime').val();
                inner_arr_2['endTime'] = $('#addCategoryForm #'+day+'_sec .sch-sec .'+className+' #endTime').val();
                time_arr.push(inner_arr_2);
            }

            inner_arr_1['timesSchedules'] = time_arr;
            main_arr[day] = inner_arr_1;
        });

        const myFormData = new FormData(document.getElementById('addCategoryForm'));
        myDesc = (addCatEditor?.getData()) ? addCatEditor.getData() : '';
        myFormData.set('description',myDesc);
        myFormData.append('schedule_array', JSON.stringify(main_arr));

        // Remove Validation Class
        $('#addCategoryForm #name').removeClass('is-invalid');
        $('#addCategoryForm #url').removeClass('is-invalid');
        $('#addCategoryForm #start_date').removeClass('is-invalid');
        $('#addCategoryForm #end_date').removeClass('is-invalid');

        // Clear all Toastr Messages
        toastr.clear();

        $.ajax({
            type: "POST",
            url: "{{ route('categories.store') }}",
            data: myFormData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#addCategoryForm').trigger('reset');
                    $('#addCategoryModal').modal('hide');
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
                else
                {
                    $('#addCategoryForm').trigger('reset');
                    $('#addCategoryModal').modal('hide');
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
                    var nameError = (validationErrors.name) ? validationErrors.name : '';
                    if (nameError != '')
                    {
                        $('#addCategoryForm #name').addClass('is-invalid');
                        toastr.error(nameError);
                    }

                    // URL Error
                    var urlError = (validationErrors.url) ? validationErrors.url : '';
                    if (urlError != '')
                    {
                        $('#addCategoryForm #url').addClass('is-invalid');
                        toastr.error(urlError);
                    }

                    // Start Date Error
                    var strDateError = (validationErrors.start_date) ? validationErrors.start_date : '';
                    if (strDateError != '')
                    {
                        $('#addCategoryForm #start_date').addClass('is-invalid');
                        toastr.error(strDateError);
                    }

                    // Enf Date Error
                    var endDateError = (validationErrors.end_date) ? validationErrors.end_date : '';
                    if (endDateError != '')
                    {
                        $('#addCategoryForm #end_date').addClass('is-invalid');
                        toastr.error(endDateError);
                    }
                }
            }
        });
    }


    // Function for Delete Category
    function deleteCategory(catId)
    {
        swal({
            title: "Are you sure You want to Delete It ?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDeleteCategory) =>
        {
            if (willDeleteCategory)
            {
                $.ajax({
                    type: "POST",
                    url: '{{ route("categories.delete") }}',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'id': catId,
                    },
                    dataType: 'JSON',
                    success: function(response)
                    {
                        if (response.success == 1)
                        {
                            toastr.success(response.message);
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


    // Function for Edit Category
    function editCategory(catID)
    {
        // Reset All Form
        $('#editCategoryModal #cat_lang_div').html('');

        $('.ck-editor').remove();
        editCatEditor = "";

        // Clear all Toastr Messages
        toastr.clear();

        $.ajax({
            type: "POST",
            url: "{{ route('categories.edit') }}",
            dataType: "JSON",
            data: {
                '_token': "{{ csrf_token() }}",
                'id': catID,
            },
            success: function(response)
            {
                if (response.success == 1)
                {
                    $('#editCategoryModal #cat_lang_div').html('');
                    $('#editCategoryModal #cat_lang_div').append(response.data);
                    $('#editCategoryModal').modal('show');

                    // Set Elements
                    if(response.category_type == 'page')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (700*400) Dimensions');
                    }
                    else if(response.category_type == 'product_category')
                    {
                        $('#editCategoryModal .cover').hide();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
                    }
                    else if(response.category_type == 'link')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').show();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                    }
                    else if(response.category_type == 'gallery')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (400*400) Dimensions');
                    }
                    else if(response.category_type == 'check_in')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').show();
                        $('#editCategoryModal .cat_div').hide();
                    }
                    else if(response.category_type == 'parent_category')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').show();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
                    }
                    else if(response.category_type == 'pdf_page')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').show();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                    }

                    changeScheduleType('editCategoryModal');

                    var my_cat_textarea = $('#editCategoryModal #category_description')[0];

                    // Text Editor
                    CKEDITOR.ClassicEditor.create(my_cat_textarea,
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
                        editCatEditor = editor;
                    });
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
        var formID = "edit_category_form";
        var main_arr = {};
        var days_arr = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

        $.each(days_arr, function (indexInArray, day)
        {
            var dayName = $('#'+formID+' #'+day+'_sec label').html();
            var checkedVal = $('#'+formID+' #'+day+'_sec #'+day).is(":checked");
            var scheduleLength = $('#'+formID+' #'+day+'_sec .sch-sec').children('div').length;
            var sch_all_childs = $('#'+formID+' #'+day+'_sec .sch-sec').children('div');

            var time_arr = [];
            var inner_arr_1 = {};

            inner_arr_1['name'] = dayName;
            inner_arr_1['enabled'] = checkedVal;
            inner_arr_1['dayInWeek'] = indexInArray;

            for(var i=0;i<scheduleLength;i++)
            {
                var inner_arr_2 = {};
                var sch_child = sch_all_childs[i];
                var className = sch_child.getAttribute('class');

                inner_arr_2['startTime'] = $('#'+formID+' #'+day+'_sec .sch-sec .'+className+' #startTime').val();
                inner_arr_2['endTime'] = $('#'+formID+' #'+day+'_sec .sch-sec .'+className+' #endTime').val();
                time_arr.push(inner_arr_2);
            }

            inner_arr_1['timesSchedules'] = time_arr;
            main_arr[day] = inner_arr_1;
        });

        var myFormData = new FormData(document.getElementById(formID));
        myDesc = (editCatEditor?.getData()) ? editCatEditor.getData() : '';
        myFormData.set('category_description',myDesc);
        myFormData.append('schedule_array', JSON.stringify(main_arr));
        myFormData.append('next_lang_code',next_lang_code);

        // Clear all Toastr Messages
        toastr.clear();

        $.ajax({
            type: "POST",
            url: "{{ route('categories.update.by.lang') }}",
            data: myFormData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('.ck-editor').remove();
                    editCatEditor = "";

                    $('#editCategoryModal #cat_lang_div').html('');
                    $('#editCategoryModal #cat_lang_div').html(response.data);

                    // Set Elements
                    if(response.category_type == 'page')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (700*400) Dimensions');
                    }
                    else if(response.category_type == 'product_category')
                    {
                        $('#editCategoryModal .cover').hide();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
                    }
                    else if(response.category_type == 'link')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').show();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                    }
                    else if(response.category_type == 'gallery')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (400*400) Dimensions');
                    }
                    else if(response.category_type == 'check_in')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').show();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').show();
                        $('#editCategoryModal .cat_div').hide();
                    }
                    else if(response.category_type == 'parent_category')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').show();
                        $('#editCategoryModal .pdf').hide();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').show();
                        $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
                    }
                    else if(response.category_type == 'pdf_page')
                    {
                        $('#editCategoryModal .cover').show();
                        $('#editCategoryModal .url').hide();
                        $('#editCategoryModal .description').hide();
                        $('#editCategoryModal .mul-image').hide();
                        $('#editCategoryModal .pdf').show();
                        $('#editCategoryModal .chk_page_styles').hide();
                        $('#editCategoryModal .cat_div').hide();
                    }

                    changeScheduleType('editCategoryModal');

                    var my_cat_textarea = $('#editCategoryModal #category_description')[0];

                    // Text Editor
                    CKEDITOR.ClassicEditor.create(my_cat_textarea,
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
                        editCatEditor = editor;
                    });
                }
                else
                {
                    $('#editCategoryModal').modal('hide');
                    $('#editCategoryModal #cat_lang_div').html('');
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


    // Function for Update Category
    function updateCategory()
    {
        var formID = "edit_category_form";
        var main_arr = {};
        var days_arr = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

        $.each(days_arr, function (indexInArray, day)
        {
            var dayName = $('#'+formID+' #'+day+'_sec label').html();
            var checkedVal = $('#'+formID+' #'+day+'_sec #'+day).is(":checked");
            var scheduleLength = $('#'+formID+' #'+day+'_sec .sch-sec').children('div').length;
            var sch_all_childs = $('#'+formID+' #'+day+'_sec .sch-sec').children('div');

            var time_arr = [];
            var inner_arr_1 = {};

            inner_arr_1['name'] = dayName;
            inner_arr_1['enabled'] = checkedVal;
            inner_arr_1['dayInWeek'] = indexInArray;

            for(var i=0;i<scheduleLength;i++)
            {
                var inner_arr_2 = {};
                var sch_child = sch_all_childs[i];
                var className = sch_child.getAttribute('class');

                inner_arr_2['startTime'] = $('#'+formID+' #'+day+'_sec .sch-sec .'+className+' #startTime').val();
                inner_arr_2['endTime'] = $('#'+formID+' #'+day+'_sec .sch-sec .'+className+' #endTime').val();
                time_arr.push(inner_arr_2);
            }

            inner_arr_1['timesSchedules'] = time_arr;
            main_arr[day] = inner_arr_1;
        });

        var myFormData = new FormData(document.getElementById(formID));
        myDesc = (editCatEditor?.getData()) ? editCatEditor.getData() : '';
        myFormData.set('category_description',myDesc);
        myFormData.append('schedule_array', JSON.stringify(main_arr));

        // Clear all Toastr Messages
        toastr.clear();

        $.ajax({
            type: "POST",
            url: "{{ route('categories.update') }}",
            data: myFormData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            success: function (response)
            {
                if(response.success == 1)
                {
                    // $('#editCategoryModal').modal('hide');
                    toastr.success(response.message);
                    // setTimeout(() => {
                    //     location.reload();
                    // }, 1000);
                }
                else
                {
                    $('#editCategoryModal').modal('hide');
                    toastr.error(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
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


    // Function for Change Category Status
    function changeStatus(catId, status)
    {
        $.ajax({
            type: "POST",
            url: '{{ route("categories.status") }}',
            data: {
                "_token": "{{ csrf_token() }}",
                'status':status,
                'id':catId
            },
            dataType: 'JSON',
            success: function(response)
            {
                if (response.success == 1)
                {
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1300);
                }
                else
                {
                    toastr.error(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1300);
                }
            }
        });
    }


    // Image Cropper Functionality for Edit Modal
    function imageCropper(formID,ele)
    {
        var currentFile = ele.files[0];
        var myFormID = formID;
        const catType = $('#'+formID+' #category_type').val();
        var img_crp_size = getImageCroppedSize(formID);

        if (currentFile)
        {
            fileSize = currentFile.size / 1024 / 1024;
            fileName = currentFile.name;
            fileType = fileName.split('.').pop().toLowerCase();

            if(fileSize > 2)
            {
                toastr.error("File is to Big "+fileSize.toFixed(2)+"MiB. Max File size : 2 MiB.");
                $('#'+myFormID+' #category_image').val('');
                return false;
            }
            else
            {
                if($.inArray(fileType, ['gif','png','jpg','jpeg']) == -1)
                {
                    toastr.error("The Category Image must be a file of type: png, jpg, svg, jpeg");
                    $('#'+myFormID+' #category_image').val('');
                    return false;
                }
                else
                {
                    if(cropper)
                    {
                        cropper.destroy();
                        $('.resize-image').attr('src',"");
                        $('.img-crop-sec').hide();
                    }

                    $('#'+myFormID+' #resize-image').attr('src',"");
                    $('#'+myFormID+' #resize-image').attr('src',URL.createObjectURL(currentFile));
                    $('#'+myFormID+' .img-crop-sec').show();

                    // const CrpImage = document.getElementById('resize-image');
                    const CrpImage = $('#'+myFormID+' #resize-image')[0];

                    cropper = new Cropper(CrpImage, {
                        // aspectRatio: 1 / 1,
                        aspectRatio : img_crp_size.ratio,
                        zoomable:false,
                        cropBoxResizable: false,
                        preview: '#'+myFormID+' .preview',
                    });
                }
            }
        }
    }


    // Reset Copper
    function resetCropper(){
        cropper.reset();
    }


    // Canel Cropper
    function cancelCropper(formID)
    {
        cropper.destroy();
        $('#'+formID+' #resize-image').attr('src',"");
        $('#'+formID+' .img-crop-sec').hide();
        $('#'+formID+' #image').val('');
        $('#'+formID+' #'+formID+'category_image').val('');
    }


    // Save Cropper Image
    function saveCropper(formID)
    {
        const catType = $('#'+formID+' #category_type').val();
        var img_crp_size = getImageCroppedSize(formID);

        var canvas = cropper.getCroppedCanvas({
            width:img_crp_size.width,
            height:img_crp_size.height
        });

        canvas.toBlob(function(blob)
        {
            addKey++;
            var html = '';

            if(catType != 'gallery')
            {
                $('#'+formID+' #images_div').html('');
                $('#'+formID+' #img-val').html('');
            }

            var image = URL.createObjectURL(blob);
            html += '<div class="inner-img img_'+addKey+'">'
            html += '<img src="'+image+'" class="w-100 h-100">';
            html += '<a class="btn btn-sm btn-danger del-pre-btn" onclick="$(\'#'+formID+' .img_'+addKey+', #'+formID+' #img_inp_'+addKey+'\').remove()"><i class="fa fa-trash"></a>';
            html += '</div>';

            $('#'+formID+" #images_div").append(html);
            url = URL.createObjectURL(blob);
            var reader = new FileReader(url);
            reader.readAsDataURL(blob);
            reader.onloadend = function()
            {
                var base64data = reader.result;
                $('#'+formID+' #img-val').append('<input type="hidden" name="og_image[]" value="'+base64data+'" id="img_inp_'+addKey+'">');
            };
        });

        cropper.destroy();
        $('#'+formID+' #resize-image').attr('src',"");
        $('#'+formID+' .img-crop-sec').hide();

        $('#'+formID+' #image').val('');
        $('#'+formID+' #'+formID+'category_image').val('');
    }


    // Delete Cropper
    function deleteCropper(formID)
    {
        if(cropper)
        {
            cropper.destroy();
        }

        $('#'+formID+' #resize-image').attr('src',"");
        $('#'+formID+' .img-crop-sec').hide();
        $('#'+formID+' #og_image').val('');
        $('#'+formID+" #del-img").remove();

        if(formID == 'addCategoryForm')
        {
            $('#'+formID+' #image').val('');
            $('#'+formID+" #crp-img-prw").attr('src',"{{ asset('public/client_images/not-found/no_image_1.jpg') }}");
        }
        else
        {
            $('#'+formID+' #'+formID+'category_image').val('');
            $('#'+formID+" #crp-img-prw").attr('src',"{{ asset('public/client_images/not-found/no_image_1.jpg') }}");
            $('#'+formID+' #edit-img').show();
            $('#'+formID+' #rep-image').hide();
        }

    }


    // Function for Cover Image Preview
    function CoverPreview(formID,ele)
    {
        currentFile = ele.files[0];
        fileName = currentFile.name;
        fileType = fileName.split('.').pop().toLowerCase();

        if($.inArray(fileType, ['gif','png','jpg','jpeg']) == -1)
        {
            toastr.error("The Cover Image must be a file of type: png, jpg, svg, jpeg");
            $('#'+myFormID+' #cover').val('');
            return false;
        }
        else
        {
            if(formID == 'addCategoryForm')
            {
                $('#'+formID+' #upload-page-cover-image img').attr('src',URL.createObjectURL(currentFile));
                $('#'+formID+' .page-cover .btn-danger').remove();
                $('#'+formID+' .page-cover').append('<a onclick="removeCover(\''+formID+'\')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>');
            }
            else
            {
                $('#editCategoryModal #upload-page-cover-image img').attr('src',URL.createObjectURL(currentFile));
            }
        }
    }


    // Function for Delete Cover Preview
    function removeCover(formID)
    {
        $('#'+formID+' #upload-page-cover-image img').attr('src',"{{ asset('public/client_images/not-found/no_image_1.jpg') }}");
        $('#'+formID+' .page-cover .btn-danger').remove();
        $('#'+formID+' #cover').val('');
    }


    // Function for Preview PDF
    function PdfPreview(formID,ele)
    {
        currentFile = ele.files[0];
        fileName = currentFile.name;
        fileType = fileName.split('.').pop().toLowerCase();

        if($.inArray(fileType, ['pdf']) == -1)
        {
            toastr.error("The File must be a file of type: pdf");
            $('#'+myFormID+' #pdf').val('');
            return false;
        }
        else
        {
            if(formID == 'addCategoryForm')
            {
                $('#'+formID+' #pdf-name').html('');
                $('#'+formID+' .pdf-file').hide();
                $('#'+formID+' #pdf-name').html(fileName);
                $('#'+formID+' #pdf-name').show();
                $('#'+formID+' #pdf-name').append('<a onclick="removePdf(\''+formID+'\')" class="btn btn-sm btn-danger ms-2"><i class="fa fa-trash">');
            }
            else
            {
                $('#editCategoryModal #pdf-name').html('');
                $('#editCategoryModal #pdf-name').html(fileName);
            }
        }
    }


    // Function for Delete Pdf Preview
    function removePdf(formID)
    {
        $('#'+formID+' #pdf-name').html('');
        $('#'+formID+' .pdf-file').show();
        $('#'+formID+' #pdf-name').hide();
        $('#'+formID+' #pdf').val('');
    }


    // Change Elements According Category Type
    function changeElements(formID)
    {
        const cat_type = $('#'+formID+' #category_type :selected').val();
        $('#'+formID+' #images_div').html('');
        $('#'+formID+' #img-val').html('');

        if(formID == 'addCategoryForm')
        {
            // Clear PDF
            $('#'+formID+' #pdf-name').html('');
            $('#'+formID+' .pdf-file').show();
            $('#'+formID+' #pdf-name').hide();
            $('#'+formID+' #pdf').val('');

            // Clear Cover
            $('#'+formID+' #upload-page-cover-image img').attr('src',"{{ asset('public/client_images/not-found/no_image_1.jpg') }}");
            $('#'+formID+' .page-cover .btn-danger').remove();
            $('#'+formID+' #cover').val('');

            if(cat_type == 'page')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').show();
                $('#'+formID+' .mul-image').show();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').hide();
                $('#'+formID+' .img-upload-label').html('Upload Image in (700*400) Dimensions');
            }
            else if(cat_type == 'product_category')
            {
                $('#'+formID+' .cover').hide();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').show();
                $('#'+formID+' .mul-image').show();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').hide();
                $('#'+formID+' .img-upload-label').html('Upload Image in (200*200) Dimensions');
            }
            else if(cat_type == 'link')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').show();
                $('#'+formID+' .description').hide();
                $('#'+formID+' .mul-image').hide();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').hide();
            }
            else if(cat_type == 'gallery')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').hide();
                $('#'+formID+' .mul-image').show();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').hide();
                $('#'+formID+' .img-upload-label').html('Upload Image in (400*400) Dimensions');
            }
            else if(cat_type == 'check_in')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').show();
                $('#'+formID+' .mul-image').hide();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').show();
                $('#'+formID+' .cat_div').hide();
            }
            else if(cat_type == 'parent_category')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').hide();
                $('#'+formID+' .mul-image').show();
                $('#'+formID+' .pdf').hide();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').show();
                $('#'+formID+' .img-upload-label').html('Upload Image in (200*200) Dimensions');
            }
            else if(cat_type == 'pdf_page')
            {
                $('#'+formID+' .cover').show();
                $('#'+formID+' .url').hide();
                $('#'+formID+' .description').hide();
                $('#'+formID+' .mul-image').hide();
                $('#'+formID+' .pdf').show();
                $('#'+formID+' .chk_page_styles').hide();
                $('#'+formID+' .cat_div').hide();
            }
        }
        else
        {
            $('#editCategoryModal .category_type option:selected').removeAttr('selected');
            $("#editCategoryModal .category_type option[value='"+cat_type+"']").attr("selected", "selected");

            if(cat_type == 'page')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').show();
                $('#editCategoryModal .mul-image').show();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').hide();
                $('#editCategoryModal .img-upload-label').html('Upload Image in (700*400) Dimensions');
            }
            else if(cat_type == 'product_category')
            {
                $('#editCategoryModal .cover').hide();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').show();
                $('#editCategoryModal .mul-image').show();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').hide();
                $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
            }
            else if(cat_type == 'link')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').show();
                $('#editCategoryModal .description').hide();
                $('#editCategoryModal .mul-image').hide();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').hide();
            }
            else if(cat_type == 'gallery')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').hide();
                $('#editCategoryModal .mul-image').show();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').hide();
                $('#editCategoryModal .img-upload-label').html('Upload Image in (400*400) Dimensions');
            }
            else if(cat_type == 'check_in')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').show();
                $('#editCategoryModal .mul-image').hide();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').show();
                $('#editCategoryModal .cat_div').hide();
            }
            else if(cat_type == 'parent_category')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').hide();
                $('#editCategoryModal .mul-image').show();
                $('#editCategoryModal .pdf').hide();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').show();
                $('#editCategoryModal .img-upload-label').html('Upload Image in (200*200) Dimensions');
            }
            else if(cat_type == 'pdf_page')
            {
                $('#editCategoryModal .cover').show();
                $('#editCategoryModal .url').hide();
                $('#editCategoryModal .description').hide();
                $('#editCategoryModal .mul-image').hide();
                $('#editCategoryModal .pdf').show();
                $('#editCategoryModal .chk_page_styles').hide();
                $('#editCategoryModal .cat_div').hide();
            }
        }

    }


    // Function for Delete Category
    function deleteCategoryImage(no,imgID)
    {
        $.ajax({
            type: "POST",
            url: "{{ route('categories.delete.images') }}",
            data: {
                "_token" : "{{ csrf_token() }}",
                "img_id" : imgID,
            },
            dataType: "JSON",
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#editCategoryModal .edit_img_'+no).remove();
                    toastr.success(response.message);
                }
                else
                {
                    toastr.error(response.message);
                }
            }
        });
    }


    // Add New Schedule
    function addNewSchedule(divID,formID)
    {
        // sch-sec
        var html = '';
        var counter;
        counter = $('#'+formID+' #'+divID+' .sch-sec').children('div').length + 1;

        html += '<div class="sch_'+counter+'">';
            html += '<div class="sch-minus">';
                html += '<i class="bi bi-dash-circle" onclick="$(this).parent().parent().remove()"></i>';
            html += '</div>';
            html += '<input type="time" name="startTime" id="startTime" class="form-control mt-2">';
            html += '<input type="time" name="endTime" id="endTime" class="form-control mt-2">';
        html += '</div>';

        if(formID == 'addCategoryForm')
        {
            $('#'+formID+' #'+divID+" .sch-sec").append(html);
        }
        else
        {
            $('#editCategoryModal #'+divID+" .sch-sec").append(html);
        }
    }


    // Function for Change Schedule Label
    function changeScheduleLabel(formID)
    {
        var status = $('#'+formID+' #schedule').is(":checked");
        if(status == true)
        {
            $('.schedule-toggle span').html('');
            $('.schedule-toggle span').append('Scheduling Active');
        }
        else
        {
            $('.schedule-toggle span').html('');
            $('.schedule-toggle span').append('Scheduling Not Active');
        }
    }


    // Function for get width height & Accept Ratio
    function getImageCroppedSize(formID)
    {
        const catType = $('#'+formID+' #category_type').val();
        var crp_height,crp_width,crp_ratio;
        var imageSize = [];

        if(catType == 'page')
        {
            crp_width = 700;
            crp_height = 400;
        }
        else if(catType == 'gallery')
        {
            crp_width = 400;
            crp_height = 400;
        }
        else
        {
            crp_width = 200;
            crp_height = 200;
        }
        crp_ratio = crp_width / crp_height;

        imageSize.width = crp_width;
        imageSize.height = crp_height;
        imageSize.ratio = crp_ratio;

        return imageSize;
    }


    // Function for Change Schedule Type
    function changeScheduleType(modelID)
    {
        var sc_type = $('#'+modelID+' #schedule_type').val();
        if(sc_type == 'date')
        {
            $('#'+modelID+' .sc_date').show();
            $('#'+modelID+' .sc_time').hide();
        }
        else
        {
            $('#'+modelID+' .sc_date').hide();
            $('#'+modelID+' .sc_time').show();
        }
    }

</script>

@endsection
