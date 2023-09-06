<?php

namespace App\Http\Controllers;

use App\Models\CustomerVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerVisits extends Controller
{
    public function index(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['StartDate'] = '';
        $data['EndDate'] = '';
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data['start_date'] = Carbon::now();
        $data['end_date'] = Carbon::now();

        $customer_visits = CustomerVisit::where('shop_id',$shop_id);

        if(!empty($start_date) && !empty($end_date))
        {
            $data['start_date'] = $start_date;
            $data['StartDate'] = $start_date;
            $data['end_date'] = $end_date;
            $data['EndDate'] = $end_date;

            $customer_visits = $customer_visits->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
        }

        $data['customer_visits'] = $customer_visits->get();

        return view('client.customers.customer_visit',$data);
    }
}
