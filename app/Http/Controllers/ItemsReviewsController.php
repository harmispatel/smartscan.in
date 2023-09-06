<?php

namespace App\Http\Controllers;

use App\Models\ItemReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemsReviewsController extends Controller
{
    function index()
    {
        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['item_reviews'] = ItemReview::with(['item'])->orderBy('id','desc')->where('shop_id',$shop_id)->get();
        return view('client.reviews.item_reviews',$data);
    }


    // Destroy Item Reviews
    function destroy(Request $request)
    {
        $review_id = $request->id;
        try
        {
            ItemReview::where('id',$review_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Review has been Deleted SuccessFully...',
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
