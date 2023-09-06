<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link href="{{ asset('public/admin_images/favicons/smartqrscan.ico') }}" rel="icon">
    @include('admin.layouts.admin-css')
</head>

<body>
    {{-- Navbar --}}
    @include('admin.layouts.admin-navbar')

    {{-- Sidebar --}}
    @include('admin.layouts.admin-sidebar')

    {{-- Main Content --}}
    <main id="main" class="main">
        @yield('content')
    </main>
    <!-- End #main -->

    {{-- Footer --}}
    @include('admin.layouts.admin-footer')

    {{-- Uplink --}}
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    {{-- Admin JS --}}
    @include('admin.layouts.admin-js')

    @yield('page-js')

</body>

</html>
