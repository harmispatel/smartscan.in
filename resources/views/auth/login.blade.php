@php
    $settings = getAdminSettings();
    $form_background = isset($settings['login_form_background']) ? $settings['login_form_background'] : '';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Smart QR SCAN | Login</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('public/admin_images/favicons/smartqrscan.ico') }}" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('public/admin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('public/admin/assets/css/custom.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('public/admin/assets/vendor/css/style.css') }}" rel="stylesheet">

    @if(!empty($form_background))
        <style>
            .bg_login {
                background: url({{ $form_background }});
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
            }
        </style>
    @endif


</head>

<body>
    <main>
        <div class="bg_login">
            <div class="container">
                <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                                {{-- <div class="d-flex justify-content-center py-4">
                                    <a href="index.html" class="logo d-flex align-items-center w-auto">
                                        <img src="assets/img/logo.png" alt="">
                                        <span class="d-none d-lg-block">NiceAdmin</span>
                                    </a>
                                </div> --}}

                                @if (session()->has('error'))
                                    <div class="col-md-12 mt-2">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>
                                @endif

                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                            <p class="text-center small">Enter your email & password to login</p>
                                        </div>

                                        <form class="row g-3" method="POST" action="{{ route('doLogin') }}">
                                            @csrf
                                            <div class="col-12">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group">
                                                    <input type="text" name="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}" id="email">
                                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                    @if($errors->has('email'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('email') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label for="password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}"
                                                    id="password">
                                                    <span class="input-group-text" style="cursor: pointer;" onclick="ShowHidePassword()" id="passIcon">
                                                        <i class="bi bi-eye-slash"></i>
                                                    </span>
                                                    @if($errors->has('password'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('password') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button class="btn btn-primary w-100" type="submit">Login</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>

            </div>
        </div>
    </main>

    <!-- Vendor JS Files -->
    <script src="{{ asset('public/admin/assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('public/admin/assets/vendor/js/main.js') }}"></script>

    {{-- Jquery --}}
    <script src="{{ asset('public/admin/assets/vendor/js/jquery.min.js') }}"></script>

    {{-- Custom Script --}}
    <script type="text/javascript">

        // Show & Hide Password
        function ShowHidePassword()
        {
            var currentType = $('#password').attr('type');
            if (currentType == 'password')
            {
                $('#password').attr('type', 'text');
                $('#passIcon').html('');
                $('#passIcon').append('<i class="bi bi-eye"></i>');
            }
            else
            {
                $('#password').attr('type', 'password');
                $('#passIcon').html('');
                $('#passIcon').append('<i class="bi bi-eye-slash"></i>');
            }
        }

    </script>

</body>
</html>
