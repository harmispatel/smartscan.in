@php
    $current_year = \Carbon\Carbon::now()->format('Y');
    $settings = getAdminSettings();
    $copyright_text = isset($settings['copyright_text']) ? $settings['copyright_text'] : '';
    $copyright_text = str_replace('[year]',$current_year,$copyright_text);
@endphp

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
    <div class="copyright">
        {{-- &copy; Copyright <strong><span>Wifi Catalogue</span></strong>. All Rights Reserved --}}
        {!! $copyright_text !!}
    </div>
    {{-- <div class="credits">
        Designed by <a href="#" target="_blank">Wifi Catalogue</a>
    </div> --}}
</footer><!-- End Footer -->
