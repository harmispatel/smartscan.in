<?php

namespace App\Http\Controllers;

use App\Models\CategoryVisit;
use App\Models\Clicks;
use App\Models\Items;
use App\Models\ItemsVisit;
use App\Models\Order;
use App\Models\Shop;
use App\Models\UserVisits;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index($key="")
    {

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $date_arr = [];
        $user_visits_arr = [];
        $total_clicks_arr = [];
        $orders_arr = [];
        $today = Carbon::now();

        if($key == 'this_week')
        {
            $month = Carbon::now()->startOfWeek();
        }
        elseif($key == 'last_week')
        {
            $month = Carbon::now()->subWeek();
        }
        elseif($key == 'last_month')
        {
            $month = Carbon::now()->subMonth();
        }
        elseif($key == 'last_six_month')
        {
            $month = Carbon::now()->subMonth(6);
        }
        elseif($key == 'last_year')
        {
            $month = Carbon::now()->subYear();
        }
        elseif($key == 'lifetime')
        {
            $shop_details = Shop::find($shop_id);
            $month = isset($shop_details['created_at']) ? $shop_details['created_at'] : '';
        }
        else
        {
            $month = Carbon::now()->startOfWeek();
        }

        $month_array = CarbonPeriod::create($month, $today);

        if(count($month_array) > 0)
        {
            foreach($month_array as $dateval)
            {
                $date_arr[] = $dateval->format('d-m-Y');
                $user_visits = UserVisits::where('shop_id',$shop_id)->whereDate('created_at','=',$dateval->format('Y-m-d'))->count();
                $user_visits_arr[$dateval->format('d-m-Y')] = $user_visits;
                $clicks = Clicks::where('shop_id',$shop_id)->whereDate('created_at','=',$dateval->format('Y-m-d'))->first();
                $orders = Order::where('shop_id',$shop_id)->whereDate('created_at','=',$dateval->format('Y-m-d'))->count();
                $orders_arr[$dateval->format('d-m-Y')] = $orders;
                $total_clicks_arr[] = isset($clicks['total_clicks']) ? $clicks['total_clicks'] : '';
            };
        }

        // Most 5 Visited Category
        $data['category_visit'] = CategoryVisit::with(['category'])->where('shop_id',$shop_id)->orderByRaw("CAST(total_clicks as UNSIGNED) DESC")->limit(5)->get();

        // most visited Item
        $data['items_visit'] = ItemsVisit::with(['item'])->where('shop_id',$shop_id)->orderByRaw("CAST(total_clicks as UNSIGNED) DESC")->limit(5)->get();

        // Max Rated Items
        // $data['max_rated_items'] = Items::withCount('ratings')->withAvg('ratings', 'rating')->orderByDesc('ratings_count')->orderByDesc('ratings_avg_rating')->where('shop_id',$shop_id)->where('published',1)->limit(5)->get();
        $data['max_rated_items'] = Items::withCount('ratings')->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating')->where('shop_id',$shop_id)->where('published',1)->limit(5)->get();

        // Low Rated Items
        $data['low_rated_items'] = Items::withCount('ratings')->withAvg('ratings', 'rating')->orderBy('ratings_avg_rating')->where('shop_id',$shop_id)->where('published',1)->limit(5)->get();

        $data['current_key'] = $key;
        $data['date_array'] = $date_arr;
        $data['user_visits_array'] = $user_visits_arr;
        $data['orders_arr'] = $orders_arr;
        $data['total_clicks_array'] = $total_clicks_arr;

        return view('client.statistics.statistics',$data);
    }

}
