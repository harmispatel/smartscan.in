<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodJunctionRequest;
use App\Models\FoodJunction;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FoodJunctionController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $data['foodjunctions'] = FoodJunction::get();
        return view('admin.foodjunctions.foodjunctions',$data);
    }



    // Show the form for creating a new resource.
    public function create()
    {
        $data['shops'] = Shop::get();
        return view('admin.foodjunctions.create_foodjunctions',$data);
    }



    // Store a newly created resource in storage.
    public function store(FoodJunctionRequest $request)
    {
        try
        {
            $junction_name = $request->name;
            $junction_slug = $request->junction_slug;
            $junction_description = $request->description;
            $shops = $request->shops;

            $junction = new FoodJunction;
            $junction->junction_name = $junction_name;
            $junction->junction_slug = $junction_slug;
            $junction->junction_description = $junction_description;
            $junction->shop_ids = serialize($shops);

            // Junction LOGO
            if($request->hasFile('junction_logo'))
            {
                $imgname = "junction_logo_".time().".". $request->file('junction_logo')->getClientOriginalExtension();
                $request->file('junction_logo')->move(public_path('admin_uploads/junctions_logo/'), $imgname);
                $junction->logo = $imgname;
            }

            // Generate Junction QR
            $new_shop_url = URL::to('/')."/junction/".$junction_slug;
            $qr_name = $junction_slug."-".time()."-qr.svg";
            $upload_path = public_path('admin_uploads/junctions_qr/'.$qr_name);
            QrCode::format('svg')->margin(2)->size(200)->generate($new_shop_url, $upload_path);

            $junction->junction_qr = $qr_name;
            $junction->save();

            return redirect()->route('food.junctions')->with('success','Junction has been Created SuccessFully...');

        }
        catch (\Throwable $th)
        {
            return redirect()->route('food.junctions')->with('error','Internal Server Error!');
        }

    }



    // Display the specified resource.
    public function show(FoodJunction $foodJunction)
    {
        //
    }



    // Show the form for editing the specified resource.
    public function edit($id)
    {
        $data['shops'] = Shop::get();
        $data['junction'] = FoodJunction::where('id',$id)->first();
        return view('admin.foodjunctions.edit_foodjunctions',$data);
    }



    // Update the specified resource in storage.
    public function update(FoodJunctionRequest $request)
    {
        try
        {
            $junction_name = $request->name;
            $junction_description = $request->description;
            $shops = $request->shops;
            $junction_id = $request->junction_id;

            $junction = FoodJunction::find($junction_id);
            $junction->junction_name = $junction_name;
            $junction->junction_description = $junction_description;
            $junction->shop_ids = serialize($shops);

            // Junction LOGO
            if($request->hasFile('junction_logo'))
            {
                // Delete old Logo
                $old_logo = isset($junction->logo) ? $junction->logo : "";
                if(!empty($old_logo) && file_exists('public/admin_uploads/junctions_logo/'.$old_logo))
                {
                    unlink('public/admin_uploads/junctions_logo/'.$old_logo);
                }

                $imgname = "junction_logo_".time().".". $request->file('junction_logo')->getClientOriginalExtension();
                $request->file('junction_logo')->move(public_path('admin_uploads/junctions_logo/'), $imgname);
                $junction->logo = $imgname;
            }

            $junction->update();

            return redirect()->route('food.junctions')->with('success','Junction has been Updated SuccessFully..');

        }
        catch (\Throwable $th)
        {
            return redirect()->route('food.junctions')->with('error','Internal Server Error!');
        }
    }



    // Remove the specified resource from storage.
    public function destroy($id)
    {
        // Get Food Junction Details
        $junction = FoodJunction::where('id',$id)->first();
        $junction_logo = isset($junction->logo) ? $junction->logo : '';
        $junction_qr = isset($junction->junction_qr) ? $junction->junction_qr : '';

        // Remove Junction Logo
        if(!empty($junction_logo) && file_exists('public/admin_uploads/junctions_logo/'.$junction_logo))
        {
            unlink('public/admin_uploads/junctions_logo/'.$junction_logo);
        }

        // Remove Junction QR
        if(!empty($junction_qr) && file_exists('public/admin_uploads/junctions_qr/'.$junction_qr))
        {
            unlink('public/admin_uploads/junctions_qr/'.$junction_qr);
        }

        // Delete FoodJunction
        FoodJunction::where('id',$id)->delete();

        return redirect()->route('food.junctions')->with('success','Junction has been Removed SuccessFully..');
    }



    // Change Status of Food Junction
    public function changeStatus(Request $request)
    {
        // Junction ID & Status
        $junction_id = $request->id;
        $status = $request->status;

        try
        {
            $junction = FoodJunction::find($junction_id);
            $junction->status = $status;
            $junction->update();

            return response()->json([
                'success' => 1,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
            ]);
        }
    }
}
