@extends('admin.layouts.admin-layout')

@section('title',__('Food Junctions'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Create Food Junction')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('food.junctions') }}">{{ __('Food Junctions')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Create Food Junction')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4" style="text-align: right;">
                <a href="{{ route('food.junctions') }}" class="btn btn-sm new-amenity btn-primary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- New Food Junction add Section --}}
    <section class="section dashboard">
        <div class="row">
            {{-- Food Junction Card --}}
            <div class="col-md-12">
                <div class="card">
                    <form class="form" action="{{ route('food.junctions.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ __('Junction Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control {{ ($errors->has('name')) ? 'is-invalid' : '' }}" placeholder="Enter Junction Name">
                                    @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="junction_slug" class="form-label">{{ __('Junction Slug') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="junction_slug" id="junction_slug" class="form-control {{ ($errors->has('junction_slug')) ? 'is-invalid' : '' }}" placeholder="Enter Junction Slug Like : demo-junction">
                                    @if($errors->has('junction_slug'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('junction_slug') }}
                                        </div>
                                    @endif
                                    <code>Enter Junction Slug in Only String and also Hyphen Allowed.</code>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label">{{ __('Description') }} </label>
                                    <textarea name="description" id="description" rows="4" class="form-control" placeholder="Write Junction Description Here...."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="shops" class="form-label">{{ __('Shops') }} <span class="text-danger">*</span></label>
                                    <select name="shops[]" id="shops" class="form-select {{ ($errors->has('shops')) ? 'is-invalid' : '' }}" multiple>
                                        @if(count($shops) > 0)
                                            @foreach ($shops as $shop)
                                                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if($errors->has('shops'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('shops') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="junction_logo" class="form-label">{{ __('Junction Logo') }}</label>
                                    <input type="file" name="junction_logo" id="junction_logo" class="form-control {{ ($errors->has('junction_logo')) ? 'is-invalid' : '' }}">
                                    @if($errors->has('junction_logo'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('junction_logo') }}
                                        </div>
                                    @endif
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
        // Error Messages
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        // Success Messages
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

        // Select 2
        $("#shops").select2({
            placeholder: "Choose Shops",
        });

    </script>
@endsection
