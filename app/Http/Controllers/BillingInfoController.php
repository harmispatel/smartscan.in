<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingInfoController extends Controller
{
    public function billingInfo()
    {
        $data['expire_date'] =  (isset(Auth::user()->hasOneSubscription['end_date'])) ? \Carbon\Carbon::now()->diffInDays(Auth::user()->hasOneSubscription['end_date'], false) : '';
        $data['user'] = User::with(['hasOneCountry'])->where('id',Auth::user()->id)->first();
        return view('client.billing_info.billing_info',$data);
    }

    public function clientSubscription()
    {
        $data['expire_date'] =  (isset(Auth::user()->hasOneSubscription['end_date'])) ? \Carbon\Carbon::now()->diffInDays(Auth::user()->hasOneSubscription['end_date'], false) : '';
        return view('client.billing_info.client_subscription',$data);
    }

    public function editBillingInfo()
    {
        $data['expire_date'] =  (isset(Auth::user()->hasOneSubscription['end_date'])) ? \Carbon\Carbon::now()->diffInDays(Auth::user()->hasOneSubscription['end_date'], false) : '';
        $data['user'] = User::where('id',Auth::user()->id)->first();
        $data['countries'] = Country::get();
        return view('client.billing_info.edit_billing_info',$data);
    }

    public function updateBillingInfo(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
            'gst_number' => 'nullable|min:15',
        ]);

        if($request->form_type == 'invoice')
        {
            $request->validate([
                'vat_id' => 'required',
            ]);
        }

        $user = User::find($request->user_id);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->company = $request->company;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->country = $request->country;
        $user->zipcode = $request->zipcode;
        $user->vat_id = $request->vat_id;
        $user->gemi_id = $request->gemi_id;
        $user->gst_number = $request->gst_number;
        $user->sgst = (isset($request->sgst)) ? $request->sgst : 0;
        $user->cgst = (isset($request->cgst)) ? $request->cgst : 0;
        $user->mobile = $request->mobile;
        $user->telephone = $request->telephone;
        $user->tax_office = $request->tax_office;
        $user->update();

        return redirect()->route('billing.info')->with('success', "Billing Information has Been Updated SuccessFully...");

    }
}
