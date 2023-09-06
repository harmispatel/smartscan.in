@extends('admin.layouts.admin-layout')

@section('title',__('Settings'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Settings')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Settings')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Sttings Section --}}
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

            {{-- Settings Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('update.admin.settings') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            @csrf

                            {{-- Fav Clients Limit --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Favourites Clients Limit')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="favourite_client_limit" class="form-control {{ ($errors->has('favourite_client_limit')) ? 'is-invalid' : '' }}" value="{{ isset($settings['favourite_client_limit']) ? $settings['favourite_client_limit'] : '' }}">
                                    @if($errors->has('favourite_client_limit'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('favourite_client_limit') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- CopyRight Text --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Copyright')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <textarea name="copyright_text" id="copyright_text" rows="5" class="form-control {{ ($errors->has('copyright_text')) ? 'is-invalid' : '' }}">{{ isset($settings['copyright_text']) ? $settings['copyright_text'] : '' }}</textarea>
                                    @if($errors->has('copyright_text'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('copyright_text') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Logo --}}
                            @php
                                $logo = isset($settings['logo']) ? $settings['logo'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Logo')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="logo" class="form-control {{ ($errors->has('logo')) ? 'is-invalid' : '' }}">
                                    <code>Max Dimensions of Logo (150*50)</code>
                                     @if($errors->has('logo'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('logo') }}
                                        </div>
                                    @endif
                                    @if(!empty($logo))
                                        <div class="mt-3">
                                            <img src="{{ $logo }}" alt="" width="150">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @php
                                $login_bg = isset($settings['login_form_background']) ? $settings['login_form_background'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Login Form Background')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="login_form_background" class="form-control {{ ($errors->has('login_form_background')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('login_form_background'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('login_form_background') }}
                                        </div>
                                    @endif
                                    @if(!empty($login_bg))
                                        <div class="mt-3">
                                            <img src="{{ $login_bg }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Default Light Theme Image --}}
                            @php
                                $light_img = isset($settings['default_light_theme_image']) ? $settings['default_light_theme_image'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Default Light Theme Image')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="default_light_theme_image" id="default_light_theme_image" class="form-control {{ ($errors->has('default_light_theme_image')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('default_light_theme_image'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('default_light_theme_image') }}
                                        </div>
                                    @endif
                                    @if(!empty($light_img))
                                        <div class="mt-3">
                                            <img src="{{ $light_img }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Default Dark Theme Image --}}
                            @php
                                $dark_img = isset($settings['default_dark_theme_image']) ? $settings['default_dark_theme_image'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Default Dark Theme Image')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="default_dark_theme_image" id="default_dark_theme_image" class="form-control {{ ($errors->has('default_dark_theme_image')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('default_dark_theme_image'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('default_dark_theme_image') }}
                                        </div>
                                    @endif
                                    @if(!empty($dark_img))
                                        <div class="mt-3">
                                            <img src="{{ $dark_img }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Main Screen Map --}}
                            @php
                                $theme_main_screen = isset($settings['theme_main_screen_demo']) ? $settings['theme_main_screen_demo'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Main Screen Map')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="theme_main_screen_demo" id="theme_main_screen_demo" class="form-control {{ ($errors->has('theme_main_screen_demo')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('theme_main_screen_demo'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('theme_main_screen_demo') }}
                                        </div>
                                    @endif
                                    @if(!empty($theme_main_screen))
                                        <div class="mt-3">
                                            <img src="{{ $theme_main_screen }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Product Screen Map --}}
                            @php
                                $theme_category_screen = isset($settings['theme_category_screen_demo']) ? $settings['theme_category_screen_demo'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Product Screen Map')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="theme_category_screen_demo" id="theme_category_screen_demo" class="form-control {{ ($errors->has('theme_category_screen_demo')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('theme_category_screen_demo'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('theme_category_screen_demo') }}
                                        </div>
                                    @endif
                                    @if(!empty($theme_category_screen))
                                        <div class="mt-3">
                                            <img src="{{ $theme_category_screen }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Day Special Image --}}
                            @php
                                $default_special_item_image = isset($settings['default_special_item_image']) ? $settings['default_special_item_image'] : '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Day Special Image')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="default_special_item_image" id="default_special_item_image" class="form-control {{ ($errors->has('default_special_item_image')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('default_special_item_image'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('default_special_item_image') }}
                                        </div>
                                    @endif
                                    @if(!empty($default_special_item_image))
                                        <div class="mt-3">
                                            <img src="{{ $default_special_item_image }}" alt="" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- New Language Section --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('New Language')}}</b>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="text" name="lang_name" id="lang_name" class="form-control" placeholder="Enter Language Name">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="text" name="lang_code" id="lang_code" class="form-control" placeholder="Enter Language Code">
                                </div>
                                <div class="col-md-2">
                                    <a class="btn btn-success" onclick="addNewLanguage()"><i class="bi bi-save"></i></a>
                                </div>
                            </div>


                            {{-- Languages Section --}}
                            @php
                                $languages_array = [];
                                foreach ($languages as $key => $value) {
                                    $languages_array[] = $value->id;
                                }
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Languages')}}</b>
                                </div>
                                <div class="col-md-6">
                                    <select name="languages[]" id="languages" class="form-control {{ ($errors->has('languages')) ? 'is-invalid' : '' }}" multiple>
                                        @if(count($languages) > 0)
                                            @foreach ($languages as $language)
                                                <option value="{{ $language->id }}" {{ (in_array($language->id,$languages_array)) ? 'selected' : '' }}>{{ $language->name }} ({{ $language->code }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if($errors->has('languages'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('languages') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @php
                                $email_arr = isset($settings['contact_us_email']) ? $settings['contact_us_email'] : '';
                                if($email_arr)
                                {
                                    $unserialize_emails = unserialize($email_arr);
                                    $emails  = implode(",",$unserialize_emails);
                                }
                                else {
                                    $emails = '';
                                }
                            @endphp
                            {{-- Fav Clients Limit --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Support mails')}}</b>
                                </div>
                                <div class="col-md-6">
                                <input type="text" name="contact_us_email" class="form-control {{ ($errors->has('contact_us_email')) ? 'is-invalid' : '' }}" value="{{ $emails }}">
                                <code>Notes</code> <br>
                                <code>1) enter mail id's in this format : abc@gmail.com,xyz@gmail.com</code> <br>
                                <code>2) Space Not Allowed after email</code>
                                    @if($errors->has('contact_us_email'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('contact_us_email') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Support Mail Form') }}</b>
                                </div>
                                <div class="col-md-6">
                                    <textarea name="contact_us_mail_template" id="contact_us_mail_template" class="form-control">{{ isset($settings['contact_us_mail_template']) ? $settings['contact_us_mail_template'] : '' }}</textarea>
                                    <code>Tags : ({shop_name}, {shop_logo}, {subject}, {message})</code>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Subscription Expire Mail Form') }}</b>
                                </div>
                                <div class="col-md-6">
                                    <textarea name="subscription_expire_mail" id="subscription_expire_mail" class="form-control">{{ isset($settings['subscription_expire_mail']) ? $settings['subscription_expire_mail'] : '' }}</textarea>
                                    <code>Tags : ({shop_name}, {shop_logo}, {expiry_date})</code>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('1st notification email') }}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="days_for_send_first_expiry_mail" id="days_for_send_first_expiry_mail" value="{{ isset($settings['days_for_send_first_expiry_mail']) ? $settings['days_for_send_first_expiry_mail'] : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('2nd notification email') }}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="days_for_send_second_expiry_mail" id="days_for_send_second_expiry_mail" value="{{ isset($settings['days_for_send_second_expiry_mail']) ? $settings['days_for_send_second_expiry_mail'] : '' }}" class="form-control">
                                </div>
                            </div>
                            @php
                                $expiry_email_arr = isset($settings['subscription_expiry_mails']) ? $settings['subscription_expiry_mails'] : '';
                                if($expiry_email_arr)
                                {
                                    $unserialize_expiry_emails = unserialize($expiry_email_arr);
                                    $expiry_emails  = implode(",",$unserialize_expiry_emails);
                                }
                                else {
                                    $expiry_emails = '';
                                }
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Admins Recipient email')}}</b>
                                </div>
                                <div class="col-md-6">
                                <input type="text" name="subscription_expiry_mails" class="form-control {{ ($errors->has('subscription_expiry_mails')) ? 'is-invalid' : '' }}" value="{{ $expiry_emails }}">
                                <code>Notes</code> <br>
                                <code>1) enter mail id's in this format : abc@gmail.com,xyz@gmail.com</code> <br>
                                <code>2) Space Not Allowed after email</code>
                                    @if($errors->has('subscription_expiry_mails'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('subscription_expiry_mails') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <b>{{ __('Google Map API Key') }}</b>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="google_map_api" id="google_map_api" class="form-control" value="{{ isset($settings['google_map_api']) ? $settings['google_map_api'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success">{{ __('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')
    <script type="text/javascript">

        // Select 2
        $("#languages").select2();

        // Add New Language
        function addNewLanguage()
        {
            var lang_name = $('#lang_name').val();
            var lang_code = $('#lang_code').val();

            if(lang_name == '' && lang_code == '')
            {
                alert('Please Enter Language Name or Code!');
                return false;
            }
            else
            {
                $.ajax({
                    type: "POST",
                    url: "{{ route('languages.save.ajax') }}",
                    data: {
                        "_token" : "{{ csrf_token() }}",
                        "name" : lang_name,
                        "code" : lang_code,
                    },
                    dataType: "JSON",
                    success: function (response)
                    {
                        if(response.success == 1)
                        {
                            toastr.success(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                        else
                        {
                            toastr.error(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    }
                });
            }

        }


        // CKEditor for Cotactus Mail Template
        CKEDITOR.ClassicEditor.create(document.getElementById("contact_us_mail_template"),
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
        });

        // CKEditor for Subscription Expire Mail Template
        CKEDITOR.ClassicEditor.create(document.getElementById("subscription_expire_mail"),
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
        });

    </script>
@endsection
