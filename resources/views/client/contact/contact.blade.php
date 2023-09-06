@extends('client.layouts.client-layout')

@section('title', __('Contact'))

@section('content')

    <section class="contact_main">
        <div class="sec_title">
            <h2>{{ __('Contact US')}}</h2>
        </div>
            <form action="{{route('contact.send')}}" class="form" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('Subject')}}</label>
                            <input class="form-control {{ $errors->has('title') ? 'is-invalid' : ''}}" type="text" name="title">
                            @if($errors->has('title'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('title') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('Message')}}</label>
                            <textarea name="message" id="message" class="form-control {{ $errors->has('message') ? 'is-invalid' : ''}}"></textarea>
                            @if($errors->has('message'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('message') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-success">{{ __('Send')}}</button>
                    </div>
                </div>
        </form>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')

    <script type="text/javascript">

        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

    </script>

@endsection


