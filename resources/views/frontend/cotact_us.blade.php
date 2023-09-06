<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Smart QR SCAN</title>
    <link href="{{ asset('public/admin_images/favicons/smartqrscan.ico') }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,400;0,500;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/vendor/css/toastr.min.css') }}">
    <!--<link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/css/swiper.min.css" rel="stylesheet">
    <!--<link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css" rel="stylesheet">-->
    <link rel="stylesheet" href="{{ asset('public/frontend/css/frontend.css') }}" >

    <style>

    </style>
</head>
<body>
    <div class="login-page">

        <!-- Header -->
        <header class="header bg-white">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <a class="navbar-brand" href="{{ URL::to('/') }}"><img src="{{ asset('public/admin_images/logos/smart_qr_logo.gif') }}" height="80px"></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                      <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::currentRouteName() == 'home') ? 'active' : '' }}" aria-current="page" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::currentRouteName() == 'qr.guide') ? 'active' : '' }}" aria-current="page" href="{{ route('qr.guide') }}">QR Guide</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::currentRouteName() == 'qr.menu') ? 'active' : '' }}" aria-current="page" href="{{ route('qr.menu') }}">QR Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::currentRouteName() == 'contact.us') ? 'active' : '' }}" aria-current="page" href="{{ route('contact.us') }}">Contact</a>
                        </li>
                       <li>
                            <a href="{{ route('login') }}" class="btn text-white" style="background-color:#2498bd; margin-left:10px;"><i class="fa fa-user"></i> <strong>Login</strong></a>
                       </li>
                      </ul>
                    </div>
                </nav>
            </div>
        </header>

        <!-- contact banner -->
        <section class="contact_banner">
            <h2>Contact Us</h2>
        </section>


        <!-- Contact -->
        <section class="contact_section">
            <div class="container">
                <div class="sec_title">
                    <!--<h2>Contact Us</h2>-->
                    <p>Interested to learn more or having any questions?</p>
                    <p>Phone us at +91 99090 16746 or fill out the contact form below and a member of our team will come back to you soon!</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="contact_form">
                            <form method="POST" action="{{ route('contact.us.mail') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Name :</label>
                                            <input class="form-control" type="text" name="name" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Bussiness Name :</label>
                                            <input class="form-control" type="text" name="bussiness_name" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Mobile No :</label>
                                            <input class="form-control" type="number" name="mobile_number" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email :</label>
                                            <input class="form-control" type="text" name="email" required />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Message :</label>
                                            <textarea class="form-control"  rows="5" type="text" name="message" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center mt-3">
                                        <div class="form-group">
                                            <button class="btn sub-bt">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="footer_logo text-center">
                            <img src="{{ asset('public/admin_images/logos/smart_qr_logo.gif') }}" height="100"/>

                            <ul class="social_icon">
                                <li><a href="#"><i class="fa-brands fa-square-facebook"></i></a></li>
                                <li><a href="#"><i class="fa-brands fa-square-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="footer_contact text-center">
                            <ul>
                                <li>
                                   <p>B-304/2 Gopal palace, Near Shiromani complex, Opposite Ocean Park, Nehrunagar, Ahmedabad,Gujarat - 380015,India</p>
                                </li>
                                <li>
                                   <p><strong>Mobile No : </strong>99090 16746</p>
                                </li>
                                <li>
                                   <a href="mailto:info@smartqrscan.com" class="text-decoration-none"><strong>Email : </strong>info@smartqrscan.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <div class="copy_right">
            <p>©Copyright 2023. All rights reserved by Smartqrscan.</p>
        </div>

    </div>

    <script src="{{ asset('public/client/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/js/swiper.min.js"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1H7NBQJCND"></script>

     <script type="text/javascript">

        // Google tag
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-1H7NBQJCND');

        // Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            timeOut: 4000
        }

        // Error Messages
        @if (Session::has('error'))
            toastr.error('{{ Session::get('error') }}')
        @endif

        // Success Message
        @if (Session::has('success'))
            toastr.success('{{ Session::get('success') }}')
        @endif

    </script>

</body>
</html>
