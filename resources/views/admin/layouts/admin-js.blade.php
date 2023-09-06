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

{{-- Sweet Alert --}}
<script src="{{ asset('public/admin/assets/vendor/js/sweet-alert.js') }}"></script>

{{-- Data Table --}}
<script src="{{ asset('public/admin/assets/vendor/simple-datatables/simple-datatables.js') }}"></script>

{{-- Toastr --}}
<script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

<!-- Select 2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Ckeditor --}}
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/super-build/ckeditor.js"></script>

{{-- apexchart --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<script type="text/javascript">

    function gotoTilesView()
    {
        window.location.href = "{{ route('clients')}}";
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
