<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RoomsController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $data['rooms'] = Rooms::where('shop_id',$shop_id)->orderBy('room_no','ASC')->get();
        return view('client.rooms.rooms',$data);
    }



    // Show the form for creating a new resource.
    public function create()
    {
        return view('client.rooms.create_room');
    }



    // Store a newly created resource in storage.
    public function store(Request $request)
    {

        $request->validate([
            'start_room_number' => 'required',
            'end_room_number' => 'required',
        ]);

        if($request->start_room_number < 1 || $request->end_room_number < 1)
        {
            return redirect()->route('rooms')->with('error','Please Enter Valid Number...');
        }

        $total_room = range($request->start_room_number, $request->end_room_number);

        if(count($total_room) > 50)
        {
            return redirect()->route('rooms')->with('error',"Maximum Limit Reached You Can't Add More Then 50 at a Time");
        }

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $input['shop_id'] = $shop_id;

        for ($i=$request->start_room_number; $i<=$request->end_room_number; $i++)
        {
            $room = Rooms::where('shop_id',$shop_id)->where('room_no',$i)->first();
            $room_id = isset($room['id']) ? $room['id'] : '';

            if(empty($room_id))
            {
                // Generate Shop Qr
                $new_shop_url = URL::to('/')."/".$shop_slug.'/room/'.$i;
                $qr_name = "room_".$i."_".time()."_qr.svg";
                $upload_path = public_path('client_uploads/shops/'.$shop_slug.'/rooms/'.$qr_name);

                QrCode::format('svg')->margin(2)->size(200)->generate($new_shop_url, $upload_path);
                $input['qr_code'] = $qr_name;
                $input['room_no'] = $i;

                // Insert Table
                $room = Rooms::insert($input);
            }
        }

        return redirect()->route('rooms')->with('success','New Room has been Generated SuccessFully...');
    }



    // Change Status of Special Icons
    public function changeStatus(Request $request)
    {
        // Ingredient ID & Status
        $room_id = $request->id;
        $status = $request->status;

        try
        {
            $room = Rooms::find($room_id);
            $room->status = $status;
            $room->update();

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



    // Function for the Print All Rooms QR
    public function printRoomsQR ()
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $data['shop_details'] = Shop::where('id',$shop_id)->first();
        $data['rooms'] = Rooms::where('shop_id',$shop_id)->orderBy('room_no','ASC')->get();

        return view('client.rooms.rooms_qr_print',$data);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rooms  $rooms
     * @return \Illuminate\Http\Response
     */
    public function edit(Rooms $rooms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rooms  $rooms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rooms $rooms)
    {
        //
    }



    // Remove the specified resource from storage.
    public function destroy(Request $request)
    {
        try
        {
            // Shop ID & Slug
            $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
            $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
            $room_id = $request->id;
            $room_details = Rooms::find($room_id);
            $room_qr = (isset($room_details['qr_code'])) ? $room_details['qr_code'] : '';

            if(!empty($room_qr) && file_exists('public/client_uploads/shops/'.$shop_slug.'/rooms/'.$room_qr))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/rooms/'.$room_qr);
            }

            Rooms::where('id',$room_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Room has been Removed SuccessFully...',
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
}
