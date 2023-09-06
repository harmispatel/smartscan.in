<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Subscriptions,Shop};

class FrontendController extends Controller
{

    public function index()
    {
        $data['shops'] = Shop::with(['usershop'])->whereHas('usershop',function($q) {
            $q->whereHas('user',function($r) {
                $r->where('is_fav',1);
            });
        })->latest()->take(10)->get();
        return view('frontend.index',$data);
    }


    public function pricing()
    {
        $data['subscriptions'] = Subscriptions::get();
        return view('frontend.pricing_list',$data);
    }

    public function contactUS()
    {
        return view('frontend.cotact_us');
    }

    public function contactUSMail(Request $request)
    {
        try
        {
            $to = 'info@smartqrscan.com';
            $from = $request->email;
            $message = $request->message;
            $mobile_number = $request->mobile_number;
            $business_name = $request->bussiness_name;
            $name = $request->name;
            $subject = 'Contact US';

            $html = "<h4>From - ".$name."</h4>";
            $html .= "<h4>Mobile - ".$mobile_number."</h4>";
            $html .= "<h4>Business Name - ".$business_name."</h4>";
            $html .= "<p>".$message."</p>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // More headers
            $headers .= 'From: <'.$from.'>' . "\r\n";

            mail($to,$subject,$html,$headers);

            return redirect()->back()->with('success','Mail has been Sent SuccessFully...');
        }
        catch (\Throwable $th)
        {
            return redirect()->back()->with('error','Internal Server Error!');
        }
    }

    public function QrGuide()
    {
        return view('frontend.qr_guide');
    }

    public function QrMenu()
    {
        return view('frontend.qr_menu');
    }
}
