<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscriptions;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function index()
    {
        $data['subscriptions'] = Subscriptions::get();
        return view('admin.subscription.subscription',$data);
    }


    public function insert()
    {
        return view('admin.subscription.new_subscription');
    }


    public function store(SubscriptionRequest $request)
    {
        $title = $request->title;
        $price = $request->price;
        $duration = $request->duration;
        $status = isset($request->status) ? $request->status : 0;
        $description = $request->description;

        // Permissions
        $permission['add_edit_clone_theme'] = (isset($request->add_edit_clone_theme)) ? $request->add_edit_clone_theme : 0;
        $permission['page'] = (isset($request->page)) ? $request->page : 0;
        $permission['banner'] = (isset($request->banner)) ? $request->banner : 0;
        $permission['pdf_file'] = (isset($request->pdf_file)) ? $request->pdf_file : 0;
        $permission['link'] = (isset($request->link)) ? $request->link : 0;
        $permission['gallery'] = (isset($request->gallery)) ? $request->gallery : 0;
        $permission['check_in'] = (isset($request->check_in)) ? $request->check_in : 0;
        $permission['ordering'] = (isset($request->ordering)) ? $request->ordering : 0;
        $permission['special_icons'] = (isset($request->special_icons)) ? $request->special_icons : 0;

        $permission = serialize($permission);

        // Insert New Subscription
        $subscription = new Subscriptions();
        $subscription->name = $title;
        $subscription->price = $price;
        $subscription->duration = $duration;
        $subscription->status = $status;
        $subscription->permissions = $permission;
        $subscription->description = $description;

        // Insert Subscription Icon if is Exists
        if($request->hasFile('icon'))
        {
            $imgname = "subscription_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('admin_uploads/subscriptions/'), $imgname);
            $subscription->icon = $imgname;
        }

        $subscription->save();

        return redirect()->route('subscriptions')->with('success','Subscription has been Inserted SuccessFully....');

    }


    public function destroy(Request $request)
    {
        try
        {
            $subscription_id = $request->id;

            // Get Subscription Details
            $subscription = Subscriptions::where('id',$subscription_id)->first();
            $subscription_icon = isset($subscription->icon) ? $subscription->icon : '';

            if(!empty($subscription_icon) && file_exists('public/admin_uploads/subscriptions/'.$subscription_icon))
            {
                unlink('public/admin_uploads/subscriptions/'.$subscription_icon);
            }

            // Delete Subscription
            Subscriptions::where('id',$subscription_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Subscription has been Removed SuccessFully..',
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



    public function edit($id)
    {
        try
        {
            $data['subscription'] = Subscriptions::where('id',$id)->first();

            if($data['subscription'])
            {
                return view('admin.subscription.edit_subscription',$data);
            }
            return redirect()->route('subscriptions')->with('error', 'Something went wrong!');
        }
        catch (\Throwable $th)
        {
            return redirect()->route('subscriptions')->with('error', 'Something went wrong!');
        }
    }


    public function update(SubscriptionRequest $request)
    {
        $id = $request->subscription_id;
        $title = $request->title;
        $price = $request->price;
        $duration = $request->duration;
        $status = isset($request->status) ? $request->status : 0;
        $description = $request->description;

        // Permissions
        $permission['add_edit_clone_theme'] = (isset($request->add_edit_clone_theme)) ? $request->add_edit_clone_theme : 0;
        $permission['page'] = (isset($request->page)) ? $request->page : 0;
        $permission['banner'] = (isset($request->banner)) ? $request->banner : 0;
        $permission['pdf_file'] = (isset($request->pdf_file)) ? $request->pdf_file : 0;
        $permission['link'] = (isset($request->link)) ? $request->link : 0;
        $permission['gallery'] = (isset($request->gallery)) ? $request->gallery : 0;
        $permission['check_in'] = (isset($request->check_in)) ? $request->check_in : 0;
        $permission['ordering'] = (isset($request->ordering)) ? $request->ordering : 0;
        $permission['special_icons'] = (isset($request->special_icons)) ? $request->special_icons : 0;

        $permission = serialize($permission);

        $subscription = Subscriptions::find($id);
        $subscription->name = $title;
        $subscription->price = $price;
        $subscription->duration = $duration;
        $subscription->status = $status;
        $subscription->permissions = $permission;
        $subscription->description = $description;

        // Insert Subscription Icon if is Exists
        if($request->hasFile('icon'))
        {
            // Delete old Icon
            $old_icon = isset($subscription->icon) ? $subscription->icon : "";
            if(!empty($old_icon) && file_exists('public/admin_uploads/subscriptions/'.$old_icon))
            {
                unlink('public/admin_uploads/subscriptions/'.$old_icon);
            }

            $imgname = "subscription_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('admin_uploads/subscriptions/'), $imgname);
            $subscription->icon = $imgname;
        }

        $subscription->update();

        return redirect()->route('subscriptions')->with('success','Subscription has been Updated SuccessFully....');

    }


}
