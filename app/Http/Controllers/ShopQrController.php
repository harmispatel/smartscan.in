<?php

namespace App\Http\Controllers;

use App\Models\QrSettings;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ShopQrController extends Controller
{
    public function index()
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['shop_details'] = Shop::where('id',$shop_id)->first();
        $data['qr_data'] = QrSettings::select('value')->where('shop_id',$shop_id)->first();
        return view('client.qrcode.view_qrcode',$data);
    }


    // Function for Setting QrCode
    public function QrCodeSettings(Request $request)
    {
        $html = '';
        $qr_size = $request->qr_size;
        $qr_style = $request->qr_style;
        $eye_style = $request->eye_style;
        $color_type = $request->color_type;
        $color_transparent = $request->color_transparent;
        $background_color_transparent = $request->background_color_transparent;

        $eye_inner_color =hexToRgb($request->eye_inner_color);
        $eye_outer_color =hexToRgb($request->eye_outer_color);
        $first_color =hexToRgb($request->first_color);
        $second_color =hexToRgb($request->second_color);
        $background_color =hexToRgb($request->background_color);


        try
        {
            // Shop Name & Url
            $shop_name = isset(Auth::user()->hasOneShop->shop['name']) ? Auth::user()->hasOneShop->shop['name'] : '';
            $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
            $new_shop_url = URL::to('/')."/".$shop_slug;

            // Generate QrCode
            $qrCode = QrCode::eyeColor(0,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(1,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(2,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eye($eye_style)->style($qr_style)
            ->backgroundColor($background_color['r'],$background_color['g'],$background_color['b'],$background_color_transparent)
            ->size($qr_size);

            if(!empty($color_type))
            {
                $qrCode->gradient($first_color['r'],$first_color['g'],$first_color['b'],$second_color['r'],$second_color['g'],$second_color['b'],$color_type);
            }
            else
            {
                $qrCode->color($first_color['r'],$first_color['g'],$first_color['b'],$color_transparent);
            }

            $qrCode = $qrCode->generate($new_shop_url);

            $html .= $qrCode;

            return response()->json([
                'success' => 1,
                'message' => 'Settings has been Changed SuccessFully.',
                'data'    => "$html",
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => 'Internal Server Error !',
            ]);
        }
    }


    // Function for Update QrCode Settings
    public function QrCodeUpdateSettings(Request $request)
    {

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $qr_size = $request->qr_size;
        $qr_style = $request->qr_style;
        $eye_style = $request->eye_style;
        $color_type = $request->color_type;
        $color_transparent = $request->color_transparent;
        $background_color_transparent = $request->background_color_transparent;

        $eye_inner_color =hexToRgb($request->eye_inner_color);
        $eye_outer_color =hexToRgb($request->eye_outer_color);
        $first_color =hexToRgb($request->first_color);
        $second_color =hexToRgb($request->second_color);
        $background_color =hexToRgb($request->background_color);

        $shop = Shop::find($shop_id);

        if($shop)
        {
            $shop_slug = $shop->shop_slug;
            $new_shop_url = URL::to('/')."/".$shop_slug;
            $qr_name = $shop_slug."_".time()."_qr.png";
            $upload_path = public_path('admin_uploads/shops_qr/'.$qr_name);

            // Old Qr PNG
            $old_qr_png = isset($shop->qr_code) ? $shop->qr_code : '';

            if(!empty($old_qr_png) && file_exists('public/admin_uploads/shops_qr/'.$old_qr_png))
            {
                unlink('public/admin_uploads/shops_qr/'.$old_qr_png);
            }

            // Old Qr SVG
            $old_qr_svg = isset($shop->qr_code) ? $shop->qr_code : '';
            $old_qr_svg_rep = (!empty($old_qr_svg)) ? str_replace('.png','.svg',$old_qr_svg) : '';

            if(!empty($old_qr_svg_rep) && file_exists('public/admin_uploads/shops_qr/'.$old_qr_svg_rep))
            {
                unlink('public/admin_uploads/shops_qr/'.$old_qr_svg_rep);
            }

            // Generate New Qr PNG
            $qrCodePng = QrCode::format('png')->margin(2)->eyeColor(0,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(1,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(2,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eye($eye_style)->style($qr_style)
            ->backgroundColor($background_color['r'],$background_color['g'],$background_color['b'],$background_color_transparent)
            ->size($qr_size);
            if(!empty($color_type))
            {
                $qrCodePng->gradient($first_color['r'],$first_color['g'],$first_color['b'],$second_color['r'],$second_color['g'],$second_color['b'],$color_type);
            }
            else
            {
                $qrCodePng->color($first_color['r'],$first_color['g'],$first_color['b'],$color_transparent);
            }
            $qrCodePng = $qrCodePng->generate($new_shop_url,$upload_path);

            // Generate New Qr SVG
            $qr_name_svg = str_replace('.png','.svg',$qr_name);
            $upload_path_svg = public_path('admin_uploads/shops_qr/'.$qr_name_svg);

            $qrCodeSvg = QrCode::format('svg')->margin(2)->eyeColor(0,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(1,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eyeColor(2,$eye_outer_color['r'],$eye_outer_color['g'],$eye_outer_color['b'],$eye_inner_color['r'],$eye_inner_color['g'],$eye_inner_color['b'])
            ->eye($eye_style)->style($qr_style)
            ->backgroundColor($background_color['r'],$background_color['g'],$background_color['b'],$background_color_transparent)
            ->size($qr_size);
            if(!empty($color_type))
            {
                $qrCodeSvg->gradient($first_color['r'],$first_color['g'],$first_color['b'],$second_color['r'],$second_color['g'],$second_color['b'],$color_type);
            }
            else
            {
                $qrCodeSvg->color($first_color['r'],$first_color['g'],$first_color['b'],$color_transparent);
            }
            $qrCodeSvg = $qrCodeSvg->generate($new_shop_url,$upload_path_svg);

            $shop->qr_code = $qr_name;
            $shop->update();

            // Update all Settings Into DB
            $qrdata = [
                'qr_size' => $qr_size,
                'qr_style' => $qr_style,
                'eye_style' => $eye_style,
                'color_type' => $color_type,
                'color_transparent' => $color_transparent,
                'background_color_transparent' => $background_color_transparent,
                'eye_inner_color' => $request->eye_inner_color,
                'eye_outer_color' => $request->eye_outer_color,
                'first_color' => $request->first_color,
                'second_color' => $request->second_color,
                'background_color' => $request->background_color,
            ];

            $setting = QrSettings::where('shop_id',$shop_id)->first();
            $setting_id = isset($setting->id) ? $setting->id : '';

            if(!empty($setting_id) || $setting_id != '')
            {
                $qr_setting = QrSettings::find($setting_id);
                $qr_setting->value = serialize($qrdata);
                $qr_setting->update();
            }
            else
            {
                $qr_setting = new QrSettings();
                $qr_setting->shop_id = $shop_id;
                $qr_setting->value = serialize($qrdata);
                $qr_setting->save();
            }

        }

        return redirect()->back()->with('success','Qrcode has been Changed SuccessFully....');
    }

}
