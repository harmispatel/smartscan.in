@php
    $client_settings = getClientSettings();
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Mail Forms'))

@section('content')

    <section class="general_info_main">
        <div class="sec_title">
            <h2>{{ __('Mail Forms')}}</h2>
        </div>
        <div class="site_info">
            <form id="mailForms" action="{{ route('design.mailFormUpdate') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="orders_mail_form_client">{{ __('Orders Mail Form Owner') }}</label>
                            <textarea name="orders_mail_form_client" id="orders_mail_form_client" class="form-control editor">{{ (isset($client_settings['orders_mail_form_client']) && !empty($client_settings['orders_mail_form_client'])) ? $client_settings['orders_mail_form_client'] : '' }}</textarea>
                            <code>Tags : ({shop_logo}, {shop_name}, {firstname}, {lastname}, {order_id}, {order_type}, {payment_method}, {items}, {total})</code>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-group">
                            <label class="form-label" for="orders_mail_form_customer">{{ __('Orders Mail Form Customer') }}</label>
                            <textarea name="orders_mail_form_customer" id="orders_mail_form_customer" class="form-control editor">{{ (isset($client_settings['orders_mail_form_customer']) && !empty($client_settings['orders_mail_form_customer'])) ? $client_settings['orders_mail_form_customer'] : '' }}</textarea>
                            <code>Tags : ({shop_logo}, {shop_name}, {firstname}, {lastname}, {order_id}, {order_type}, {order_status}, {payment_method}, {items}, {total}, {estimated_time})</code>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="check_in_mail_form">{{ __('Check In Mail Form') }}</label>
                            <textarea name="check_in_mail_form" id="check_in_mail_form" class="form-control editor">{{ (isset($client_settings['check_in_mail_form']) && !empty($client_settings['check_in_mail_form'])) ? $client_settings['check_in_mail_form'] : '' }}</textarea>
                            <code>Tags : ({shop_logo}, {shop_name}, {firstname}, {lastname}, {phone}, {passport_no}, {room_no}, {nationality}, {age}, {address}, {arrival_date}, {departure_date}, {message})</code>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <button class="btn btn-success">{{ __('Update')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection


{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        // Toastr Settings
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            timeOut: 4000
        }

        // Success Message
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        $('.editor').each(function ()
        {
            CKEDITOR.ClassicEditor.create(document.getElementById($(this).prop('id')),
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

        // // Text Editor for Orders Mail Template
        // CKEDITOR.ClassicEditor.create(document.getElementById("order_mail_template"),
        // {
        //     toolbar: {
        //         items: [
        //             'heading', '|',
        //             'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
        //             'bulletedList', 'numberedList', 'todoList', '|',
        //             'outdent', 'indent', '|',
        //             'undo', 'redo',
        //             '-',
        //             'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
        //             'alignment', '|',
        //             'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
        //             'specialCharacters', 'horizontalLine', 'pageBreak', '|',
        //             'sourceEditing'
        //         ],
        //         shouldNotGroupWhenFull: true
        //     },
        //     list: {
        //         properties: {
        //             styles: true,
        //             startIndex: true,
        //             reversed: true
        //         }
        //     },
        //     'height':500,
        //     fontSize: {
        //         options: [ 10, 12, 14, 'default', 18, 20, 22 ],
        //         supportAllValues: true
        //     },
        //     htmlSupport: {
        //         allow: [
        //             {
        //                 name: /.*/,
        //                 attributes: true,
        //                 classes: true,
        //                 styles: true
        //             }
        //         ]
        //     },
        //     htmlEmbed: {
        //         showPreviews: true
        //     },
        //     link: {
        //         decorators: {
        //             addTargetToExternalLinks: true,
        //             defaultProtocol: 'https://',
        //             toggleDownloadable: {
        //                 mode: 'manual',
        //                 label: 'Downloadable',
        //                 attributes: {
        //                     download: 'file'
        //                 }
        //             }
        //         }
        //     },
        //     mention: {
        //         feeds: [
        //             {
        //                 marker: '@',
        //                 feed: [
        //                     '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
        //                     '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
        //                     '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
        //                     '@sugar', '@sweet', '@topping', '@wafer'
        //                 ],
        //                 minimumCharacters: 1
        //             }
        //         ]
        //     },
        //     removePlugins: [
        //         'CKBox',
        //         'CKFinder',
        //         'EasyImage',
        //         'RealTimeCollaborativeComments',
        //         'RealTimeCollaborativeTrackChanges',
        //         'RealTimeCollaborativeRevisionHistory',
        //         'PresenceList',
        //         'Comments',
        //         'TrackChanges',
        //         'TrackChangesData',
        //         'RevisionHistory',
        //         'Pagination',
        //         'WProofreader',
        //         'MathType'
        //     ]
        // });

    </script>

@endsection

