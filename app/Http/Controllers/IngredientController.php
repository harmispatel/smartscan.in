<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class IngredientController extends Controller
{
    // Display all Special Icons
    public function index()
    {
        $data['ingredients'] = Ingredient::where('shop_id',NULL)->get();
        return view('admin.ingredients.ingredients',$data);
    }



    // Display Special Icons of Client Shop
    public function specialIcons()
    {
        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['special_icons'] = Ingredient::where('shop_id',$shop_id)->get();
        return view('client.special_icons.special_icons',$data);
    }



    // Create New Special Icons
    public function insert()
    {
        return view('admin.ingredients.new_ingredients');
    }



    // Create New Special Icons for Client Shop
    public function insertSpecialIcons()
    {
        return view('client.special_icons.new_special_icons');
    }



    // Store Newly Created Ingredient
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'icon' => 'required|mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG|dimensions:width=80,height=80',
        ]);

        $ingredient = new Ingredient();
        $ingredient->name = $request->name;
        $ingredient->status = isset($request->status) ? $request->status : 0;

        // Insert Ingredient Icon if is Exists
        if($request->hasFile('icon'))
        {
            $imgname = "ingredient_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('admin_uploads/ingredients/'), $imgname);
            $ingredient->icon = $imgname;
        }

        $ingredient->save();


        // Store New Special Icons to Client
        $all_shops = Shop::get();

        if(count($all_shops) > 0)
        {
            foreach($all_shops as $shop)
            {
                $shop_id = (isset($shop['id'])) ? $shop['id'] : '';
                $shop_slug = (isset($shop['shop_slug'])) ? $shop['shop_slug'] : '';

                $new_special_icon = new Ingredient();
                $new_special_icon->shop_id = $shop_id;
                $new_special_icon->parent_id = $ingredient->id;
                $new_special_icon->name = $ingredient->name;
                $new_special_icon->status = $ingredient->status;
                $new_special_icon->icon = $ingredient->icon;
                $new_special_icon->save();

                if(!empty($ingredient->icon) && file_exists('public/admin_uploads/ingredients/'.$ingredient->icon))
                {
                    File::copy(public_path('admin_uploads/ingredients/'.$ingredient->icon), public_path('client_uploads/shops/'.$shop_slug.'/ingredients/'.$ingredient->icon));
                }
            }
        }

        return redirect()->route('ingredients')->with('success','Special Icon has been Inserted SuccessFully....');

    }



    // Store Newly Created Special Icon For Client Shop
    public function storeSpecialIcons(Request $request)
    {

        $shop_id = (isset(Auth::user()->hasOneShop->shop['id'])) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'name' => 'required',
            'icon' => 'required|mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG|dimensions:width=80,height=80',
        ]);

        $ingredient = new Ingredient();
        $ingredient->name = $request->name;
        $ingredient->shop_id = $shop_id;
        $ingredient->status = isset($request->status) ? $request->status : 0;

        // Insert Ingredient Icon if is Exists
        if($request->hasFile('icon'))
        {
            $imgname = "ingredient_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/ingredients/'), $imgname);
            $ingredient->icon = $imgname;
        }

        $ingredient->save();

        return redirect()->route('special.icons')->with('success','Special Icon has been Inserted SuccessFully....');
    }



    // Edit Specific Special Icon
    public function edit($id)
    {
        $data['ingredient'] = Ingredient::where('id',$id)->first();
        return view('admin.ingredients.edit_ingredients',$data);
    }



    // Edit Specific Special Icon for Client Shop
    public function editSpecialIcons($id)
    {
        $data['special_icon'] = Ingredient::where('id',$id)->first();
        return view('client.special_icons.edit_special_icons',$data);
    }



    // Change Status of Special Icons
    public function changeStatus(Request $request)
    {
        // Ingredient ID & Status
        $ingredient_id = $request->id;
        $status = $request->status;

        try
        {
            $ingredient = Ingredient::find($ingredient_id);
            $ingredient->status = $status;
            $ingredient->update();

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



    // Update Specific Special Icon
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'icon' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG|dimensions:width=80,height=80',
        ]);

        $ingredient = Ingredient::find($request->ingredient_id);

        $ing_old_name = (isset($ingredient->name)) ? $ingredient->name : '';

        $ingredient->name = $request->name;
        $ingredient->status = isset($request->status) ? $request->status : 0;

        // Insert Ingredient Icon if is Exists
        if($request->hasFile('icon'))
        {
            // Delete old Icon
            $old_icon = isset($ingredient->icon) ? $ingredient->icon : "";
            if(!empty($old_icon) && file_exists('public/admin_uploads/ingredients/'.$old_icon))
            {
                unlink('public/admin_uploads/ingredients/'.$old_icon);
            }

            $imgname = "ingredient_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('admin_uploads/ingredients/'), $imgname);
            $ingredient->icon = $imgname;
        }

        $ingredient->update();


        // Update Same Special Icon in Client Shop
        $client_ing = Ingredient::where('parent_id',$request->ingredient_id)->where('name',$ing_old_name)->get();

        if(count($client_ing) > 0)
        {
            foreach ($client_ing as $ing)
            {
                $ing_id = (isset($ing['id'])) ? $ing['id'] : '';
                if(!empty($ing_id))
                {
                    $ing_dt = Ingredient::find($ing_id);
                    $ing_dt->name = $request->name;
                    $ing_dt->update();
                }
            }
        }

        return redirect()->route('ingredients')->with('success','Special Icon has been Updated SuccessFully....');
    }



    // Update Specific Special Icon for Client Shop
    public function updateSpecialIcons(Request $request)
    {
        $shop_slug = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'name' => 'required',
            'icon' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG|dimensions:width=80,height=80',
        ]);

        $special_icon = Ingredient::find($request->ingredient_id);
        $special_icon->name = $request->name;
        $special_icon->status = isset($request->status) ? $request->status : 0;

        // Insert Ingredient Icon if is Exists
        if($request->hasFile('icon'))
        {
            // Delete old Icon
            $old_icon = isset($special_icon->icon) ? $special_icon->icon : "";
            if(!empty($old_icon) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$old_icon))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$old_icon);
            }

            $imgname = "ingredient_".time().".". $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('client_uploads/shops/'.$shop_slug.'/ingredients/'), $imgname);
            $special_icon->icon = $imgname;
        }

        $special_icon->update();

        return redirect()->route('special.icons')->with('success','Special Icon has been Updated SuccessFully....');
    }



    // Destroy (Delete) Ingredient
    public function destroy($id)
    {
        // Get Ingredient Details
        $ingredient = Ingredient::where('id',$id)->first();
        $ingredient_icon = isset($ingredient->icon) ? $ingredient->icon : '';
        if(!empty($ingredient_icon) && file_exists('public/admin_uploads/ingredients/'.$ingredient_icon))
        {
            unlink('public/admin_uploads/ingredients/'.$ingredient_icon);
        }

        // Delete Child Ingredient
        $child_ing = Ingredient::where('parent_id',$id)->get();
        if(count($child_ing) > 0)
        {
            foreach($child_ing as $ing)
            {
                $ing_id = (isset($ing['id'])) ? $ing['id'] : '';
                $ing_icon = (isset($ing['icon'])) ? $ing['icon'] : '';
                $shop_id = (isset($ing['shop_id'])) ? $ing['shop_id'] : '';
                $shop = Shop::where('id',$shop_id)->first();
                $shop_slug = (isset($shop['shop_slug'])) ? $shop['shop_slug'] : '';

                if(!empty($ing_icon) && !empty($shop_slug) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$ing_icon);
                }
                Ingredient::where('id',$ing_id)->delete();
            }
        }

        // Delete Ingredient
        Ingredient::where('id',$id)->delete();

        return redirect()->route('ingredients')->with('success','Ingredient has been Removed SuccessFully..');
    }



    // Destroy (Delete) Special Icons for Client Shop
    public function destroySpecialIcons($id)
    {
        $shop_slug = (isset(Auth::user()->hasOneShop->shop['shop_slug'])) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Get Special Icon Details
        $special_icon = Ingredient::where('id',$id)->first();
        $special_icon_img = isset($special_icon->icon) ? $special_icon->icon : '';
        if(!empty($special_icon_img) && file_exists('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$special_icon_img))
        {
            unlink('public/client_uploads/shops/'.$shop_slug.'/ingredients/'.$special_icon_img);
        }

        // Delete Special Icon
        Ingredient::where('id',$id)->delete();

        return redirect()->route('special.icons')->with('success','Special Icon has been Removed SuccessFully..');
    }
}
