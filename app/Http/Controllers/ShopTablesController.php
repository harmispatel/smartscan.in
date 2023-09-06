<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ShopTablesController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $data['shop_tables'] = ShopTables::where('shop_id',$shop_id)->orderBy('table_no','ASC')->get();
        return view('client.tables.tables',$data);
    }



    // Show the form for creating a new resource.
    public function create()
    {
        return view('client.tables.create_table');
    }



    // Store a newly created resource in storage.
    public function store(Request $request)
    {

        $request->validate([
            'start_table_number' => 'required',
            'end_table_number' => 'required',
        ]);

        if($request->start_table_number < 1 || $request->end_table_number < 1)
        {
            return redirect()->route('shop.tables')->with('error','Please Enter Valid Number...');
        }

        $total_table = range($request->start_table_number, $request->end_table_number);

        if(count($total_table) > 50)
        {
            return redirect()->route('shop.tables')->with('error',"Maximum Limit Reached You Can't Add More Then 50 at a Time");
        }

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $input['shop_id'] = $shop_id;

        for ($i=$request->start_table_number; $i<=$request->end_table_number; $i++)
        {
            $table = ShopTables::where('shop_id',$shop_id)->where('table_no',$i)->first();
            $table_id = isset($table['id']) ? $table['id'] : '';

            if(empty($table_id))
            {
                // Generate Shop Qr
                $new_shop_url = URL::to('/')."/".$shop_slug.'/table/'.$i;
                $qr_name = "table_".$i."_".time()."_qr.svg";
                $upload_path = public_path('client_uploads/shops/'.$shop_slug.'/tables/'.$qr_name);

                QrCode::format('svg')->margin(2)->size(200)->generate($new_shop_url, $upload_path);
                $input['qr_code'] = $qr_name;
                $input['table_no'] = $i;

                // Insert Table
                $shop_table = ShopTables::insert($input);
            }
        }

        return redirect()->route('shop.tables')->with('success','New Table has been Generated SuccessFully...');
    }



    // Change Status of Special Icons
    public function changeStatus(Request $request)
    {
        // Ingredient ID & Status
        $table_id = $request->id;
        $status = $request->status;

        try
        {
            $table = ShopTables::find($table_id);
            $table->status = $status;
            $table->update();

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



    // Function for the Print All Tables QR
    public function printTablesQR()
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $data['shop_details'] = Shop::where('id',$shop_id)->first();
        $data['shop_tables'] = ShopTables::where('shop_id',$shop_id)->orderBy('table_no','ASC')->get();

        return view('client.tables.tables_qr_print',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShopTables  $shopTables
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopTables $shopTables)
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
            $table_id = $request->id;
            $table_details = ShopTables::find($table_id);
            $table_qr = (isset($table_details['qr_code'])) ? $table_details['qr_code'] : '';

            if(!empty($table_qr) && file_exists('public/client_uploads/shops/'.$shop_slug.'/tables/'.$table_qr))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/tables/'.$table_qr);
            }

            ShopTables::where('id',$table_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Shop Table has been Removed SuccessFully...',
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
