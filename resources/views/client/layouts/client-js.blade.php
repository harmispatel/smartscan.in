<!-- Vendor JS Files -->
<script src="{{ asset('public/admin/assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/echarts/echarts.min.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/quill/quill.min.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/php-email-form/validate.js') }}"></script>
<script src="{{ asset('public/admin/assets/vendor/tinymce/tinymce.min.js') }}"></script>

<!-- Template Main JS File -->
<script src="{{ asset('public/admin/assets/vendor/js/main.js') }}"></script>


{{-- Jquery --}}
<script src="{{ asset('public/admin/assets/vendor/js/jquery.min.js') }}"></script>

{{-- Jquery UI --}}
<script src="{{ asset('public/admin/assets/vendor/js/jquery-ui.js') }}"></script>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js" integrity="sha512-0bEtK0USNd96MnO4XhH8jhv3nyRF0eK87pJke6pkYf3cM0uDIhNJy9ltuzqgypoIFXw3JSuiy04tVk4AjpZdZw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}

{{-- Sweet Alert --}}
<script src="{{ asset('public/admin/assets/vendor/js/sweet-alert.js') }}"></script>

{{-- Data Table --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Toastr --}}
<script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

<!-- Select 2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Cropper JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" integrity="sha512-6lplKUSl86rUVprDIjiW8DuOniNX8UDoRATqZSds/7t6zCQZfaCe3e5zcGaQwxa8Kpn5RTM9Fvl3X2lLV4grPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Ckeditor --}}
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/super-build/ckeditor.js"></script>

<script src="https://howlerjs.com/assets/howler.js/dist/howler.min.js"></script>

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

{{-- Date Range Picker --}}
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script type="text/javascript">

    $(document).ready(function ()
    {
        OrderNotification();
    });

    setInterval(() => {
        OrderNotification();
    }, 10000);

    function OrderNotification()
    {
        $.ajax({
            type: "POST",
            url: "{{ route('order.notification') }}",
            data: {
                "_token" : "{{ csrf_token() }}",
            },
            dataType: "JSON",
            success: function (response)
            {
                if(response.success == 1)
                {
                    if(response.count > 0)
                    {
                        $('.noti-message').html('');
                        $('.noti-message').append(response.data);
                        $('.noti-count').html('');
                        $('.noti-count').append(response.count);

                        var play_sound = $('#play_sound').val();
                        var notification_sound = $('#notification_sound').val();

                        if(play_sound == 1)
                        {
                            var sound = new Howl({
                                src: [notification_sound],
                                autoplay : true,
                            });
                            $('#myHiddenButton').trigger("click");
                        }
                    }
                    else
                    {
                        $('.noti-message').html('');
                        $('.noti-message').append(response.data);
                        $('.noti-count').html('');
                        $('.noti-count').append(response.count);
                    }
                }
            }
        });
    }


    function previewMyShop(shopSlug)
    {
        var preUrl = "{{ url('/') }}/"+shopSlug;
        $('#previewModal iframe').attr('src',preUrl);
        $('#previewModal').modal('show');
    }


    // Change Admin Language
    function changeBackendLang(langCode)
    {
        $.ajax({
            type: "POST",
            url: "{{ route('change.backend.language') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "langCode": langCode,
            },
            dataType: "JSON",
            success: function(response) {
                if (response.success == 1)
                {
                    location.reload();
                }
            }
        });
    }


</script>

