<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAreas;
use App\Models\Order;
use App\Models\OrderSetting;
use App\Models\UserShop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Magarrent\LaravelCurrencyFormatter\Facades\Currency;

class OrderController extends Controller
{
    // Function for Display Client Orders
    public function index()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['orders'] = Order::where('shop_id',$shop_id)->whereIn('order_status',['pending','accepted'])->orderBy('id','DESC')->get();

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
        {
            return view('client.orders.orders',$data);
        }
        else
        {
            return redirect()->route('client.dashboard')->with('error','Unauthorized Action!');
        }
    }


    // Function for Get newly created order
    public function getNewOrders()
    {
        $html = '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $shop_settings = getClientSettings($shop_id);
        // Shop Currency
        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Order Settings
        $order_setting = getOrderSettings($shop_id);
        $auto_print = (isset($order_setting['auto_print']) && !empty($order_setting['auto_print'])) ? $order_setting['auto_print'] : 0;
        $enable_print = (isset($order_setting['enable_print']) && !empty($order_setting['enable_print'])) ? $order_setting['enable_print'] : 0;

        // Optional Fields
        $email_field = (isset($order_setting['email_field']) && $order_setting['email_field'] == 1) ? $order_setting['email_field'] : 0;
        $floor_field = (isset($order_setting['floor_field']) && $order_setting['floor_field'] == 1) ? $order_setting['floor_field'] : 0;
        $door_bell_field = (isset($order_setting['door_bell_field']) && $order_setting['door_bell_field'] == 1) ? $order_setting['door_bell_field'] : 0;
        $full_name_field = (isset($order_setting['full_name_field']) && $order_setting['full_name_field'] == 1) ? $order_setting['full_name_field'] : 0;
        $instructions_field = (isset($order_setting['instructions_field']) && $order_setting['instructions_field'] == 1) ? $order_setting['instructions_field'] : 0;
        $live_address_field = (isset($order_setting['live_address_field']) && $order_setting['live_address_field'] == 1) ? $order_setting['live_address_field'] : 0;

        // Orders
        $orders = Order::where('shop_id',$shop_id)->whereIn('order_status',['pending','accepted'])->orderBy('id','DESC')->get();

        if(count($orders) > 0)
        {
            foreach($orders as $order)
            {
                $discount_type = (isset($order->discount_type) && !empty($order->discount_type)) ? $order->discount_type : 'percentage';

                $html .= '<div class="order">';
                    $html .= '<div class="order-btn d-flex align-items-center justify-content-end">';
                        $html .= '<div class="d-flex align-items-center flex-wrap">'.__('Estimated time of arrival').' <input type="number" onchange="changeEstimatedTime(this)" name="estimated_time" id="estimated_time" value="'.$order->estimated_time.'" class="form-control mx-1 estimated_time" style="width: 100px!important" ord-id="'.$order->id.'"';
                        if($order->order_status == 'accepted')
                        {
                            $html .= 'disabled';
                        }
                        else
                        {
                            $html .= '';
                        }
                        $html .= '> '.__('Minutes').'.</div>';

                        if($order->order_status == 'pending')
                        {
                            $html .= '<a class="btn btn-sm btn-primary ms-3" onclick="acceptOrder('.$order->id.')"><i class="bi bi-check-circle" data-bs-toggle="tooltip" title="Accept"></i> '.__('Accept').'</a>';
                            $html .= '<a class="btn btn-sm btn-danger ms-3" onclick="rejectOrder('.$order->id.')"><i class="bi bi-x-circle" data-bs-toggle="tooltip" title="Reject"></i> '.__('Reject').'</a>';
                        }
                        elseif($order->order_status == 'accepted')
                        {
                            $html .= '<a class="btn btn-sm btn-success ms-3" onclick="finalizedOrder('.$order->id.')"><i class="bi bi-check-circle" data-bs-toggle="tooltip" title="Complete"></i> '.__('Finalize').'</a>';
                        }

                        if($enable_print == 1)
                        {
                            $html .= '<a class="btn btn-sm btn-primary ms-3" onclick="printReceipt('.$order->id .')"><i class="bi bi-printer"></i> Print</a>';
                        }

                    $html .= '</div>';

                    $html .= '<div class="order-info">';
                        $html .= '<ul>';
                            $html .= '<li><strong>#'.$order->id.'</strong></li>';
                            $html .= '<li><strong>'.__('Order Date').' : </strong>'.date('d-m-Y h:i:s',strtotime($order->created_at)).'</li>';
                            $html .= '<li><strong>'.__('Order Type').' : </strong>'.$order->checkout_type.'</li>';
                            $html .= '<li><strong>'.__('Payment Method').' : </strong>'.$order->payment_method.'</li>';

                            if($order->checkout_type == 'takeaway')
                            {
                                $html .= '<li><strong>'.__('Customer').' : </strong>'.$order->firstname.' '.$order->lastname.'</li>';
                                $html .= '<li><strong>'.__('Telephone').' : </strong> '.$order->phone.'</li>';

                                if($email_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Email').' : </strong> '.$order->email.'</li>';
                                }
                            }
                            elseif($order->checkout_type == 'table_service')
                            {
                                $html .= '<li><strong>'.__('Table No.').' : </strong> '.$order->table.'</li>';
                            }
                            elseif($order->checkout_type == 'room_delivery')
                            {
                                $html .= '<li><strong>'.__('Customer').' : </strong>'.$order->firstname.' '.$order->lastname.'</li>';
                                $html .= '<li><strong>'.__('Room No.').' : </strong> '.$order->room.'</li>';
                                if(!empty($order->delivery_time ))
                                {
                                    $html .= '<li><strong>'.__('Delivery Time').' : </strong> '.$order->delivery_time.'</li>';
                                }
                            }
                            elseif($order->checkout_type == 'delivery')
                            {
                                $html .= '<li><strong>'.__('Customer').' : </strong>'.$order->firstname.' '.$order->lastname.'</li>';
                                $html .= '<li><strong>'.__('Telephone').' : </strong> '.$order->phone.'</li>';

                                if($email_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Email').' : </strong> '.$order->email.'</li>';
                                }

                                $html .= '<li><strong>'.__('Address').' : </strong> '.$order->address.'</li>';

                                if($floor_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Floor').' : </strong> '.$order->floor.'</li>';
                                }

                                if($door_bell_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Door Bell').' : </strong> '.$order->door_bell.'</li>';
                                }

                                if($live_address_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Google Map').' : </strong> <a href="https://maps.google.com?q='.$order->address.'" target="_blank">Address Link</a></li>';
                                }

                                if($instructions_field == 1)
                                {
                                    $html .= '<li><strong>'.__('Comments').' : </strong> '.$order->instructions.'</li>';
                                }
                            }

                        $html .= '</ul>';
                    $html .= '</div>';

                    $html .= '<hr>';

                    $html .= '<div class="order-info mt-2">';
                        $html .= '<div class="row">';
                            $html .= '<div class="col-md-3">';
                                $html .= '<table class="table">';

                                    $html .= '<tr>';
                                        $html .= '<td><b>'.__('Sub Total').'</b></td>';
                                        $html .= '<td class="text-end">'. Currency::currency($currency)->format($order->order_subtotal).'</td>';
                                    $html .= '</tr>';

                                    if($order->discount_per > 0)
                                    {
                                        $html .= '<tr>';
                                            $html .= '<td><b>'.__('Discount').'</b></td>';
                                            if($order->discount_per == 'fixed')
                                            {
                                                $html .= '<td class="text-end">- '.Currency::currency($currency)->format($order->discount_per).'</td>';
                                            }
                                            else
                                            {
                                                $html .= '<td class="text-end">- '.$order->discount_per.'%</td>';
                                            }
                                        $html .= '</tr>';
                                    }

                                    if($order->cgst > 0 && $order->sgst > 0)
                                    {
                                        $gst_amt = $order->cgst + $order->sgst;
                                        $gst_amt = $order->gst_amount / $gst_amt;

                                        $html .= '<tr>';
                                            $html .= '<td><b>'.__('CGST.').' ('.$order->cgst.'%)</b></td>';
                                            $html .= '<td class="text-end">+ '.Currency::currency($currency)->format($order->cgst * $gst_amt).'</td>';
                                        $html .= '</tr>';

                                        $html .= '<tr>';
                                            $html .= '<td><b>'.__('SGST.').' ('.$order->sgst.'%)</b></td>';
                                            $html .= '<td class="text-end">+ '.Currency::currency($currency)->format($order->sgst * $gst_amt).'</td>';
                                        $html .= '</tr>';
                                    }

                                    $html .= '<tr class="text-end">';
                                        $html .= '<td colspan="2"><b>'.Currency::currency($currency)->format($order->order_total).'</b></td>';
                                    $html .= '</tr>';

                                $html .= '</table>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<hr>';

                    $html .= '<div class="order-items">';
                        $html .= '<div class="row">';
                            if(count($order->order_items) > 0)
                            {
                                $html .= '<div class="col-md-8">';
                                    $html .= '<table class="table">';
                                        foreach ($order->order_items as $ord_item)
                                        {
                                            $sub_total = ( $ord_item['sub_total'] / $ord_item['item_qty']);
                                            $option = unserialize($ord_item['options']);

                                            $html .= '<tr>';
                                                $html .= '<td>';
                                                    $html .= '<b>'.$ord_item['item_qty'].' x '.$ord_item['item_name'].'</b>';
                                                    if(!empty($option))
                                                    {
                                                        $html .= '<br> '.implode(', ',$option);
                                                    }
                                                $html .= '</td>';
                                                $html .= '<td width="25%" class="text-end">'.Currency::currency($currency)->format($sub_total).'</td>';
                                                $html .= '<td width="25%" class="text-end">'.$ord_item['sub_total_text'].'</td>';
                                            $html .= '</tr>';
                                        }
                                    $html .= '</table>';
                                $html .= '</div>';
                            }
                        $html .= '</div>';
                    $html .= '</div>';

                $html .= '</div>';
            }
        }
        else
        {
            $html .= '<div class="row">';
                $html .= '<div class="col-md-12 text-center">';
                    $html .= '<h3>Orders Not Available</h3>';
                $html .= '</div>';
            $html .= '</div>';
        }

        return response()->json([
            'success' => 1,
            'data' => $html,
        ]);
    }


    // Function for Display Client Orders History
    public function ordersHistory(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['payment_method'] = '';
        $data['status_filter'] = '';
        $data['day_filter'] = '';
        $data['total_text'] = 'Total Amount';
        $data['total'] = 0.00;
        $data['start_date'] = Carbon::now();
        $data['end_date'] = Carbon::now();
        $data['StartDate'] = '';
        $data['EndDate'] = '';

        if($request->isMethod('get'))
        {
            $data['orders'] = Order::where('shop_id',$shop_id)->get();
            $data['total'] = Order::where('shop_id',$shop_id)->sum('order_total');
        }
        else
        {
            $orders = Order::where('shop_id',$shop_id);
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $data['payment_method'] = (isset($request->filter_by_payment_method)) ? $request->filter_by_payment_method : '';
            $data['status_filter'] = (isset($request->filter_by_status)) ? $request->filter_by_status : '';

            // Payment Method Filter
            if(!empty($data['payment_method']))
            {
                $orders = $orders->where('payment_method',$data['payment_method']);
                $data['total'] = $orders->sum('order_total');
            }
            else
            {
                $data['total'] = $orders->sum('order_total');
            }

            // Status Filter
            if(!empty($data['status_filter']))
            {
                $orders = $orders->where('order_status',$data['status_filter']);
                $data['total'] = $orders->sum('order_total');
            }
            else
            {
                $data['total'] = $orders->sum('order_total');
            }

            if(!empty($start_date) && !empty($end_date))
            {
                $data['start_date'] = $start_date;
                $data['StartDate'] = $start_date;
                $data['end_date'] = $end_date;
                $data['EndDate'] = $end_date;

                $orders = $orders->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
                $data['total'] = $orders->sum('order_total');
                $data['orders'] = $orders->get();
            }
            else
            {

                // Day Filter
                $data['day_filter'] = (isset($request->filter_by_day)) ? $request->filter_by_day : '';
                if(!empty($data['day_filter']))
                {
                    if($data['day_filter'] == 'today')
                    {
                        $today = Carbon::today();
                        $orders = $orders->whereDate('created_at', $today);
                        $data['total_text'] = "Today's Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'this_week')
                    {
                        $startOfWeek = Carbon::now()->startOfWeek();
                        $endOfWeek = Carbon::now()->endOfWeek();
                        $orders = $orders->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                        $data['total_text'] = "This Week Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'last_week')
                    {
                        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
                        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
                        $orders = $orders->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                        $data['total_text'] = "Last Week Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'this_month')
                    {
                        $currentMonth = Carbon::now()->format('Y-m');
                        $orders = $orders->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                        $data['total_text'] = "This Month Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'last_month')
                    {
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        $orders = $orders->whereBetween('created_at', [$startDate, $endDate]);
                        $data['total_text'] = "Last Month Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'last_six_month')
                    {
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        $orders = $orders->whereBetween('created_at', [$startDate, $endDate]);
                        $data['total_text'] = "Last Six Months Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'this_year')
                    {
                        $startOfYear = Carbon::now()->startOfYear();
                        $endOfYear = Carbon::now()->endOfYear();
                        $orders = $orders->whereBetween('created_at', [$startOfYear, $endOfYear]);
                        $data['total_text'] = "This Year Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                    elseif($data['day_filter'] == 'last_year')
                    {
                        $startOfYear = Carbon::now()->subYear()->startOfYear();
                        $endOfYear = Carbon::now()->subYear()->endOfYear();
                        $orders = $orders->whereBetween('created_at', [$startOfYear, $endOfYear]);
                        $data['total_text'] = "Last Year Total Amount";
                        $data['total'] = $orders->sum('order_total');
                    }
                }
            }

            $data['orders'] = $orders->get();
        }

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
        {
            return view('client.orders.orders_history',$data);
        }
        else
        {
            return redirect()->route('client.dashboard')->with('error','Unauthorized Action!');
        }
    }


    // function for view OrderSettings
    public function OrderSettings()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['order_settings'] = getOrderSettings($shop_id);
        $data['deliveryAreas'] = DeliveryAreas::where('shop_id',$shop_id)->get();

        return view('client.orders.order_settings',$data);
    }


    // Function for Update Order Settings
    public function UpdateOrderSettings(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $all_data['delivery'] = (isset($request->delivery)) ? $request->delivery : 0;
        $all_data['takeaway'] = (isset($request->takeaway)) ? $request->takeaway : 0;
        $all_data['room_delivery'] = (isset($request->room_delivery)) ? $request->room_delivery : 0;
        $all_data['table_service'] = (isset($request->table_service)) ? $request->table_service : 0;
        $all_data['only_cart'] = (isset($request->only_cart)) ? $request->only_cart : 0;
        $all_data['auto_order_approval'] = (isset($request->auto_order_approval)) ? $request->auto_order_approval : 0;
        $all_data['scheduler_active'] = (isset($request->scheduler_active)) ? $request->scheduler_active : 0;
        $all_data['min_amount_for_delivery'] = (isset($request->min_amount_for_delivery)) ? $request->min_amount_for_delivery : '';
        $all_data['discount_percentage'] = (isset($request->discount_percentage)) ? $request->discount_percentage : '';
        $all_data['order_arrival_minutes'] = (isset($request->order_arrival_minutes)) ? $request->order_arrival_minutes : 30;
        $all_data['schedule_array'] = $request->schedule_array;
        $all_data['default_printer'] = (isset($request->default_printer)) ? $request->default_printer : '';
        $all_data['receipt_intro'] = $request->receipt_intro;
        $all_data['discount_type'] = $request->discount_type;
        $all_data['auto_print'] = (isset($request->auto_print)) ? $request->auto_print : 0;
        $all_data['play_sound'] = (isset($request->play_sound)) ? $request->play_sound : 0;
        $all_data['enable_print'] = (isset($request->enable_print)) ? $request->enable_print : 0;
        $all_data['printer_paper'] = (isset($request->printer_paper)) ? $request->printer_paper : '';
        $all_data['printer_tray'] = (isset($request->printer_tray)) ? $request->printer_tray : '';
        $all_data['print_font_size'] = (isset($request->print_font_size)) ? $request->print_font_size : '';
        $all_data['notification_sound'] = (isset($request->notification_sound)) ? $request->notification_sound : 'buzzer-01.mp3';
        $all_data['customer_details'] = (isset($request->customer_details)) ? $request->customer_details : 0;
        $all_data['email_field'] = (isset($request->email_field)) ? $request->email_field : 0;
        $all_data['floor_field'] = (isset($request->floor_field)) ? $request->floor_field : 0;
        $all_data['door_bell_field'] = (isset($request->door_bell_field)) ? $request->door_bell_field : 0;
        $all_data['full_name_field'] = (isset($request->full_name_field)) ? $request->full_name_field : 0;
        $all_data['instructions_field'] = (isset($request->instructions_field)) ? $request->instructions_field : 0;
        $all_data['live_address_field'] = (isset($request->live_address_field)) ? $request->live_address_field : 0;

        try
        {
            // Insert or Update Settings
            foreach($all_data as $key => $value)
            {
                $query = OrderSetting::where('shop_id',$shop_id)->where('key',$key)->first();
                $setting_id = isset($query->id) ? $query->id : '';

                if (!empty($setting_id) || $setting_id != '')  // Update
                {
                    $settings = OrderSetting::find($setting_id);
                    $settings->value = $value;
                    $settings->update();
                }
                else // Insert
                {
                    $settings = new OrderSetting();
                    $settings->shop_id = $shop_id;
                    $settings->key = $key;
                    $settings->value = $value;
                    $settings->save();
                }
            }

            // Insert Delivery Zones Area
            $delivery_zones = (isset($request->new_coordinates) && !empty($request->new_coordinates)) ? json_decode($request->new_coordinates,true) : [];

            if(count($delivery_zones) > 0)
            {
                foreach($delivery_zones as $delivery_zone)
                {
                    $polygon = serialize($delivery_zone);

                    $delivery_area = new DeliveryAreas();
                    $delivery_area->shop_id = $shop_id;
                    $delivery_area->coordinates = $polygon;
                    $delivery_area->save();
                }
            }

            return response()->json([
                'success' => 1,
                'message' => 'Setting has been Updated SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for Clear Delivery Range Settings
    public function clearDeliveryRangeSettings()
    {
        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';

        DeliveryAreas::where('shop_id',$shop_id)->delete();

        return redirect()->route('order.settings')->with('success',"Setting has been Updated SuccessFully..");

    }


    // Function for Change Order Estimated Time
    public function changeOrderEstimate(Request $request)
    {
        $order_id = $request->order_id;
        $estimated_time = $request->estimate_time;
        if($estimated_time == '' || $estimated_time == 0 || $estimated_time < 0)
        {
            $estimated_time = '30';
        }

        try
        {
            $order = Order::find($order_id);
            $order->estimated_time = $estimated_time;
            $order->update();

            return response()->json([
                'success' => 1,
                'message' => 'Time has been Changed SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }

    }


    // Function for Accpeting Order
    public function acceptOrder(Request $request)
    {
        $order_id = $request->order_id;
        try
        {
            // Shop ID
            $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
            $shop_name = isset(Auth::user()->hasOneShop->shop['name']) ? Auth::user()->hasOneShop->shop['name'] : '';
            $shop_url = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
            $shop_slug = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
            $shop_url = asset($shop_url);
            $shop_name = '<a href="'.$shop_url.'">'.$shop_name.'</a>';
            $shop_logo = (isset(Auth::user()->hasOneShop->shop['logo'])) ? Auth::user()->hasOneShop->shop['logo'] : '';
            $shop_logo = '<img src="'.$shop_logo.'" width="200">';

            // Order Settings
            $order_setting = getOrderSettings($shop_id);

            // Optional Fields
            $email_field = (isset($order_setting['email_field']) && $order_setting['email_field'] == 1) ? $order_setting['email_field'] : 0;
            $floor_field = (isset($order_setting['floor_field']) && $order_setting['floor_field'] == 1) ? $order_setting['floor_field'] : 0;
            $door_bell_field = (isset($order_setting['door_bell_field']) && $order_setting['door_bell_field'] == 1) ? $order_setting['door_bell_field'] : 0;
            $full_name_field = (isset($order_setting['full_name_field']) && $order_setting['full_name_field'] == 1) ? $order_setting['full_name_field'] : 0;
            $instructions_field = (isset($order_setting['instructions_field']) && $order_setting['instructions_field'] == 1) ? $order_setting['instructions_field'] : 0;
            $live_address_field = (isset($order_setting['live_address_field']) && $order_setting['live_address_field'] == 1) ? $order_setting['live_address_field'] : 0;

            // Update Order Status
            $order = Order::find($order_id);
            $order->order_status = 'accepted';
            $order->is_new = 0;
            $order->update();

            // Get Shop Settings
            $shop_settings = getClientSettings($shop_id);
            $orders_mail_form_customer = (isset($shop_settings['orders_mail_form_customer'])) ? $shop_settings['orders_mail_form_customer'] : '';

            // Shop Currency
            $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

            // Get Contact Emails
            $shop_user = UserShop::with(['user'])->where('shop_id',$shop_id)->first();
            $contact_emails = (isset($shop_user->user['contact_emails']) && !empty($shop_user->user['contact_emails'])) ? unserialize($shop_user->user['contact_emails']) : '';

            // Sent Mail to Customer
            if($order->id)
            {
                $order_items = (isset($order->order_items) && count($order->order_items) > 0) ? $order->order_items : [];
                $discount_type = (isset($order->discount_type) && !empty($order->discount_type)) ? $order->discount_type : 'percentage';

                $checkout_type =  (isset($order->checkout_type)) ? $order->checkout_type : '';
                $payment_method =  (isset($order->payment_method)) ? $order->payment_method : '';

                $from_email = (isset($order->email)) ? $order->email : '';

                if($checkout_type == 'takeaway' || $checkout_type == 'delivery')
                {
                    if(!empty($from_email) && count($contact_emails) > 0 && !empty($orders_mail_form_customer) && $email_field == 1)
                    {
                        $to = $from_email;
                        $from = $contact_emails[0];
                        $subject = "Order Placed";
                        $fname = (isset($order->firstname)) ? $order->firstname : '';
                        $lname = (isset($order->lastname)) ? $order->lastname : '';
                        $estimated_time = (isset($order->estimated_time)) ? $order->estimated_time : '';

                        $message = $orders_mail_form_customer;
                        $message = str_replace('{shop_logo}',$shop_logo,$message);
                        $message = str_replace('{shop_name}',$shop_name,$message);
                        $message = str_replace('{firstname}',$fname,$message);
                        $message = str_replace('{lastname}',$lname,$message);
                        $message = str_replace('{order_id}',$order->id,$message);
                        $message = str_replace('{order_type}',$checkout_type,$message);
                        $message = str_replace('{payment_method}',$payment_method,$message);
                        $message = str_replace('{order_status}','Accepted',$message);
                        $message = str_replace('{estimated_time}',$estimated_time,$message);

                        // Order Items
                        $order_html  = "";
                        $order_html .= '<div>';
                            $order_html .= '<table style="width:100%; border:1px solid gray;border-collapse: collapse;">';
                                $order_html .= '<thead style="background:lightgray; color:white">';
                                    $order_html .= '<tr style="text-transform: uppercase!important;    font-weight: 700!important;">';
                                        $order_html .= '<th style="text-align: left!important;width: 60%;padding:10px">Item</th>';
                                        $order_html .= '<th style="text-align: center!important;padding:10px">Qty.</th>';
                                        $order_html .= '<th style="text-align: right!important;padding:10px">Item Total</th>';
                                    $order_html .= '</tr>';
                                $order_html .= '</thead>';
                                $order_html .= '<tbody style="font-weight: 600!important;">';

                                    if(count($order_items) > 0)
                                    {
                                        foreach($order_items as $order_item)
                                        {
                                            $item_dt = itemDetails($order_item['item_id']);
                                            $item_image = (isset($item_dt['image']) && !empty($item_dt['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image']) : asset('public/client_images/not-found/no_image_1.jpg');
                                            $options_array = (isset($order_item['options']) && !empty($order_item['options'])) ? unserialize($order_item['options']) : '';
                                            if(count($options_array) > 0)
                                            {
                                                $options_array = implode(', ',$options_array);
                                            }

                                            $order_html .= '<tr>';

                                                $order_html .= '<td style="text-align: left!important;padding:10px; border-bottom:1px solid gray;">';
                                                    $order_html .= '<div style="align-items: center!important;display: flex!important;">';
                                                        $order_html .= '<a style="display: inline-block;
                                                        flex-shrink: 0;position: relative;border-radius: 0.75rem;">';
                                                            $order_html .= '<span style="width: 50px;
                                                            height: 50px;display: flex;
                                                            align-items: center;
                                                            justify-content: center;
                                                            font-weight: 500;background-repeat: no-repeat;
                                                            background-position: center center;
                                                            background-size: cover;
                                                            border-radius: 0.75rem; background-image:url('.$item_image.')"></span>';
                                                        $order_html .= '</a>';
                                                        $order_html .= '<div style="display: block;    margin-left: 3rem!important;">';
                                                            $order_html .= '<a style="font-weight: 700!important;color: #7e8299;
                                                            ">'.$order_item->item_name.'</a>';

                                                            if(!empty($options_array))
                                                            {
                                                                $order_html .= '<div style="color: #a19e9e;display: block;">'.$options_array.'</div>';
                                                            }
                                                            else
                                                            {
                                                                $order_html .= '<div style="color: #a19e9e;display: block;"></div>';
                                                            }

                                                        $order_html .= '</div>';
                                                    $order_html .= '</div>';
                                                $order_html .= '</td>';

                                                $order_html .= '<td style="text-align: center!important;padding:10px; border-bottom:1px solid gray;">';
                                                    $order_html .= $order_item['item_qty'];
                                                $order_html .= '</td>';

                                                $order_html .= '<td style="text-align: right!important;padding:10px; border-bottom:1px solid gray;">';
                                                    $order_html .= Currency::currency($currency)->format($order_item['sub_total']);
                                                $order_html .= '</td>';

                                            $order_html .= '</tr>';
                                        }
                                    }

                                $order_html .= '</tbody>';
                            $order_html .= '</table>';
                        $order_html .= '</div>';
                        $message = str_replace('{items}',$order_html,$message);

                        // Order Total
                        $order_total_html = "";
                                $order_total_html .= '<div>';
                                    $order_total_html .= '<table style="width:50%; border:1px solid gray;border-collapse: collapse;">';
                                        $order_total_html .= '<tbody style="font-weight: 700!important;">';
                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Sub Total : </td>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.Currency::currency($currency)->format($order->order_subtotal).'</td>';
                                            $order_total_html .= '</tr>';

                                            if($order->discount_per > 0)
                                            {
                                                $order_total_html .= '<tr>';
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Discount : </td>';
                                                    if($order->discount_per == 'fixed')
                                                    {
                                                        $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.Currency::currency($currency)->format($order->discount_per).'</td>';
                                                    }
                                                    else
                                                    {
                                                        $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.$order->discount_per.'%</td>';
                                                    }
                                                $order_total_html .= '</tr>';
                                            }

                                            if($order->cgst > 0 && $order->sgst > 0)
                                            {
                                                $gst_amt = $order->cgst + $order->sgst;
                                                $gst_amt = $order->gst_amount / $gst_amt;

                                                $order_total_html .= '<tr>';
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('CGST.').' ('.$order->cgst.'%)</td>';
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order->cgst * $gst_amt).'</td>';
                                                $order_total_html .= '</tr>';
                                                $order_total_html .= '<tr>';
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('SGST.').' ('.$order->sgst.'%)</td>';
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order->sgst * $gst_amt).'</td>';
                                                $order_total_html .= '</tr>';
                                            }

                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px;">Total : </td>';
                                                $order_total_html .= '<td style="padding:10px;">';
                                                    $order_total_html .= Currency::currency($currency)->format($order->order_total);
                                                $order_total_html .= '</td>';
                                            $order_total_html .= '</tr>';

                                        $order_total_html .= '</tbody>';
                                    $order_total_html .= '</table>';
                                $order_total_html .= '</div>';
                                $message = str_replace('{total}',$order_total_html,$message);

                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                        // More headers
                        $headers .= 'From: <'.$from.'>' . "\r\n";

                        mail($to,$subject,$message,$headers);

                    }
                }
            }

            return response()->json([
                'success' => 1,
                'message' => 'Order has been Accepted SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for Reject Order
    public function rejectOrder(Request $request)
    {
        $order_id = $request->order_id;
        $reject_reason = $request->reject_reason;
        try
        {
            // Update Order Status
            $order = Order::find($order_id);
            $order->order_status = 'rejected';
            $order->reject_reason = $reject_reason;
            $order->is_new = 0;
            $order->update();

            return response()->json([
                'success' => 1,
                'message' => 'Order has been Rejected SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for Finalized Order
    public function finalizedOrder(Request $request)
    {
        $order_id = $request->order_id;
        try
        {
            $order = Order::find($order_id);
            $order->order_status = 'completed';
            $order->update();

            return response()->json([
                'success' => 1,
                'message' => 'Order has been Completed SuccessFully...',
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }
    }


    // Function for view Order
    public function viewOrder($order_id)
    {
        try
        {
            $order_id = decrypt($order_id);
            $data['order'] = Order::with(['order_items'])->where('id',$order_id)->first();
            return view('client.orders.order_details',$data);
        }
        catch (\Throwable $th)
        {
            return redirect()->route('client.orders')->with('error',"Internal Server Error!");
        }
    }


    // Function for Set Delivery Address in Session
    public function setDeliveryAddress(Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;
        $address = $request->address;
        $shop_id = $request->shop_id;

        try
        {
            session()->put('cust_lat',$lat);
            session()->put('cust_long',$lng);
            session()->put('cust_address',$address);
            session()->save();

            $delivey_avaialbility = checkDeliveryAvilability($shop_id,$lat,$lng);

            return response()->json([
                'success' => 1,
                'message' => 'Address has been set successfully...',
                'available' => $delivey_avaialbility,
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error!',
            ]);
        }

    }


    // Function for set Printer JS License Key
    public function setPrinterLicense()
    {
        $license_owner = 'Dimitris Bourlos - 1 WebApp Lic - 1 WebServer Lic';
        // $license_key  = '661C6658D5FC2787F94AC3E96C33BBE59C5FC29D';
        $license_key  = '6FFB09414392C388097D50175A10478DE4611F4A';

        //DO NOT MODIFY THE FOLLOWING CODE
        $timestamp = request()->query('timestamp');
        $license_hash = hash('sha256', $license_key . $timestamp, false);
        $resp = $license_owner . '|' . $license_hash;

        return response($resp)->header('Content-Type', 'text/plain');
    }


    // Function for Get Order Receipt
    public function getOrderReceipt(Request $request)
    {
        $order_id = $request->order_id;
        $user_details = Auth::user();
        $shop_address = (isset($user_details['address'])) ? $user_details['address'] : '';
        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_name = (isset(Auth::user()->hasOneShop->shop['name'])) ? Auth::user()->hasOneShop->shop['name'] : '';
        $shop_settings = getClientSettings($shop_id);
        $business_telephone = (isset($shop_settings['business_telephone'])) ? $shop_settings['business_telephone'] : '';
        $gst_number = (isset($user_details['gst_number']) && !empty($user_details['gst_number'])) ? $user_details['gst_number'] : '';

        $html = '';

        try
        {
            $order = Order::with(['order_items','shop'])->where('id',$order_id)->first();
            $discount_type = (isset($order->discount_type) && !empty($order->discount_type)) ? $order->discount_type : 'percentage';
            $shop_id = (isset($order->shop['id'])) ? $order->shop['id'] : '';

            $shop_settings = getClientSettings($shop_id);

            $order_setting = getOrderSettings($shop_id);
            $receipt_intro = (isset($order_setting['receipt_intro']) && !empty($order_setting['receipt_intro'])) ? $order_setting['receipt_intro'] : 'ORDER';

            // Shop Currency
            $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

            $order_date = (isset($order->created_at)) ? $order->created_at : '';
            $payment_method = (isset($order->payment_method)) ? str_replace('_',' ',$order->payment_method) : '';
            $checkout_type = (isset($order->checkout_type)) ? $order->checkout_type : '';
            $customer = $order->firstname." ".$order->lastname;
            $phone = (isset($order->phone)) ? $order->phone : '';
            $email = (isset($order->email)) ? $order->email : '';
            $address = (isset($order->address)) ? $order->address : '';
            $floor = (isset($order->floor)) ? $order->floor : '';
            $table_no = (isset($order->table)) ? $order->table : '';
            $room_no = (isset($order->room)) ? $order->room : '';
            $delivery_time = (isset($order->delivery_time)) ? $order->delivery_time : '';
            $door_bell = (isset($order->door_bell)) ? $order->door_bell : '';
            $items = (isset($order->order_items)) ? $order->order_items : [];
            $order_total_text = (isset($order->order_total_text)) ? $order->order_total_text : '';

            $html .= '<div class="row justify-content-center">';
                $html .= '<div class="col-md-10">';
                    $html .= '<div class="card">';
                        $html .= '<div class="card-body" style="font-size:38px!important;">';
                            $html .= '<div class="row">';
                                $html .= '<div class="col-md-12 text-center mb-3">';
                                    $html .= '<p class="m-0"><strong>'.$shop_name.'</strong></p>';
                                    if(!empty($business_telephone))
                                    {
                                        $html .= '<p class="m-0"><b>'.$business_telephone.'</b></p>';
                                    }
                                $html .= '</div>';
                                $html .= '<div class="col-md-12 text-center mb-3">';
                                    $html .= '<p class="m-0">'.$shop_address.'</p>';
                                $html .= '</div>';
                                if(!empty($gst_number))
                                {
                                    $html .= '<div class="col-md-12 text-center mb-3">';
                                        $html .= '<p class="m-0"> GST No. : '.$gst_number.'</p>';
                                    $html .= '</div>';
                                }
                                $html .= '<div class="col-md-12">';
                                    $html .= '<ul class="p-0 m-0 list-unstyled" style="border-top: 2px dotted #ccc;padding:15px 0 !important;border-bottom:2px solid #000;">';
                                        if($checkout_type == 'takeaway' || $checkout_type == 'delivery')
                                        {
                                            $html .= '<li><b>Customer : </b> '.$customer.'</li>';
                                            $html .= '<li><b>Customer Phone : </b> '.$phone.'</li>';
                                        }
                                        if($checkout_type == 'room_delivery')
                                        {
                                            $html .= '<li><b>Customer : </b> '.$customer.'</li>';
                                            $html .= '<li><b>Room No. : </b> '.$room_no.'</li>';
                                            if(!empty($delivery_time))
                                            {
                                                $html .= '<li><b>Delivery Time : </b> '.$delivery_time.'</li>';
                                            }
                                        }
                                        $html .= '<li><b>Order No. : </b>'.$order_id.'</li>';
                                        $html .= '<li><b>Order Date : </b> '.date('d-m-Y h:i:s',strtotime($order_date)).'</li>';
                                        $html .= '<li><b>Payment Method : </b> '.ucfirst($payment_method).'</li>';
                                        $html .= '<li><b>Checkout Type : </b> '.ucfirst(str_replace('_',' ',$checkout_type)).'</li>';
                                        if($checkout_type == 'delivery')
                                        {
                                            $html .= '<li><b>Bell : </b> '.$door_bell.'</li>';
                                            $html .= '<li><b>Floor No. : </b> '.$floor.'</li>';
                                            $html .= '<li><b>Address : </b> '.$address.'</li>';
                                        }
                                        if($checkout_type == 'table_service')
                                        {
                                            $html .= '<li><b>Table No : </b> '.$table_no.'</li>';
                                        }
                                    $html .= '</ul>';
                                $html .= '</div>';
                            $html .= '</div>';
                            // $html .= '<hr>';
                            $html .= '<div class="row ord-rec-body">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<table class="table border-0 m-0" style="border-bottom:2px solid #000 !important">';
                                        $html .= '<thead>';
                                            $html .= '<tr><th class="border-0" width="10%">S.No</th><th class="border-0">Item</th><th class="border-0" width="10%">Qty.</th><th width="25%" class="text-end border-0">Amount</th></tr>';
                                        $html .= '</thead>';
                                        $html .= '<tbody>';
                                            if(count($items) > 0)
                                            {
                                                $i=1;
                                                foreach($items as $item)
                                                {
                                                    $item_name = (isset($item['item_name'])) ? $item['item_name'] : '';
                                                    $item_qty = (isset($item['item_qty'])) ? $item['item_qty'] : 0;
                                                    $sub_total_text = (isset($item['sub_total_text'])) ? $item['sub_total_text'] : 0;
                                                    $option = unserialize($item['options']);

                                                    $html .= '<tr>';
                                                        $html .= '<td class="border-0">'.$i.'</td>';
                                                        $html .= '<td class="border-0">'.$item_name;
                                                        if(!empty($option))
                                                        {
                                                            $html .= '<br>'.implode(', ',$option);
                                                        }
                                                        $html .= '</td>';
                                                        $html .= '<td class="border-0">'.$item_qty.'</td>';
                                                        $html .= '<td class="text-end border-0">'.$sub_total_text.'</td>';
                                                    $html .= '</tr>';
                                                    $i++;
                                                }
                                            }
                                        $html .= '</tbody>';
                                    $html .= '</table>';
                                $html .= '</div>';
                                // $html .= '<div class="col-md-6 mt-2">';
                                // $html .= '</div>';
                                $html .= '<div class="col-md-12 ord-rec-body">';
                                    $html .= '<table class="table m-0 border-0" style="border-bottom:2px solid #000 !important">';

                                        $html .= '<tr>';
                                            $html .= '<td><strong>Sub Total : </strong></td>';
                                            $html .= '<td class="text-end">'.Currency::currency($currency)->format($order->order_subtotal).'</td>';
                                        $html .= '</tr>';

                                        if($order->discount_per > 0)
                                        {
                                            $html .= '<tr>';
                                                $html .= '<td><strong>Discount : </strong></td>';
                                                if($order->discount_per == 'fixed')
                                                {
                                                    $html .= '<td class="text-end">- '.Currency::currency($currency)->format($order->discount_per).'</td>';
                                                }
                                                else
                                                {
                                                    $html .= '<td class="text-end">- '.$order->discount_per.'%</td>';
                                                }
                                            $html .= '</tr>';
                                        }

                                        if($order->cgst > 0 && $order->sgst > 0)
                                        {
                                            $gst_amt = $order->cgst + $order->sgst;
                                            $gst_amt = $order->gst_amount / $gst_amt;

                                            $html .= '<tr>';
                                                $html .= '<td><strong>CGST ('.$order->cgst.'%) : </strong></td>';
                                                $html .= '<td class="text-end">+ '.Currency::currency($currency)->format($order->cgst * $gst_amt).'</td>';
                                            $html .= '</tr>';
                                            $html .= '<tr>';
                                                $html .= '<td><strong>SGST ('.$order->sgst.'%) : </strong></td>';
                                                $html .= '<td class="text-end">+ '.Currency::currency($currency)->format($order->sgst * $gst_amt).'</td>';
                                            $html .= '</tr>';
                                        }

                                        $html .= '<tr class="text-end">';
                                            $html .= '<td colspan="2"><strong>'.Currency::currency($currency)->format($order->order_total).'</strong></td>';
                                        $html .= '</tr>';

                                    $html .= '</table>';
                                $html .= '</div>';
                            $html .= '</div>';
                            // $html .= '<hr>';
                            $html .= '<div class="row">';
                                $html .= '<div class="col-md-12 text-center mt-2">';
                                    $html .= '<p class="p-0 m-0 ord-rec-body-start">Thank For Your Business.</p>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            return response()->json([
                'success' => 1,
                'message' => "Receipt Generated",
                'data' => $html,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }

    }


    // Function for Get Order Notification
    public function orderNotification(Request $request)
    {
        $html = '';
        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
        $new_order_count = Order::where('shop_id',$shop_id)->where('order_status','pending')->where('is_new',1)->count();

        if($new_order_count > 0)
        {
            $html .= 'You Have '.$new_order_count.' New Orders';
            $html .= '<a href="'.route('client.orders').'"><span class="badge rounded-pill bg-primary p-2 ms-2">View All</span></a>';
        }
        else
        {
            $html .= 'You Have 0 New Orders';
            $html .= '<a href="'.route('client.orders').'"><span class="badge rounded-pill bg-primary p-2 ms-2">View All</span></a>';
        }


        return response()->json([
            'success' => 1,
            'data' => $html,
            'count' => $new_order_count,
        ]);
    }

}
