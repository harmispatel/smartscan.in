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

        <!-- Banner -->
        <section class="banner">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="site-info">
                            <h1>Create Your Own QR Menu For Your <span style="color:#2498bd"> Restaurant!</span></h1>
                            <p>Customers may easily access the <strong>restaurant digital menu</strong> by searching online. With an <strong>online menu maker</strong>, you can create eye-catching digital menu for your restaurant.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                      <div class="banner_text">
                        <div class="qr_img">
                            <img src="{{ asset('public/admin_images/backgrounds/smart_logo.png') }}" >
                            <img src="{{ asset('public/admin_images/backgrounds/moblie_img.png') }}" >
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Virtual Menu -->
        <section class="virtual_menu">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="rest-clipart site-info">
                  <h1>Virtual Menu</span></h1>
                      <p>The electronic catalog for catering businesses unlocks new possibilities for a modern, fast and quality service to your customers.</p>
                      <p>
                          Ideal for: Cafe, Bar, Bistro, Restaurants, Taverns, Beach Bar, Playgrounds.
                      </p>

                      <div class="virtual_menu_btn">
                        <a class="btn btn-info" href="https://smartqrscan.com/smartqrscandemo" target="_blank" role="button">See The Demo</a>
                      </div>
                </div>
              </div>

              <div class="col-md-6">
                  <div class="site-info text-center">
                      <!--<img src="{{ asset('public/admin_images/backgrounds/qr_restarant_bg.jpg') }}" class="w-100">-->
                      <video width="317" height="637" loop="" muted="" playsinline="" autoplay="">
                          <source src="{{ asset('public/frontend/video/smartqrscan_menu.mp4') }}" type="video/mp4">
                      </video>
                  </div>
              </div>

            </div>
          </div>
        </section>

        <!-- Website Steps -->
        <section>
          <div class="sec_main">
            <div class="container">
              <div class="row steps_content">
                <div class="col-md-4">
                  <div class="steps_content_detail">
                    <div class="icon">
                      <i class="fa-solid fa-image"></i>
                    </div>
                    <h3>STEP 01</h3>
                    <p>Send logo png, jpeg, psd, CI, PDF ( Big size and clear display)</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="steps_content_detail">
                    <div class="icon">
                      <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <h3>STEP 02</h3>
                    <p>Send your current menu or catalog in PDF or Document or JPEG</p>
                  </div>
                </div>
                <div class="col-md-4">
                    <div class="steps_content_detail">
                        <div class="icon">
                          <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <h3>STEP 03</h3>
                        <p>Send you QR Code print it and USE IT.</p>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section>
            <div class="main">
                <div class="container">
                    <div class="sec_title">
                        <h2>Why Choose SmartQrScan?</h2>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <div class="choose-info">

                            <p>Are you tired of customers calling your hotel for room service requests, leading to phone congestion and potential miscommunication or errors in manually taking orders? SmartQrscan offers an easy solution. We provide a QR code for each room, allowing customers to scan it and place orders directly from their rooms. These orders are sent directly to your kitchen or POS system, ensuring accurate and efficient processing.</p>

                            <p>Our services extend beyond room service. We also offer table service for your restaurant. By placing QR codes on each table, customers can conveniently place their orders, which are then sent directly to the kitchen.</p>

                            <p>If you offer food delivery outside the hotel, our system can manage that too. You have full control over the delivery areas and timings through our backend system.</p>

                            <p>In addition to the above, we also offer a take-away system for your convenience.</p>

                            <p>Our system provides a fully customizable and dynamic menu. You can easily personalize the colors and theme to match your preferences.</p>

                            <p>Rest assured that our system is 100% responsive, ensuring seamless functionality across devices.</p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Where to use  -->
        <section>
            <div class="sec_main">
            <div class="container">
              <div class="row use_content">
                <div class="col-md-6">
                    <div class="use_content_column1">
                      <h3>Where is Smart QR scan usefully?</h3>

                      <div class="use_content_detail">
                        <div class="icon">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                        <div class="data">
                          <h4>RESTAURANTS</h4>
                          <p>Smart QR Scan is suitable for all restaurants business moreover Online Delivery and Table service is available!.
                            <br />Also we have take away inside..</p>
                        </div>
                      </div>

                      <div class="use_content_detail">
                        <div class="icon">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <div class="data">
                          <h4>HOTELS</h4>
                          <p>Smart QR Scan is suitable for all hotels enterprises moreover Room Delivery service is available!</p>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="use_content_column2">
                        <img src="{{ asset('public/admin_images/backgrounds/resturant_img.jpeg') }}" />
                    </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Features -->
        <section>
          <div class="main">
              <div class="container">
              <div class="row use_content">
                <div class="col-md-6">
                    <div class="use_content_column2">
                        <img src="{{ asset('public/admin_images/backgrounds/featured_new.JPG') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="use_content_column1">
                      <h3>FEATURED SERVICES</h3>
                      <p>Smart QR Scan</p>

                      <div class="features">
                        <ul>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Instant price changes</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Add new categories and products</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Activate/deactivate products based on availability</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Add photos in categories & product</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Display menu visitor statistics</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Change menu colors</span></li>
                          <li><i class="icon fa-regular fa-square-check"></i><span>Responsive</span></li>
                        </ul>
                      </div>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Us -->
        <section class="contact_section">
            <div class="container">
                <div class="sec_title">
                    <h2>Contact Us</h2>
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
                                    <div class="col-md-12 text-center">
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

        <!-- Recent Stores -->
        @if(count($shops) > 0)
            <section class="brand_slider">
                <div class="brand_slider_title">
                    <h2>Recently Registered Stores</h2>
                </div>
                 <div class="swiper-container">
                	<ul class="swiper-wrapper align-items-center">
                	    @foreach($shops as $shop)
                    		<li class="swiper-slide">
                    		    <a href="{{ route('restaurant',$shop->shop_slug) }}" target="_blank"><img src="{{ $shop->logo }}" alt="{{ $shop->name }}"></a>
                    		</li>
                		@endforeach
                	</ul>
                </div>
            </section>
        @endif

        <!-- Footer -->
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

        <!-- Copyright -->
        <div class="copy_right">
            <p>©Copyright 2023. All rights reserved by Smartqrscan.</p>
        </div>

    </div>

    <script src="{{ asset('public/client/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/js/swiper.min.js"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1H7NBQJCND"></script>

    <script type="text/javascript">

        // Google tag
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-1H7NBQJCND');


        $('.brand-carousel').owlCarousel({
          loop:false,
          margin:10,
          autoplay:true,
          responsive:{
            0:{
              items:1
            },
            600:{
              items:1
            },
            1000:{
              items:5
            }
          }
        });

        var Swiper = new Swiper('.swiper-container', {
        	direction: 'horizontal',
        	visibilityFullFit: true,
        	loop: true,
        	autoplay: 4000,
        	slidesPerView: 5,
        	grabCursor: true,
        	autoplayDisableOnInteraction: false,
        	speed: 2000,
        	breakpoints: {
        		480: {
        			slidesPerView: 1
        		},
        		740: {
        			slidesPerView: 1
        		},
        		960: {
        			slidesPerView: 3
        		},
        		1280: {
        			slidesPerView: 4
        		}
        	}
        })


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
