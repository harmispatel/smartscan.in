@php
    $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : "";
    $primary_lang_details = clientLanguageSettings($shop_id);

    $language = getLangDetails(isset($primary_lang_details['primary_language']) ? $primary_lang_details['primary_language'] : '');
    $language_code = isset($language['code']) ? $language['code'] : '';
    $name_key = $language_code."_name";

    // Get Subscription ID
    $subscription_id = getClientSubscriptionID($shop_id);

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);
    $ordering = (isset($package_permissions['ordering'])) ? $package_permissions['ordering'] : 0;
@endphp

@extends('client.layouts.client-layout')

@section('title', __('Statistics'))

@section('content')

    <section class="statistics_main">
        <div class="sec_title">
            <h2>{{ __('Statistics')}}</h2>
        </div>
        <div class="row justify-content-end">
            <div class="col-md-3">
                <select name="date_filter" id="date_filter" class="form-select">
                    <option value="this_week" {{ ($current_key == 'this_week') ? 'selected' : '' }}>This Week</option>
                    <option value="last_week" {{ ($current_key == 'last_week') ? 'selected' : '' }}>Last Week</option>
                    <option value="last_month" {{ ($current_key == 'last_month') ? 'selected' : '' }}>Last Month</option>
                    <option value="last_six_month" {{ ($current_key == 'last_six_month') ? 'selected' : '' }}>Last Six Month</option>
                    <option value="last_year" {{ ($current_key == 'last_year') ? 'selected' : '' }}>Last Year</option>
                    <option value="lifetime" {{ ($current_key == 'lifetime') ? 'selected' : '' }}>LifeTime</option>
                </select>
            </div>
        </div>
        <div class="chart mb-3">

            <canvas id="lineChart" style="max-height: 400px; display: block; box-sizing: border-box; height: 221px; width: 442px;" width="442" height="221"></canvas>

        </div>

        <div class="most_viewed_sec">
            <div class="sec_title">
                <h3><i class="fa-solid fa-chart-line me-2"></i> {{ __('Most visited')}}</h3>
            </div>
            <div class="row">
                <div class="col-md-6 border-end">
                    <div class="viewed_category">
                        <h4>{{ __('Categories')}}</h4>
                        <ul class="viewed_ul">
                            @if(count($category_visit) > 0)
                                @php
                                    $key = 1;
                                @endphp
                                @foreach ($category_visit as $cat_visit)
                                    <li><b>{{ $key }}.</b> {{ isset($cat_visit->category[$name_key]) ? $cat_visit->category[$name_key] : '' }} ({{ $cat_visit['total_clicks'] }})</li>

                                    @php
                                        $key++;
                                    @endphp
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="viewed_Items">
                        <h4>{{ __('Items')}}</h4>
                        <ul class="viewed_ul">
                            @if(count($items_visit) > 0)
                                @php
                                    $item_key = 1;
                                @endphp
                                @foreach ($items_visit as $it_visit)
                                    <li><b>{{ $item_key }}.</b> {{ isset($it_visit->item[$name_key]) ? $it_visit->item[$name_key] : '' }} ({{ $it_visit['total_clicks'] }})</li>

                                    @php
                                        $item_key++;
                                    @endphp
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        <div class="most_viewed_sec mt-4">
            <div class="sec_title">
                <h3><i class="bi bi-star me-2"></i> {{ __('Ratings')}}</h3>
            </div>
            <div class="row">
                <div class="col-md-6 border-end">
                    <div class="viewed_category">
                        <h4>{{ __('High Rated Items')}}</h4>
                        <ul class="viewed_ul">
                            @if(count($max_rated_items) > 0)
                                @php
                                    $max_key = 1;
                                @endphp
                                @foreach ($max_rated_items as $item)
                                    @if(!empty($item['ratings_avg_rating']))
                                        @php
                                            $rating = number_format($item['ratings_avg_rating'],0);
                                        @endphp
                                        @if($rating >= 4)
                                            <li>
                                                <div>
                                                    <strong>{{ $max_key }})</strong> {{ isset($item[$name_key]) ? $item[$name_key] : '' }} ({{ $rating }})
                                                    <br>
                                                    <div class="rated">
                                                        @for($i=1; $i <= $rating; $i++)
                                                            <label class="star-rating-complete" title="text">{{$i}} stars</label>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        @php
                                            $max_key++;
                                        @endphp
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="viewed_Items">
                        <h4>{{ __('Low Rated Items')}}</h4>
                        <ul class="viewed_ul">
                            @if(count($low_rated_items) > 0)
                                @php
                                    $low_key = 1;
                                @endphp
                                @foreach ($low_rated_items as $item)
                                    @if(!empty($item['ratings_avg_rating']))
                                        @php
                                            $rating = number_format($item['ratings_avg_rating'],0);
                                        @endphp
                                        @if($rating <= 2)
                                            <li>
                                                <div>
                                                    <strong>{{ $low_key }})</strong> {{ isset($item[$name_key]) ? $item[$name_key] : '' }} ({{ $rating }})
                                                    <br>
                                                    <div class="rated">
                                                        @for($i=1; $i <= $rating; $i++)
                                                            <label class="star-rating-complete" title="text">{{$i}} stars</label>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        @php
                                            $low_key++;
                                        @endphp
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')
    <script type="text/javascript">

        const date_arr = {{ Js::from($date_array) }};
        const user_visit_arr = {{ Js::from($user_visits_array) }};
        const total_clicks_arr = {{ Js::from($total_clicks_array) }};
        const orders_arr = {{ Js::from($orders_arr) }};
        const ordering = @json($ordering);

        var chartArr = [
            {
                label: 'Clicks',
                data: total_clicks_arr,
                fill: false,
                borderColor: 'green',
                tension: 0.1,
            },
            {
                label: 'Users',
                data: user_visit_arr,
                fill: false,
                borderColor: 'red',
                tension: 0.1
            }
        ];

        if(ordering == 1)
        {
            var ordArr = {
                label: 'Orders',
                data: orders_arr,
                fill: false,
                borderColor: 'blue',
                tension: 0.1
            };
            chartArr.push(ordArr);
        }

        // Chart
        document.addEventListener("DOMContentLoaded", () =>
        {
            new Chart(document.querySelector('#lineChart'), {
                type: 'line',
                data: {
                    labels: date_arr,
                    datasets: chartArr
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        // Function for Filter with Key
        $('#date_filter').on('change',function()
        {
            var filter_key = $(this).val();
            var Url = "{{ route('statistics') }}";
            location.href = Url+"/"+filter_key;
        });

    </script>

@endsection


