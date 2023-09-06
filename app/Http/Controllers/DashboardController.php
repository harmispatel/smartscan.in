<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Items;
use App\Models\Languages;
use App\Models\Shop;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Display Admin Dashboard.
    public function index()
    {
        // Total Shops
        // $shop['total'] = Shop::count();
        // $data['shop'] = $shop;

        // Subscriptions Shop
        $data['subscriptions'] = Subscriptions::withCount(['user_subscriptions'])->where('status',1)->get();

        // Recente Clients
        $data['recent_clients'] = User::with(['hasOneShop','hasOneSubscription'])->where('user_type',2)->orderBy('id','DESC')->limit(10)->get();

        return view('admin.dashboard.dashboard',$data);
    }

    // Display Client Dashboard
    public function clientDashboard()
    {
        // Shop ID
        $data['shop_id'] = isset(Auth::user()->hasOneShop->Shop['id']) ? Auth::user()->hasOneShop->Shop['id'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($data['shop_id']);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $data['primary_language_detail'] = Languages::where('id',$primary_lang_id)->first();

        // Total Category Count
        $category['total_category'] = Category::where('shop_id',$data['shop_id'])->whereIn('category_type',['product_category','parent_category'])->count();
        $category['total_page'] = Category::where('shop_id',$data['shop_id'])->where('category_type','page')->count();
        $category['total_link'] = Category::where('shop_id',$data['shop_id'])->where('category_type','link')->count();
        $category['gallery'] = Category::where('shop_id',$data['shop_id'])->where('category_type','gallery')->count();
        $category['pdf_page'] = Category::where('shop_id',$data['shop_id'])->where('category_type','pdf_page')->count();
        $category['check_in'] = Category::where('shop_id',$data['shop_id'])->where('category_type','check_in')->count();

        // All Categories List
        $data['categories'] = Category::with(['categoryImages'])->where('shop_id',$data['shop_id'])->limit(8)->latest('created_at')->get();

        // Get All Items
        $data['items'] = Items::with('category')->where('shop_id',$data['shop_id'])->limit(8)->latest('created_at')->get();

        // Total Food Count
        $item['total'] = Items::where('shop_id',$data['shop_id'])->count();

        $data['category'] = $category;
        $data['item'] = $item;

        return view('client.dashboard.dashboard',$data);
    }

    // Function for Change Backend Language
    public function changeBackendLanguage(Request $request)
    {
        $lang_code = $request->langCode;

        session()->put('lang_code',$lang_code);

        return response()->json([
            'success' => 1,
        ]);
    }
}
