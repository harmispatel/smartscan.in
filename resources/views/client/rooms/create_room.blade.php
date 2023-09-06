@extends('client.layouts.client-layout')

@section('title',__('New Rooms'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Rooms')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('rooms') }}">{{ __('Rooms')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('New Rooms')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('rooms') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- New Room add Section --}}
    <section class="section dashboard">
        <div class="row">

            {{-- Room Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('rooms.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                        <div class="card-body">
                            <div class="card-title">
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="start_room_number" class="form-label">Start Room Number</label>
                                        <input type="number" name="start_room_number" id="start_room_number" class="form-control {{ ($errors->has('start_room_number')) ? 'is-invalid' : '' }}">
                                        @if($errors->has('start_room_number'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('start_room_number') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="end_room_number" class="form-label">End Room Number</label>
                                        <input type="number" name="end_room_number" id="end_room_number" class="form-control {{ ($errors->has('end_room_number')) ? 'is-invalid' : '' }}">
                                        @if($errors->has('end_room_number'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('end_room_number') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success">{{ __('Save')}}</button>
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

    </script>
@endsection
