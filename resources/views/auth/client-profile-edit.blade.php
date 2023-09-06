@extends('client.layouts.client-layout')

@section('title', __('Edit Profile'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Edit Profile')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">{{ __('Edit Profile')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Profile Section --}}
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

            {{-- Profile Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('client.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            <div class="container">
                                <div class="row mb-2">
                                    <h3>{{ __('User Details')}}</h3>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                                            <label for="firstname" class="form-label">{{ __('First Name')}}</label>
                                            <input type="text" name="firstname" id="firstname" class="form-control {{ ($errors->has('firstname')) ? 'is-invalid' : '' }}" value="{{ $user->firstname }}">
                                            @if($errors->has('firstname'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('firstname') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="lastname" class="form-label">{{ __('Last Name')}}</label>
                                            <input type="text" name="lastname" id="lastname" class="form-control" value="{{ $user->lastname }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="email" class="form-label">{{ __('Email')}}</label>
                                            <input type="text" name="email" id="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}" value="{{ $user->email }}">
                                            @if($errors->has('email'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('email') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @php
                                        $email_arr = isset($user['contact_emails']) ? $user['contact_emails'] : '';
                                        if($email_arr)
                                        {
                                            $unserialize_emails = unserialize($email_arr);
                                            $emails  = implode(",",$unserialize_emails);
                                        }
                                        else {
                                            $emails = '';
                                        }
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="contact_emails" class="form-label">{{ __('Contact Emails')}}</label>
                                            <input type="text" name="contact_emails" id="contact_emails" class="form-control" value="{{ $emails }}">
                                            <code>Notes</code> <br>
                                            <code>1) enter mail id's in this format : abc@gmail.com,xyz@gmail.com</code> <br>
                                            <code>2) Space Not Allowed after email</code>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="password" class="form-label">{{ __('Password')}}</label>
                                            <input type="password" name="password" id="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}" value="">
                                            @if($errors->has('password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">{{ __('Confirm Password')}}</label>
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control {{ ($errors->has('confirm_password')) ? 'is-invalid' : '' }}" value="">
                                            @if($errors->has('confirm_password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('confirm_password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="profile_picture" class="form-label">{{ __('Profile Picture')}}</label>
                                            <input type="file" name="profile_picture" id="profile_picture" class="form-control {{ ($errors->has('profile_picture')) ? 'is-invalid' : '' }}" value="">
                                            @if($errors->has('profile_picture'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('profile_picture') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Preview')}}</label>
                                            <div class="position-relative">
                                                @if(!empty($user->image))
                                                    <img src="{{ $user->image }}" class="w-100">
                                                    <a href="{{ route('client.delete.profile.picture') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a>
                                                @else
                                                    <img src="{{ asset('public/admin_images/not-found/not-found2.png') }}" width="100">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-2">
                                    <h3>{{ __('Shop Details')}}</h3>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="shop_name" class="form-label">{{ __('Shop Name')}}</label>
                                            <input type="text" name="shop_name" id="shop_name" class="form-control {{ ($errors->has('shop_name')) ? 'is-invalid' : '' }}" value="{{ isset($user->hasOneShop->shop['name']) ? $user->hasOneShop->shop['name'] : '' }}" placeholder="Enter Your Shop Name">
                                            @if($errors->has('shop_name'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('shop_name') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="shop_logo" class="form-label">{{ __('Shop Logo')}}</label>
                                            <input type="file" name="shop_logo" id="shop_logo" class="form-control {{ ($errors->has('shop_logo')) ? 'is-invalid' : '' }}">
                                            @if($errors->has('shop_logo'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('shop_logo') }}
                                                </div>
                                            @endif
                                        </div>
                                        <code class="mt-2">Upload Shop Logo (150*80) or (150*150)</code>
                                        @php
                                            $image = isset($user->hasOneShop->shop['logo']) ? $user->hasOneShop->shop['logo'] : '';
                                        @endphp
                                        <div class="row mt-5">
                                            <div class="col-md-4">
                                                <div class="form-group mt-2 position-relative">
                                                    @if(!empty($image))
                                                        <img class="w-100" src="{{ $image }}">
                                                        <a href="{{ route('shop.delete.logo') }}" class="btn btn-sm btn-danger" style="position: absolute; top: -35px; right: 0px;"><i class="bi bi-trash"></i></a>
                                                    @else
                                                        <img width="100" src="{{ asset('public/client_images/not-found/no_image_1.jpg') }}">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <label for="shop_description" class="form-label">{{ __('Shop Description')}}</label>
                                            <textarea name="shop_description" id="shop_description" rows="5" class="form-control text-editor">{{ isset($user->hasOneShop->shop['description']) ? $user->hasOneShop->shop['description'] : '' }}</textarea>
                                        </div>
                                    </div>
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

$(document).ready(function ()
{
    // Text Editor
    CKEDITOR.ClassicEditor.create(document.getElementById("shop_description"),
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
});


    </script>
@endsection
