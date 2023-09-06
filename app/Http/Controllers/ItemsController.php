<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\Category;
use App\Models\CategoryProductTags;
use App\Models\Ingredient;
use App\Models\ItemPrice;
use App\Models\ItemReview;
use App\Models\Items;
use App\Models\ItemsVisit;
use App\Models\Languages;
use App\Models\Option;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemsController extends Controller
{
    public function index($id="")
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['ingredients'] = Ingredient::where('shop_id',$shop_id)->get();
        $data['tags'] = Tags::where('shop_id',$shop_id)->get();
        $data['options'] = Option::where('shop_id',$shop_id)->get();
        $data['categories'] = Category::where('shop_id',$shop_id)->where('category_type','product_category')->get();

        if(!empty($id) || $id != '')
        {
            $data['cat_id'] = $id;
            $data['category'] = Category::where('id',$id)->first();
            $data['items'] = Items::where('category_id',$id)->where('shop_id',$shop_id)->orderBy('order_key')->get();
            $data['cat_tags'] = CategoryProductTags::join('tags','tags.id','category_product_tags.tag_id')->orderBy('tags.order')->where('category_id',$id)->where('tags.shop_id','=',$shop_id)->get()->unique('tag_id');
        }
        else
        {
            $data['cat_id'] = '';
            $data['category'] = "All";
            $data['items'] = Items::orderBy('order_key')->where('shop_id',$shop_id)->get();
            $data['cat_tags'] = CategoryProductTags::join('tags','tags.id','category_product_tags.tag_id')->where('tags.shop_id','=',$shop_id)->orderBy('tags.order')->get()->unique('tag_id');
        }

        return view('client.items.items',$data);
    }



    // Function for Store Newly Create Item
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required',
            'category'   => 'required',
        ]);

        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $item_name_key = $lang_code."_name";
        $item_calories_key = $lang_code."_calories";
        $item_description_key = $lang_code."_description";
        $item_price_label_key = $lang_code."_label";

        $max_item_order_key = Items::max('order_key');
        $item_order = (isset($max_item_order_key) && !empty($max_item_order_key)) ? ($max_item_order_key + 1) : 1;

        $category_id = $request->category;
        $type = $request->type;
        $name = $request->name;
        $calories = $request->calories;
        $description = $request->description;
        $discount_type = $request->discount_type;
        $discount = $request->discount;
        $is_new = isset($request->is_new) ? $request->is_new : 0;
        $as_sign = isset($request->is_sign) ? $request->is_sign : 0;
        $published = isset($request->published) ? $request->published : 0;
        $review_rating = isset($request->review_rating) ? $request->review_rating : 0;
        $day_special = isset($request->day_special) ? $request->day_special : 0;
        $ingredients = (isset($request->ingredients) && count($request->ingredients) > 0) ? serialize($request->ingredients) : '';
        $options = (isset($request->options) && count($request->options) > 0) ? serialize($request->options) : '';
        $tags = isset($request->tags) ? $request->tags : [];


        $price_array['price'] = isset($request->price['price']) ? array_filter($request->price['price']) : [];
        $price_array['label'] = isset($request->price['label']) ? $request->price['label'] : [];

        if(count($price_array['price']) > 0)
        {
            $price = $price_array;
        }
        else
        {
            $price = [];
        }


        try
        {
            $item = new Items();
            $item->category_id = $category_id;
            $item->shop_id = $shop_id;
            $item->type = $type;

            $item->name = $name;
            $item->calories = $calories;
            $item->description = $description;

            $item->$item_name_key = $name;
            $item->$item_calories_key = $calories;
            $item->$item_description_key = $description;

            $item->discount_type = $discount_type;
            $item->discount = $discount;
            $item->published = $published;
            $item->order_key = $item_order;
            $item->ingredients = $ingredients;
            $item->options = $options;
            $item->is_new = $is_new;
            $item->as_sign = $as_sign;
            $item->review = $review_rating;
            $item->day_special = $day_special;

            // Insert Item Image if is Exists
            if(isset($request->og_image) && !empty($request->og_image) && $request->hasFile('image'))
            {
                $og_image = $request->og_image;
                $image_arr = explode(";base64,", $og_image);
                $image_base64 = base64_decode($image_arr[1]);

                $imgname = "item_".time().".". $request->file('image')->getClientOriginalExtension();
                $img_path = public_path('client_uploads/shops/'.$shop_slug.'/items/'.$imgname);
                file_put_contents($img_path,$image_base64);
                // $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/items/'), $imgname);
                $item->image = $imgname;
            }

            $item->save();

            // Store Item Price
            if(count($price) > 0)
            {
                $price_arr = $price['price'];
                $label_arr = $price['label'];

                if(count($price_arr) > 0)
                {
                    foreach($price_arr as $key => $price_val)
                    {
                        $label_val = isset($label_arr[$key]) ? $label_arr[$key] : '';
                        $new_price = new ItemPrice();
                        $new_price->item_id = $item->id;
                        $new_price->shop_id = $shop_id;
                        $new_price->price = $price_val;
                        $new_price->label = $label_val;
                        $new_price->$item_price_label_key = $label_val;
                        $new_price->save();
                    }
                }
            }


            // Insert & Update Tags
            if(count($tags) > 0)
            {
                foreach($tags as $val)
                {
                    $findTag = Tags::where($item_name_key,$val)->where('shop_id',$shop_id)->first();
                    $tag_id = (isset($findTag->id) && !empty($findTag->id)) ? $findTag->id : '';

                    if(!empty($tag_id) || $tag_id != '')
                    {
                        $tag = Tags::find($tag_id);
                        $tag->name = $val;
                        $tag->$item_name_key = $val;
                        $tag->update();
                    }
                    else
                    {
                        $max_order = Tags::max('order');
                        $order = (isset($max_order) && !empty($max_order)) ? ($max_order + 1) : 1;

                        $tag = new Tags();
                        $tag->shop_id = $shop_id;
                        $tag->name = $val;
                        $tag->$item_name_key = $val;
                        $tag->order = $order;
                        $tag->save();
                    }

                    if($tag->id)
                    {
                        $cat_pro_tag = new CategoryProductTags();
                        $cat_pro_tag->tag_id = $tag->id;
                        $cat_pro_tag->category_id = $category_id;
                        $cat_pro_tag->item_id = $item->id;
                        $cat_pro_tag->save();
                    }
                }
            }

            return response()->json([
                'success' => 1,
                'message' => "Item has been Inserted SuccessFully....",
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }

    }



    // Function for Delete Item
    public function destroy(Request $request)
    {
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        try
        {
            $id = $request->id;

            $item = Items::where('id',$id)->first();
            $item_image = isset($item->image) ? $item->image : '';
            $cat_id = isset($item->category_id) ? $item->category_id : '';

            // Delete Item Image
            if(!empty($item_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image);
            }

            // Delete Item Category Tags
            CategoryProductTags::where('item_id',$id)->where('category_id',$cat_id)->delete();

            // Delete Item Visits
            ItemsVisit::where('item_id',$id)->delete();

            // Delete Item Prices
            ItemPrice::where('item_id',$id)->delete();

            // Delete Item Reviews
            ItemReview::where('item_id',$id)->delete();

            // Delete Item
            Items::where('id',$id)->delete();

            return response()->json([
                'success' => 1,
                'message' => "Item has been Deleted SuccessFully....",
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }
    }



    // Function for Change Item Status
    public function status(Request $request)
    {
        try
        {
            $id = $request->id;
            $published = $request->status;

            $item = Items::find($id);
            $item->published = $published;
            $item->update();

            return response()->json([
                'success' => 1,
                'message' => "Item Status has been Changed Successfully..",
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }
    }



    // Function for Filtered Items
    public function searchItems(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
        $keyword = $request->keywords;
        $cat_id = $request->id;

        if(session()->has('lang_code'))
        {
            $curr_lang_code = session()->get('lang_code');
        }
        else
        {
            $curr_lang_code = 'en';
        }

        try
        {
            $name_key = $curr_lang_code."_name";
            if(!empty($cat_id))
            {
                $items = Items::where($name_key,'LIKE','%'.$keyword.'%')->where('category_id',$cat_id)->where('shop_id',$shop_id)->get();
            }
            else
            {
                $items = Items::where($name_key,'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id)->get();
            }
            $html = '';

            if(count($items) > 0)
            {
                foreach($items as $item)
                {
                    $newStatus = ($item->published == 1) ? 0 : 1;
                    $checked = ($item->published == 1) ? 'checked' : '';

                    if(!empty($item->image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->image))
                    {
                        $image = asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item->image);
                    }
                    else
                    {
                        $image = asset('public/client_images/not-found/no_image_1.jpg');
                    }

                    $html .= '<div class="col-md-3">';
                        $html .= '<div class="item_box">';
                            $html .= '<div class="item_img">';
                                $html .= '<a><img src="'.$image.'" class="w-100"></a>';
                                $html .= '<div class="edit_item_bt">';
                                    $html .= '<button class="btn edit_category" onclick="editCategory('.$item->id.')">EDIT ITEM.</button>';
                                $html .= '</div>';
                                $html .= '<a class="delet_bt" onclick="deleteItem('.$item->id.')" style="cursor: pointer;"><i class="fa-solid fa-trash"></i></a>';
                                $html .= '<a class="cat_edit_bt" onclick="editItem('.$item->id.')">
                                <i class="fa-solid fa-edit"></i>
                            </a>';
                            $html .= '</div>';
                            $html .= '<div class="item_info">';
                                $html .= '<div class="item_name">';
                                    $html .= '<h3>'.$item->en_name.'</h3>';
                                    $html .= '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" role="switch" id="status" onclick="changeStatus('.$item->id.','.$newStatus.')" value="1" '.$checked.'></div>';
                                $html .= '</div>';
                                $html .= '<h2>Product</h2>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';


                }
            }

            $html .= '<div class="col-md-3">';
                $html .= '<div class="item_box">';
                    $html .= '<div class="item_img add_category">';
                        $html .= '<a data-bs-toggle="modal" data-bs-target="#addItemModal" class="add_category_bt" id="NewItemBtn"><i class="fa-solid fa-plus"></i></a>';
                    $html .= '</div>';
                    $html .= '<div class="item_info text-center"><h2>Product</h2></div>';
                $html .= '</div>';
            $html .= '</div>';

            return response()->json([
                'success' => 1,
                'message' => "Item has been retrived Successfully...",
                'data'    => $html,
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }

    }



    // Function for Edit Item
    public function edit(Request $request)
    {
        $item_id = $request->id;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        try
        {
            // Item Details
            $item = Items::where('id',$item_id)->first();

            // Categories
            $categories = Category::where('shop_id',$shop_id)->where('category_type','product_category')->get();

            // Ingredients
            $ingredients = Ingredient::where('shop_id',$shop_id)->get();

            // Order Attributes
            $options = Option::where('shop_id',$shop_id)->get();

            // Tags
            $tags = Tags::where('shop_id',$shop_id)->get();

            // ModalName
            $modalName = "'editItemModal'";

            // Subscrption ID
            $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

            // Get Package Permissions
            $package_permissions = getPackagePermission($subscription_id);

            // Get Language Settings
            $language_settings = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

            // Primary Language Details
            $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
            $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
            $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';
            $item_name_key = $primary_lang_code."_name";
            $item_desc_key = $primary_lang_code."_description";
            $item_price_label_key = $primary_lang_code."_label";
            $option_title_key = $primary_lang_code."_title";
            $primary_input_lang_code = "'$primary_lang_code'";

            // Item Details
            $item_type = (isset($item['type'])) ? $item['type'] : '';
            $category_id = (isset($item['category_id'])) ? $item['category_id'] : '';
            $default_image = asset('public/client_images/not-found/no_image_1.jpg');
            $item_image = (isset($item['image']) && !empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) : "";
            $delete_item_image_url = route('items.delete.image',$item_id);
            $item_name = (isset($item[$item_name_key])) ? $item[$item_name_key] : '';
            $item_desc = (isset($item[$item_desc_key])) ? $item[$item_desc_key] : '';
            $item_ingredients = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
            $price_array = ItemPrice::where('item_id',$item['id'])->where('shop_id',$shop_id)->get();
            $item_cat_tags = CategoryProductTags::with(['hasOneTag'])->where('item_id',$item['id'])->where('category_id',$item['category_id'])->get();
            $calories = isset($item[$primary_lang_code."_calories"]) ? $item[$primary_lang_code."_calories"] : '';
            $item_options = (isset($item['options']) && !empty($item['options'])) ? unserialize($item['options']) : [];
            $item_published = (isset($item['published']) && $item['published'] == 1) ? 'checked' : '';
            $review_rating = (isset($item['review']) && $item['review'] == 1) ? 'checked' : '';
            $item_is_new = (isset($item['is_new']) && $item['is_new'] == 1) ? 'checked' : '';
            $item_as_sign = (isset($item['as_sign']) && $item['as_sign'] == 1) ? 'checked' : '';
            $item_day_special = (isset($item['day_special']) && $item['day_special'] == 1) ? 'checked' : '';
            $discount = (isset($item['discount']) && !empty($item['discount'])) ? $item['discount'] : 0;

            // Item Category Tags Array
            if(count($item_cat_tags) > 0)
            {
                foreach ($item_cat_tags as $key => $value)
                {
                    $primary_tag_data[] = isset($value->hasOneTag[$primary_lang_code.'_name']) ? $value->hasOneTag[$primary_lang_code.'_name'] : '';
                }
            }
            else
            {
                $primary_tag_data = [];
            }

            // Additional Languages
            $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

            if(count($additional_languages) > 0)
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateItemByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                    // Additional Language
                    foreach($additional_languages as $value)
                    {
                        // Additional Language Details
                        $add_lang_detail = Languages::where('id',$value->language_id)->first();
                        $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                        $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                        $html .= '<a class="text-uppercase" onclick="updateItemByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                    }
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="edit_item_form" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';

                            // Item Type
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="type">'.__('Type').'</label>';
                                    $html .= '<select name="type" id="type" class="form-select" onchange="togglePrice('.$modalName.')">';
                                        $html .= '<option value="1"';
                                            if($item_type == 1)
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .='>Product</option>';
                                        $html .= '<option value="2"';
                                            if($item_type == 2)
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>Divider</option>';
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Category
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="category">'. __('Category').'</label>';
                                    $html .= '<select name="category" id="category" class="form-select">';
                                            $html .= '<option value="">Choose Category</option>';
                                            if(count($categories) > 0)
                                            {
                                                foreach ($categories as $cat)
                                                {
                                                    $html .= '<option value="'.$cat['id'].'"';
                                                        if($category_id == $cat['id'])
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .= '>'.$cat[$primary_lang_code."_name"].'</option>';
                                                }
                                            }
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Item Name
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="item_name">'.__('Name').'</label>';
                                    $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$item_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Price
                            $html .= '<div class="row price_div priceDiv">';
                                $html .= '<div class="col-md-12" id="priceDiv">';
                                    $html .= '<label class="form-label">'.__('Price').'</label>';
                                    if(isset($price_array) && count($price_array) > 0)
                                    {
                                        foreach($price_array as $key => $price_arr)
                                        {
                                            $price_label = isset($price_arr[$item_price_label_key]) ? $price_arr[$item_price_label_key] : '';
                                            $price_count = $key + 1;

                                            $html .= '<div class="row mb-3 align-items-center price price_'.$price_count.'">';
                                                $html .= '<div class="col-md-5 mb-1">';
                                                    $html .= '<input type="text" name="price[price][]" class="form-control" placeholder="Enter Price" value="'.$price_arr['price'].'">';
                                                    $html .= '<input type="hidden" name="price[priceID][]" value="'.$price_arr['id'].'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-6 mb-1">';
                                                    $html .= '<input type="text" name="price[label][]" class="form-control" placeholder="Enter Price Label" value="'.$price_label.'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-1 mb-1">';
                                                    $html .= '<a onclick="deleteItemPrice('.$price_arr['id'].','.$price_count.')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';

                            // Price Increment Button
                            $html .= '<div class="row mb-3 price_div priceDiv">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<a onclick="addPrice(\'editItemModal\')" class="btn addPriceBtn btn-info text-white">'.__('Add Price').'</a>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button for Show & Hide More Details
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 text-center">';
                                    $html .= '<a class="btn btn-sm btn-primary" style="cursor: pointer" onclick="toggleMoreDetails(\'editItemModal\')" id="more_dt_btn">More Details.. <i class="bi bi-eye-slash"></i></a>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row" id="more_details" style="display: none;">';

                                // Discount Type
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Discount Type').'</label>';
                                    $html .= '<select name="discount_type" id="discount_type" class="form-control">';
                                        $html .= '<option value="percentage" ';
                                            if($item['discount_type'] == 'percentage')
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>'.__('Percentage %').'</option>';
                                        $html .= '<option value="fixed" ';
                                            if($item['discount_type'] == 'fixed')
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>'.__('Fixed Amount').'</option>';
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Discount
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Discount').'</label>';
                                    $html .= '<input type="number" name="discount" id="discount" class="form-control" value="'.$discount.'">';
                                $html .= '</div>';

                                // Description
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Desription').'</label>';
                                    $html .= '<textarea name="item_description" id="item_description" class="form-control item_description" rows="3">'.$item_desc.'</textarea>';
                                $html .= '</div>';

                                // Image Section
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label">'.__('Image').'</label>';
                                    $html .= '<input type="file" name="item_image" id="item_image" class="form-control item_image" onchange="imageCropper(\'edit_item_form\',this)" style="display:none">';
                                    $html .= '<input type="hidden" name="og_image" id="og_image" class="og_image">';

                                    if(!empty($item_image))
                                    {
                                        $html .= '<div class="row" id="edit-img">';
                                            $html .= '<div class="col-md-3">';
                                                $html .= '<div class="position-relative" id="itemImage">';
                                                    $html .= '<label style="cursor:pointer" for="item_image"><img src="'.$item_image.'" class="w-100" style="border-radius:10px;"></label>';
                                                    $html .= '<a href="'.$delete_item_image_url.'" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: -45px;"><i class="bi bi-trash"></i></a>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        $html .= '</div>';

                                        $html .= '<div class="row mt-2" id="rep-image" style="display:none;">';
                                            $html .= '<div class="col-md-3" id="img-label">';
                                                $html .= '<label for="item_image" style="cursor: pointer">';
                                                    $html .= '<img src="" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw">';
                                                $html .= '</label>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    else
                                    {
                                        $html .= '<div class="mt-3" id="itemImage">';
                                            $html .= '<div class="col-md-3" id="img-label">';
                                                $html .= '<label style="cursor:pointer;" for="item_image"><img src="'.$default_image.'" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw"></label>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    $html .= '<code>Upload Image in (400*400) Dimensions</code>';
                                $html .= '</div>';

                                // Cropper Image Section
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<div class="row">';
                                        $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                            $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                            $html .= '<div class="mt-3">';
                                                $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'edit_item_form\')">Save</a>';
                                                $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                                $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'edit_item_form\')">Cancel</a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                            $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';

                                // Special Icons
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="ingredients">'.__('Indicative Icons').'</label>';
                                    $html .= '<select name="ingredients[]" id="ingredients" class="form-select" multiple>';
                                        if(count($ingredients) > 0)
                                        {
                                            foreach($ingredients as $ing)
                                            {
                                                $parent_id = (isset($ing->parent_id)) ? $ing->parent_id : NULL;
                                                if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_id != NULL)
                                                {
                                                    $html .= '<option value="'.$ing["id"].'"';
                                                        if(in_array($ing["id"],$item_ingredients))
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .='>'.$ing["name"].'</option>';
                                                }
                                            }
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Tags
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="tags">'.__('Tags').'</label>';
                                    $html .= '<select name="tags[]" id="tags" class="form-select" multiple>';
                                    if(count($tags) > 0)
                                    {
                                        foreach($tags as $tag)
                                        {
                                            $html .= '<option value="'.$tag[$primary_lang_code."_name"].'"';
                                            if(in_array($tag[$primary_lang_code."_name"],$primary_tag_data))
                                            {
                                                $html .= 'selected';
                                            }
                                            $html .='>'.$tag[$primary_lang_code."_name"].'</option>';
                                        }
                                    }
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Calories
                                $html .= '<div class="col-md-12 mb-3 calories_div">';
                                    $html .= '<label class="form-label" for="calories">'.__('Calories').'</label>';
                                    $html .= '<input type="text" name="calories" id="calories" class="form-control" value="'.$calories.'">';
                                $html .= '</div>';

                                // Order Attributes
                                if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                                {
                                    $html .= '<div class="col-md-12 mb-3">';
                                        $html .= '<label class="form-label" for="options">'.__('Attributes').'</label>';
                                        $html .= '<select name="options[]" id="options" class="form-select" multiple>';
                                            if(count($options) > 0)
                                            {
                                                foreach($options as $opt)
                                                {
                                                    $html .= '<option value="'.$opt["id"].'"';
                                                        if(in_array($opt["id"],$item_options))
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .='>'.$opt[$option_title_key].'</option>';
                                                }
                                            }
                                        $html .= '</select>';
                                    $html .= '</div>';
                                }

                                // Toggle Buttons
                                $html .= '<div class="col-md-12 mb-3 mt-1">';
                                    $html .= '<div class="row">';

                                        $html .= '<div class="col-md-6 mark_new mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="mark_new" name="is_new" value="1" '.$item_is_new.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="mark_new" class="form-label">'.__('New').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 mark_sign mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="mark_sign" name="is_sign" value="1" '.$item_as_sign.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="mark_sign" class="form-label">'.__('Recommended').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 day_special mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="day_special" name="day_special" value="1" '.$item_day_special.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="day_special" class="form-label">'.__('Day Special').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="publish" name="published" value="1" '.$item_published.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="publish" class="form-label">'.__('Published').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 review_rating mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="review_rating" name="review_rating" value="1" '.$review_rating.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="review_rating" class="form-label">'.__('Review & Rating').'</label>';
                                        $html .= '</div>';

                                    $html .= '</div>';
                                $html .= '</div>';

                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';

            }
            else
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateItemByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="edit_item_form" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';

                            // Item Type
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="type">'.__('Type').'</label>';
                                    $html .= '<select name="type" id="type" class="form-select" onchange="togglePrice('.$modalName.')">';
                                        $html .= '<option value="1"';
                                            if($item_type == 1)
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .='>Product</option>';
                                        $html .= '<option value="2"';
                                            if($item_type == 2)
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>Divider</option>';
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Category
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="category">'. __('Category').'</label>';
                                    $html .= '<select name="category" id="category" class="form-select">';
                                            $html .= '<option value="">Choose Category</option>';
                                            if(count($categories) > 0)
                                            {
                                                foreach ($categories as $cat)
                                                {
                                                    $html .= '<option value="'.$cat['id'].'"';
                                                        if($category_id == $cat['id'])
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .= '>'.$cat[$primary_lang_code."_name"].'</option>';
                                                }
                                            }
                                    $html .= '</select>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Item Name
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="item_name">'.__('Name').'</label>';
                                    $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$item_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Price
                            $html .= '<div class="row price_div priceDiv">';
                                $html .= '<div class="col-md-12" id="priceDiv">';
                                    $html .= '<label class="form-label">'.__('Price').'</label>';
                                    if(isset($price_array) && count($price_array) > 0)
                                    {
                                        foreach($price_array as $key => $price_arr)
                                        {
                                            $price_label = isset($price_arr[$item_price_label_key]) ? $price_arr[$item_price_label_key] : '';
                                            $price_count = $key + 1;

                                            $html .= '<div class="row mb-3 align-items-center price price_'.$price_count.'">';
                                                $html .= '<div class="col-md-5 mb-1">';
                                                    $html .= '<input type="text" name="price[price][]" class="form-control" placeholder="Enter Price" value="'.$price_arr['price'].'">';
                                                    $html .= '<input type="hidden" name="price[priceID][]" value="'.$price_arr['id'].'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-6 mb-1">';
                                                    $html .= '<input type="text" name="price[label][]" class="form-control" placeholder="Enter Price Label" value="'.$price_label.'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-1 mb-1">';
                                                    $html .= '<a onclick="deleteItemPrice('.$price_arr['id'].','.$price_count.')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';

                            // Price Increment Button
                            $html .= '<div class="row mb-3 price_div priceDiv">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<a onclick="addPrice(\'editItemModal\')" class="btn addPriceBtn btn-info text-white">'.__('Add Price').'</a>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button for Show & Hide More Details
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 text-center">';
                                    $html .= '<a class="btn btn-sm btn-primary" style="cursor: pointer" onclick="toggleMoreDetails(\'editItemModal\')" id="more_dt_btn">More Details.. <i class="bi bi-eye-slash"></i></a>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row" id="more_details" style="display: none;">';

                                // Discount Type
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Discount Type').'</label>';
                                    $html .= '<select name="discount_type" id="discount_type" class="form-control">';
                                        $html .= '<option value="percentage" ';
                                            if($item['discount_type'] == 'percentage')
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>'.__('Percentage %').'</option>';
                                        $html .= '<option value="fixed" ';
                                            if($item['discount_type'] == 'fixed')
                                            {
                                                $html .= 'selected';
                                            }
                                        $html .= '>'.__('Fixed Amount').'</option>';
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Discount
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Discount').'</label>';
                                    $html .= '<input type="number" name="discount" id="discount" class="form-control" value="'.$discount.'">';
                                $html .= '</div>';

                                // Description
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="item_description">'.__('Desription').'</label>';
                                    $html .= '<textarea name="item_description" id="item_description" class="form-control item_description" rows="3">'.$item_desc.'</textarea>';
                                $html .= '</div>';

                                // Image Section
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label">'.__('Image').'</label>';
                                    $html .= '<input type="file" name="item_image" id="item_image" class="form-control item_image" onchange="imageCropper(\'edit_item_form\',this)" style="display:none">';
                                    $html .= '<input type="hidden" name="og_image" id="og_image" class="og_image">';

                                    if(!empty($item_image))
                                    {
                                        $html .= '<div class="row" id="edit-img">';
                                            $html .= '<div class="col-md-3">';
                                                $html .= '<div class="position-relative" id="itemImage">';
                                                    $html .= '<label style="cursor:pointer" for="item_image"><img src="'.$item_image.'" class="w-100" style="border-radius:10px;"></label>';
                                                    $html .= '<a href="'.$delete_item_image_url.'" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: -45px;"><i class="bi bi-trash"></i></a>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        $html .= '</div>';

                                        $html .= '<div class="row mt-2" id="rep-image" style="display:none;">';
                                            $html .= '<div class="col-md-3" id="img-label">';
                                                $html .= '<label for="item_image" style="cursor: pointer">';
                                                    $html .= '<img src="" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw">';
                                                $html .= '</label>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    else
                                    {
                                        $html .= '<div class="mt-3" id="itemImage">';
                                            $html .= '<div class="col-md-3" id="img-label">';
                                                $html .= '<label style="cursor:pointer;" for="item_image"><img src="'.$default_image.'" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw"></label>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                    $html .= '<code>Upload Image in (400*400) Dimensions</code>';
                                $html .= '</div>';

                                // Cropper Image Section
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<div class="row">';
                                        $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                            $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                            $html .= '<div class="mt-3">';
                                                $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'edit_item_form\')">Save</a>';
                                                $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                                $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'edit_item_form\')">Cancel</a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                            $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';

                                // Special Icons
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="ingredients">'.__('Indicative Icons').'</label>';
                                    $html .= '<select name="ingredients[]" id="ingredients" class="form-select" multiple>';
                                        if(count($ingredients) > 0)
                                        {
                                            foreach($ingredients as $ing)
                                            {
                                                $parent_id = (isset($ing->parent_id)) ? $ing->parent_id : NULL;
                                                if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_id != NULL)
                                                {
                                                    $html .= '<option value="'.$ing["id"].'"';
                                                        if(in_array($ing["id"],$item_ingredients))
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .='>'.$ing["name"].'</option>';
                                                }
                                            }
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Tags
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="tags">'.__('Tags').'</label>';
                                    $html .= '<select name="tags[]" id="tags" class="form-select" multiple>';
                                    if(count($tags) > 0)
                                    {
                                        foreach($tags as $tag)
                                        {
                                            $html .= '<option value="'.$tag[$primary_lang_code."_name"].'"';
                                            if(in_array($tag[$primary_lang_code."_name"],$primary_tag_data))
                                            {
                                                $html .= 'selected';
                                            }
                                            $html .='>'.$tag[$primary_lang_code."_name"].'</option>';
                                        }
                                    }
                                    $html .= '</select>';
                                $html .= '</div>';

                                // Calories
                                $html .= '<div class="col-md-12 mb-3 calories_div">';
                                    $html .= '<label class="form-label" for="calories">'.__('Calories').'</label>';
                                    $html .= '<input type="text" name="calories" id="calories" class="form-control" value="'.$calories.'">';
                                $html .= '</div>';

                                // Order Attributes
                                if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                                {
                                    $html .= '<div class="col-md-12 mb-3">';
                                        $html .= '<label class="form-label" for="options">'.__('Attributes').'</label>';
                                        $html .= '<select name="options[]" id="options" class="form-select" multiple>';
                                            if(count($options) > 0)
                                            {
                                                foreach($options as $opt)
                                                {
                                                    $html .= '<option value="'.$opt["id"].'"';
                                                        if(in_array($opt["id"],$item_options))
                                                        {
                                                            $html .= 'selected';
                                                        }
                                                    $html .='>'.$opt[$option_title_key].'</option>';
                                                }
                                            }
                                        $html .= '</select>';
                                    $html .= '</div>';
                                }

                                // Toggle Buttons
                                $html .= '<div class="col-md-12 mb-3 mt-1">';
                                    $html .= '<div class="row">';

                                        $html .= '<div class="col-md-6 mark_new mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="mark_new" name="is_new" value="1" '.$item_is_new.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="mark_new" class="form-label">'.__('New').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 mark_sign mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="mark_sign" name="is_sign" value="1" '.$item_as_sign.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="mark_sign" class="form-label">'.__('Recommended').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 day_special mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="day_special" name="day_special" value="1" '.$item_day_special.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="day_special" class="form-label">'.__('Day Special').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="publish" name="published" value="1" '.$item_published.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="publish" class="form-label">'.__('Published').'</label>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-6 review_rating mb-3">';
                                            $html .= '<label class="switch me-2">';
                                                $html .= '<input type="checkbox" id="review_rating" name="review_rating" value="1" '.$review_rating.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                            $html .= '<label for="review_rating" class="form-label">'.__('Review & Rating').'</label>';
                                        $html .= '</div>';

                                    $html .= '</div>';
                                $html .= '</div>';

                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            return response()->json([
                'success' => 1,
                'message' => "Item Details has been Retrived Successfully..",
                'data'=> $html,
                'item_type'=> $item_type,
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }
    }



    // Function for Update Existing Item
    public function update(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $request->validate([
            'item_name'   => 'required',
            'category'   => 'required',
        ]);

        $lang_code = $request->lang_code;
        $item_id = $request->item_id;
        $item_type = $request->type;
        $category = $request->category;
        $item_name = $request->item_name;
        $discount_type = $request->discount_type;
        $discount = $request->discount;
        $item_description = $request->item_description;
        $item_calories = $request->calories;
        $is_new = isset($request->is_new) ? $request->is_new : 0;
        $is_sign = isset($request->is_sign) ? $request->is_sign : 0;
        $day_special = isset($request->day_special) ? $request->day_special : 0;
        $published = isset($request->published) ? $request->published : 0;
        $review_rating = isset($request->review_rating) ? $request->review_rating : 0;

        $active_lang_code = $request->active_lang_code;

        $price_array['price'] = isset($request->price['price']) ? array_filter($request->price['price']) : [];
        $price_array['label'] = isset($request->price['label']) ? $request->price['label'] : [];
        $price_array['priceID'] = isset($request->price['priceID']) ? $request->price['priceID'] : [];

        $ingredients = (isset($request->ingredients) && count($request->ingredients) > 0) ? serialize($request->ingredients) : '';
        $options = (isset($request->options) && count($request->options) > 0) ? serialize($request->options) : '';
        $tags = isset($request->tags) ? $request->tags : [];

        if(count($price_array['price']) > 0)
        {
            $item_price = $price_array;
        }
        else
        {
            $item_price = [];
        }


        try
        {
            $name_key = $active_lang_code."_name";
            $description_key = $active_lang_code."_description";
            $price_label_key = $active_lang_code."_label";
            $calories_key = $active_lang_code."_calories";

            $item = Items::find($item_id);

            if($item)
            {
                $item->category_id = $category;
                $item->published = $published;
                $item->is_new = $is_new;
                $item->as_sign = $is_sign;
                $item->day_special = $day_special;
                $item->review = $review_rating;
                $item->ingredients = $ingredients;
                $item->options = $options;
                $item->type = $item_type;
                $item->discount_type = $discount_type;
                $item->discount = $discount;

                $item->name = $item_name;
                $item->description = $item_description;
                $item->calories = $item_calories;

                $item->$name_key = $item_name;
                $item->$description_key = $item_description;
                $item->$calories_key = $item_calories;

                // Insert Item Image if is Exists
                if(isset($request->og_image) && !empty($request->og_image) && $request->hasFile('item_image'))
                {
                    $og_image = $request->og_image;
                    $image_arr = explode(";base64,", $og_image);
                    $image_base64 = base64_decode($image_arr[1]);

                    // Delete old Image
                    $item_image = isset($item->image) ? $item->image : '';
                    if(!empty($item_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image))
                    {
                        unlink('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image);
                    }

                    $imgname = "item_".time().".". $request->file('item_image')->getClientOriginalExtension();
                    $img_path = public_path('client_uploads/shops/'.$shop_slug.'/items/'.$imgname);
                    file_put_contents($img_path,$image_base64);
                    $item->image = $imgname;
                }

                $item->update();

                // Update & Insert New Price
                if(count($item_price) > 0)
                {
                    $price_arr = $item_price['price'];
                    $label_arr = $item_price['label'];
                    $ids_arr = $item_price['priceID'];

                    if(count($price_arr) > 0)
                    {
                        foreach($price_arr as $key => $price_val)
                        {
                            $label_val = isset($label_arr[$key]) ? $label_arr[$key] : '';
                            $price_id = isset($ids_arr[$key]) ? $ids_arr[$key] : '';

                            if(!empty($price_id) || $price_id != '') // Update Price
                            {
                                $upd_price = ItemPrice::find($price_id);
                                $upd_price->price = $price_val;
                                $upd_price->label = $label_val;
                                $upd_price->$price_label_key = $label_val;
                                $upd_price->update();
                            }
                            else // Insert New Price
                            {
                                $new_price = new ItemPrice();
                                $new_price->item_id = $item_id;
                                $new_price->shop_id = $shop_id;
                                $new_price->price = $price_val;
                                $new_price->label = $label_val;
                                $new_price->$price_label_key = $label_val;
                                $new_price->save();
                            }
                        }
                    }

                }

                CategoryProductTags::where('category_id',$item->category_id)->where('item_id',$item->id)->delete();

                // Insert & Update Tags
                if(count($tags) > 0)
                {
                    foreach($tags as $val)
                    {
                        $findTag = Tags::where($name_key,$val)->where('shop_id',$shop_id)->first();
                        $tag_id = (isset($findTag->id) && !empty($findTag->id)) ? $findTag->id : '';

                        if(!empty($tag_id) || $tag_id != '')
                        {
                            $tag = Tags::find($tag_id);
                            $tag->name = $val;
                            $tag->$name_key = $val;
                            $tag->update();
                        }
                        else
                        {
                            $max_order = Tags::max('order');
                            $order = (isset($max_order) && !empty($max_order)) ? ($max_order + 1) : 1;

                            $tag = new Tags();
                            $tag->shop_id = $shop_id;
                            $tag->name = $val;
                            $tag->$name_key = $val;
                            $tag->order = $order;
                            $tag->save();
                        }

                        if($tag->id)
                        {
                            $cat_pro_tag = new CategoryProductTags();
                            $cat_pro_tag->tag_id = $tag->id;
                            $cat_pro_tag->category_id = $item->category_id;
                            $cat_pro_tag->item_id = $item->id;
                            $cat_pro_tag->save();
                        }
                    }
                }

            }

            return response()->json([
                'success' => 1,
                'message' => "Item has been Updated SuccessFully....",
            ]);

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }

    }



    // Function for Update Category By Language Code
    public function updateByLangCode(Request $request)
    {
        // Shop ID & Shop Slug
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        $item_id = $request->item_id;
        $item_type = $request->type;
        $category = $request->category;
        $item_name = $request->item_name;
        $item_description = $request->item_description;
        $item_calories = $request->calories;
        $discount_type = $request->discount_type;
        $discount = $request->discount;
        $is_new = isset($request->is_new) ? $request->is_new : 0;
        $is_sign = isset($request->is_sign) ? $request->is_sign : 0;
        $day_special = isset($request->day_special) ? $request->day_special : 0;
        $published = isset($request->published) ? $request->published : 0;
        $review_rating = isset($request->review_rating) ? $request->review_rating : 0;

        $price_array['price'] = isset($request->price['price']) ? array_filter($request->price['price']) : [];
        $price_array['label'] = isset($request->price['label']) ? $request->price['label'] : [];
        $price_array['priceID'] = isset($request->price['priceID']) ? $request->price['priceID'] : [];

        $ingredients = (isset($request->ingredients) && count($request->ingredients) > 0) ? serialize($request->ingredients) : '';
        $options = (isset($request->options) && count($request->options) > 0) ? serialize($request->options) : '';
        $tags = isset($request->tags) ? $request->tags : [];

        if(count($price_array['price']) > 0)
        {
            $item_price = $price_array;
        }
        else
        {
            $item_price = [];
        }

        $active_lang_code = $request->active_lang_code;
        $next_lang_code = $request->next_lang_code;
        $act_lang_name_key = $active_lang_code."_name";
        $act_lang_description_key = $active_lang_code."_description";
        $act_lang_calories_key = $active_lang_code."_calories";
        $act_lang_price_key = $active_lang_code."_label";

        $request->validate([
            'item_name'   => 'required',
            'category'   => 'required',
        ]);

        try
        {
            // Update Item
            $item = Items::find($item_id);

            if($item)
            {
                $item->category_id = $category;
                $item->published = $published;
                $item->is_new = $is_new;
                $item->as_sign = $is_sign;
                $item->day_special = $day_special;
                $item->review = $review_rating;
                $item->ingredients = $ingredients;
                $item->options = $options;
                $item->type = $item_type;
                $item->discount_type = $discount_type;
                $item->discount = $discount;

                $item->name = $item_name;
                $item->description = $item_description;
                $item->calories = $item_calories;

                $item->$act_lang_name_key = $item_name;
                $item->$act_lang_description_key = $item_description;
                $item->$act_lang_calories_key = $item_calories;

                // Insert Item Image if is Exists
                if(isset($request->og_image) && !empty($request->og_image) && $request->hasFile('item_image'))
                {
                    $og_image = $request->og_image;
                    $image_arr = explode(";base64,", $og_image);
                    $image_base64 = base64_decode($image_arr[1]);

                    // Delete old Image
                    $item_image = isset($item->image) ? $item->image : '';
                    if(!empty($item_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image))
                    {
                        unlink('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image);
                    }

                    $imgname = "item_".time().".". $request->file('item_image')->getClientOriginalExtension();
                    $img_path = public_path('client_uploads/shops/'.$shop_slug.'/items/'.$imgname);
                    file_put_contents($img_path,$image_base64);
                    $item->image = $imgname;
                }

                $item->update();

                // Update & Insert New Price
                if(count($item_price) > 0)
                {
                    $price_arr = $item_price['price'];
                    $label_arr = $item_price['label'];
                    $ids_arr = $item_price['priceID'];

                    if(count($price_arr) > 0)
                    {
                        foreach($price_arr as $key => $price_val)
                        {
                            $label_val = isset($label_arr[$key]) ? $label_arr[$key] : '';
                            $price_id = isset($ids_arr[$key]) ? $ids_arr[$key] : '';

                            if(!empty($price_id) || $price_id != '') // Update Price
                            {
                                $upd_price = ItemPrice::find($price_id);
                                $upd_price->price = $price_val;
                                $upd_price->label = $label_val;
                                $upd_price->$act_lang_price_key = $label_val;
                                $upd_price->update();
                            }
                            else // Insert New Price
                            {
                                $new_price = new ItemPrice();
                                $new_price->item_id = $item_id;
                                $new_price->shop_id = $shop_id;
                                $new_price->price = $price_val;
                                $new_price->label = $label_val;
                                $new_price->$act_lang_price_key = $label_val;
                                $new_price->save();
                            }
                        }
                    }

                }

                CategoryProductTags::where('category_id',$item->category_id)->where('item_id',$item->id)->delete();

                // Insert & Update Tags
                if(count($tags) > 0)
                {
                    foreach($tags as $val)
                    {
                        $findTag = Tags::where($act_lang_name_key,$val)->where('shop_id',$shop_id)->first();
                        $tag_id = (isset($findTag->id) && !empty($findTag->id)) ? $findTag->id : '';

                        if(!empty($tag_id) || $tag_id != '')
                        {
                            $tag = Tags::find($tag_id);
                            $tag->name = $val;
                            $tag->$act_lang_name_key = $val;
                            $tag->update();
                        }
                        else
                        {
                            $max_order = Tags::max('order');
                            $order = (isset($max_order) && !empty($max_order)) ? ($max_order + 1) : 1;

                            $tag = new Tags();
                            $tag->shop_id = $shop_id;
                            $tag->name = $val;
                            $tag->$act_lang_name_key = $val;
                            $tag->order = $order;
                            $tag->save();
                        }

                        if($tag->id)
                        {
                            $cat_pro_tag = new CategoryProductTags();
                            $cat_pro_tag->tag_id = $tag->id;
                            $cat_pro_tag->category_id = $item->category_id;
                            $cat_pro_tag->item_id = $item->id;
                            $cat_pro_tag->save();
                        }
                    }
                }
            }

            // Get HTML Data
            $html_data = $this->getEditItemData($next_lang_code,$item_id);

            return response()->json([
                'success' => 1,
                'message' => "Item Details has been Retrived Successfully..",
                'data' => $html_data,
                'item_type'=> $item_type,
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }

    }



    // Function for Get Item Data
    public function getEditItemData($current_lang_code,$item_id)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
        $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
        $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();
        if(count($additional_languages) > 0)
        {
            $name_key = $current_lang_code."_name";
            $description_key = $current_lang_code."_description";
            $calories_key = $current_lang_code."_calories";
            $price_label_key = $current_lang_code."_label";
            $option_title_key = $current_lang_code."_title";
        }
        else
        {
            $name_key = $primary_lang_code."_name";
            $description_key = $primary_lang_code."_description";
            $calories_key = $primary_lang_code."_calories";
            $price_label_key = $primary_lang_code."_label";
            $option_title_key = $primary_lang_code."_title";
        }

        // Item Details
        $item = Items::where('id',$item_id)->first();

        // Categories
        $categories = Category::where('shop_id',$shop_id)->where('category_type','product_category')->get();

        // Ingredients
        $ingredients = Ingredient::where('shop_id',$shop_id)->get();

        // Order Attributes
        $options = Option::where('shop_id',$shop_id)->get();

        // Tags
        $tags = Tags::where('shop_id',$shop_id)->get();

        // ModalName
        $modalName = "'editItemModal'";

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        // Item Details
        $item_type = (isset($item['type'])) ? $item['type'] : '';
        $category_id = (isset($item['category_id'])) ? $item['category_id'] : '';
        $default_image = asset('public/client_images/not-found/no_image_1.jpg');
        $item_image = (isset($item['image']) && !empty($item['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item['image']) : "";
        $delete_item_image_url = route('items.delete.image',$item_id);
        $item_name = (isset($item[$name_key])) ? $item[$name_key] : '';
        $item_desc = (isset($item[$description_key])) ? $item[$description_key] : '';
        $calories = isset($item[$calories_key]) ? $item[$calories_key] : '';
        $item_ingredients = (isset($item['ingredients']) && !empty($item['ingredients'])) ? unserialize($item['ingredients']) : [];
        $price_array = ItemPrice::where('item_id',$item['id'])->where('shop_id',$shop_id)->get();
        $item_cat_tags = CategoryProductTags::with(['hasOneTag'])->where('item_id',$item['id'])->where('category_id',$item['category_id'])->get();
        $item_options = (isset($item['options']) && !empty($item['options'])) ? unserialize($item['options']) : [];
        $item_published = (isset($item['published']) && $item['published'] == 1) ? 'checked' : '';
        $review_rating = (isset($item['review']) && $item['review'] == 1) ? 'checked' : '';
        $item_is_new = (isset($item['is_new']) && $item['is_new'] == 1) ? 'checked' : '';
        $item_as_sign = (isset($item['as_sign']) && $item['as_sign'] == 1) ? 'checked' : '';
        $item_day_special = (isset($item['day_special']) && $item['day_special'] == 1) ? 'checked' : '';
        $discount = (isset($item['discount']) && !empty($item['discount'])) ? $item['discount'] : 0;

        // Item Category Tags Array
        if(count($item_cat_tags) > 0)
        {
            foreach ($item_cat_tags as $key => $value)
            {
                $lang_tag_data[] = isset($value->hasOneTag[$name_key]) ? $value->hasOneTag[$name_key] : '';
            }
        }
        else
        {
            $lang_tag_data = [];
        }

        // Primary Active Tab
        $primary_active_tab = ($primary_lang_code == $current_lang_code) ? 'active' : '';

        if(count($additional_languages) > 0)
        {
            $html = '';
            $html .= '<div class="lang-tab">';
                // Primary Language
                $html .= '<a class="'.$primary_active_tab.' text-uppercase" onclick="updateItemByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                // Additional Language
                foreach($additional_languages as $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                    // Additional Active Tab
                    $additional_active_tab = ($add_lang_code == $current_lang_code) ? 'active' : '';

                    $html .= '<a class="'.$additional_active_tab.' text-uppercase" onclick="updateItemByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                }
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="edit_item_form" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$current_lang_code.'">';
                        $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';

                        // Item Type
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="type">'.__('Type').'</label>';
                                $html .= '<select name="type" id="type" class="form-select" onchange="togglePrice('.$modalName.')">';
                                    $html .= '<option value="1"';
                                        if($item_type == 1)
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .='>Product</option>';
                                    $html .= '<option value="2"';
                                        if($item_type == 2)
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>Divider</option>';
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Category
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category">'. __('Category').'</label>';
                                $html .= '<select name="category" id="category" class="form-select">';
                                        $html .= '<option value="">Choose Category</option>';
                                        if(count($categories) > 0)
                                        {
                                            foreach ($categories as $cat)
                                            {
                                                $html .= '<option value="'.$cat['id'].'"';
                                                    if($category_id == $cat['id'])
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>'.$cat[$name_key].'</option>';
                                            }
                                        }
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Item Name
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="item_name">'.__('Name').'</label>';
                                $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$item_name.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Price
                        $html .= '<div class="row price_div priceDiv">';
                            $html .= '<div class="col-md-12" id="priceDiv">';
                                $html .= '<label class="form-label">'.__('Price').'</label>';
                                if(isset($price_array) && count($price_array) > 0)
                                {
                                    foreach($price_array as $key => $price_arr)
                                    {
                                        $price_label = isset($price_arr[$price_label_key]) ? $price_arr[$price_label_key] : '';
                                        $price_count = $key + 1;

                                        $html .= '<div class="row mb-3 align-items-center price price_'.$price_count.'">';
                                            $html .= '<div class="col-md-5 mb-1">';
                                                $html .= '<input type="text" name="price[price][]" class="form-control" placeholder="Enter Price" value="'.$price_arr['price'].'">';
                                                $html .= '<input type="hidden" name="price[priceID][]" value="'.$price_arr['id'].'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-6 mb-1">';
                                                $html .= '<input type="text" name="price[label][]" class="form-control" placeholder="Enter Price Label" value="'.$price_label.'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-1 mb-1">';
                                                $html .= '<a onclick="deleteItemPrice('.$price_arr['id'].','.$price_count.')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';

                        // Price Increment Button
                        $html .= '<div class="row mb-3 price_div priceDiv">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<a onclick="addPrice(\'editItemModal\')" class="btn addPriceBtn btn-info text-white">'.__('Add Price').'</a>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button for Show & Hide More Details
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 text-center">';
                                $html .= '<a class="btn btn-sm btn-primary" style="cursor: pointer" onclick="toggleMoreDetails(\'editItemModal\')" id="more_dt_btn">More Details.. <i class="bi bi-eye-slash"></i></a>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row" id="more_details" style="display: none;">';

                            // Discount Type
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Discount Type').'</label>';
                                $html .= '<select name="discount_type" id="discount_type" class="form-control">';
                                    $html .= '<option value="percentage" ';
                                        if($item['discount_type'] == 'percentage')
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>'.__('Percentage %').'</option>';
                                    $html .= '<option value="fixed" ';
                                        if($item['discount_type'] == 'fixed')
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>'.__('Fixed Amount').'</option>';
                                $html .= '</select>';
                            $html .= '</div>';

                            // Discount
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Discount').'</label>';
                                $html .= '<input type="number" name="discount" id="discount" class="form-control" value="'.$discount.'">';
                            $html .= '</div>';

                            // Description
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Desription').'</label>';
                                $html .= '<textarea name="item_description" id="item_description" class="form-control item_description" rows="3">'.$item_desc.'</textarea>';
                            $html .= '</div>';

                            // Image Section
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label">'.__('Image').'</label>';
                                $html .= '<input type="file" name="item_image" id="item_image" class="form-control item_image" onchange="imageCropper(\'edit_item_form\',this)" style="display:none">';
                                $html .= '<input type="hidden" name="og_image" id="og_image" class="og_image">';

                                if(!empty($item_image))
                                {
                                    $html .= '<div class="row" id="edit-img">';
                                        $html .= '<div class="col-md-3">';
                                            $html .= '<div class="position-relative" id="itemImage">';
                                                $html .= '<label style="cursor:pointer" for="item_image"><img src="'.$item_image.'" class="w-100" style="border-radius:10px;"></label>';
                                                $html .= '<a href="'.$delete_item_image_url.'" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: -45px;"><i class="bi bi-trash"></i></a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';

                                    $html .= '<div class="row mt-2" id="rep-image" style="display:none;">';
                                        $html .= '<div class="col-md-3" id="img-label">';
                                            $html .= '<label for="item_image" style="cursor: pointer">';
                                                $html .= '<img src="" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw">';
                                            $html .= '</label>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                                else
                                {
                                    $html .= '<div class="mt-3" id="itemImage">';
                                        $html .= '<div class="col-md-3" id="img-label">';
                                            $html .= '<label style="cursor:pointer;" for="item_image"><img src="'.$default_image.'" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw"></label>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '<code>Upload Image in (400*400) Dimensions</code>';
                            $html .= '</div>';

                            // Cropper Image Section
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<div class="row">';
                                    $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                        $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                        $html .= '<div class="mt-3">';
                                            $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'edit_item_form\')">Save</a>';
                                            $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                            $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'edit_item_form\')">Cancel</a>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                        $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Special Icons
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="ingredients">'.__('Indicative Icons').'</label>';
                                $html .= '<select name="ingredients[]" id="ingredients" class="form-select" multiple>';
                                    if(count($ingredients) > 0)
                                    {
                                        foreach($ingredients as $ing)
                                        {
                                            $parent_id = (isset($ing->parent_id)) ? $ing->parent_id : NULL;
                                            if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_id != NULL)
                                            {
                                                $html .= '<option value="'.$ing["id"].'"';
                                                    if(in_array($ing["id"],$item_ingredients))
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .='>'.$ing["name"].'</option>';
                                            }
                                        }
                                    }
                                $html .= '</select>';
                            $html .= '</div>';

                            // Tags
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="tags">'.__('Tags').'</label>';
                                $html .= '<select name="tags[]" id="tags" class="form-select" multiple>';
                                if(count($tags) > 0)
                                {
                                    foreach($tags as $tag)
                                    {
                                        $html .= '<option value="'.$tag[$name_key].'"';
                                        if(in_array($tag[$name_key],$lang_tag_data))
                                        {
                                            $html .= 'selected';
                                        }
                                        $html .='>'.$tag[$name_key].'</option>';
                                    }
                                }
                                $html .= '</select>';
                            $html .= '</div>';

                            // Calories
                            $html .= '<div class="col-md-12 mb-3 calories_div">';
                                $html .= '<label class="form-label" for="calories">'.__('Calories').'</label>';
                                $html .= '<input type="text" name="calories" id="calories" class="form-control" value="'.$calories.'">';
                            $html .= '</div>';

                            // Order Attributes
                            if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                            {
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="options">'.__('Attributes').'</label>';
                                    $html .= '<select name="options[]" id="options" class="form-select" multiple>';
                                        if(count($options) > 0)
                                        {
                                            foreach($options as $opt)
                                            {
                                                $html .= '<option value="'.$opt["id"].'"';
                                                    if(in_array($opt["id"],$item_options))
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .='>'.$opt[$option_title_key].'</option>';
                                            }
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';
                            }

                            // Toggle Buttons
                            $html .= '<div class="col-md-12 mb-3 mt-1">';
                                $html .= '<div class="row">';

                                    $html .= '<div class="col-md-6 mark_new mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="mark_new" name="is_new" value="1" '.$item_is_new.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="mark_new" class="form-label">'.__('New').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 mark_sign mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="mark_sign" name="is_sign" value="1" '.$item_as_sign.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="mark_sign" class="form-label">'.__('Recommended').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 day_special mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="day_special" name="day_special" value="1" '.$item_day_special.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="day_special" class="form-label">'.__('Day Special').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="publish" name="published" value="1" '.$item_published.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="publish" class="form-label">'.__('Published').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 review_rating mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="review_rating" name="review_rating" value="1" '.$review_rating.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="review_rating" class="form-label">'.__('Review & Rating').'</label>';
                                    $html .= '</div>';

                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';

                    $html .= '</form>';
                $html .= '</div>';
            $html .= '</div>';
        }
        else
        {
            $html = '';
            $html .= '<div class="lang-tab">';
                // Primary Language
                $html .= '<a class="active text-uppercase" onclick="updateItemByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="edit_item_form" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                        $html .= '<input type="hidden" name="item_id" id="item_id" value="'.$item['id'].'">';

                        // Item Type
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="type">'.__('Type').'</label>';
                                $html .= '<select name="type" id="type" class="form-select" onchange="togglePrice('.$modalName.')">';
                                    $html .= '<option value="1"';
                                        if($item_type == 1)
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .='>Product</option>';
                                    $html .= '<option value="2"';
                                        if($item_type == 2)
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>Divider</option>';
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Category
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category">'. __('Category').'</label>';
                                $html .= '<select name="category" id="category" class="form-select">';
                                        $html .= '<option value="">Choose Category</option>';
                                        if(count($categories) > 0)
                                        {
                                            foreach ($categories as $cat)
                                            {
                                                $html .= '<option value="'.$cat['id'].'"';
                                                    if($category_id == $cat['id'])
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>'.$cat[$name_key].'</option>';
                                            }
                                        }
                                $html .= '</select>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Item Name
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="item_name">'.__('Name').'</label>';
                                $html .= '<input type="text" name="item_name" id="item_name" class="form-control" value="'.$item_name.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Price
                        $html .= '<div class="row price_div priceDiv">';
                            $html .= '<div class="col-md-12" id="priceDiv">';
                                $html .= '<label class="form-label">'.__('Price').'</label>';
                                if(isset($price_array) && count($price_array) > 0)
                                {
                                    foreach($price_array as $key => $price_arr)
                                    {
                                        $price_label = isset($price_arr[$price_label_key]) ? $price_arr[$price_label_key] : '';
                                        $price_count = $key + 1;

                                        $html .= '<div class="row mb-3 align-items-center price price_'.$price_count.'">';
                                            $html .= '<div class="col-md-5 mb-1">';
                                                $html .= '<input type="text" name="price[price][]" class="form-control" placeholder="Enter Price" value="'.$price_arr['price'].'">';
                                                $html .= '<input type="hidden" name="price[priceID][]" value="'.$price_arr['id'].'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-6 mb-1">';
                                                $html .= '<input type="text" name="price[label][]" class="form-control" placeholder="Enter Price Label" value="'.$price_label.'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-1 mb-1">';
                                                $html .= '<a onclick="deleteItemPrice('.$price_arr['id'].','.$price_count.')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';

                        // Price Increment Button
                        $html .= '<div class="row mb-3 price_div priceDiv">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<a onclick="addPrice(\'editItemModal\')" class="btn addPriceBtn btn-info text-white">'.__('Add Price').'</a>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button for Show & Hide More Details
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 text-center">';
                                $html .= '<a class="btn btn-sm btn-primary" style="cursor: pointer" onclick="toggleMoreDetails(\'editItemModal\')" id="more_dt_btn">More Details.. <i class="bi bi-eye-slash"></i></a>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row" id="more_details" style="display: none;">';

                            // Discount Type
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Discount Type').'</label>';
                                $html .= '<select name="discount_type" id="discount_type" class="form-control">';
                                    $html .= '<option value="percentage" ';
                                        if($item['discount_type'] == 'percentage')
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>'.__('Percentage %').'</option>';
                                    $html .= '<option value="fixed" ';
                                        if($item['discount_type'] == 'fixed')
                                        {
                                            $html .= 'selected';
                                        }
                                    $html .= '>'.__('Fixed Amount').'</option>';
                                $html .= '</select>';
                            $html .= '</div>';

                            // Discount
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Discount').'</label>';
                                $html .= '<input type="number" name="discount" id="discount" class="form-control" value="'.$discount.'">';
                            $html .= '</div>';

                            // Description
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="item_description">'.__('Desription').'</label>';
                                $html .= '<textarea name="item_description" id="item_description" class="form-control item_description" rows="3">'.$item_desc.'</textarea>';
                            $html .= '</div>';

                            // Image Section
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label">'.__('Image').'</label>';
                                $html .= '<input type="file" name="item_image" id="item_image" class="form-control item_image" onchange="imageCropper(\'edit_item_form\',this)" style="display:none">';
                                $html .= '<input type="hidden" name="og_image" id="og_image" class="og_image">';

                                if(!empty($item_image))
                                {
                                    $html .= '<div class="row" id="edit-img">';
                                        $html .= '<div class="col-md-3">';
                                            $html .= '<div class="position-relative" id="itemImage">';
                                                $html .= '<label style="cursor:pointer" for="item_image"><img src="'.$item_image.'" class="w-100" style="border-radius:10px;"></label>';
                                                $html .= '<a href="'.$delete_item_image_url.'" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: -45px;"><i class="bi bi-trash"></i></a>';
                                            $html .= '</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';

                                    $html .= '<div class="row mt-2" id="rep-image" style="display:none;">';
                                        $html .= '<div class="col-md-3" id="img-label">';
                                            $html .= '<label for="item_image" style="cursor: pointer">';
                                                $html .= '<img src="" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw">';
                                            $html .= '</label>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                                else
                                {
                                    $html .= '<div class="mt-3" id="itemImage">';
                                        $html .= '<div class="col-md-3" id="img-label">';
                                            $html .= '<label style="cursor:pointer;" for="item_image"><img src="'.$default_image.'" class="w-100 h-100" style="border-radius:10px;" id="crp-img-prw"></label>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '<code>Upload Image in (400*400) Dimensions</code>';
                            $html .= '</div>';

                            // Cropper Image Section
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<div class="row">';
                                    $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                        $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                        $html .= '<div class="mt-3">';
                                            $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'edit_item_form\')">Save</a>';
                                            $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                            $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'edit_item_form\')">Cancel</a>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                        $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Special Icons
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="ingredients">'.__('Indicative Icons').'</label>';
                                $html .= '<select name="ingredients[]" id="ingredients" class="form-select" multiple>';
                                    if(count($ingredients) > 0)
                                    {
                                        foreach($ingredients as $ing)
                                        {
                                            $parent_id = (isset($ing->parent_id)) ? $ing->parent_id : NULL;
                                            if((isset($package_permissions['special_icons']) && !empty($package_permissions['special_icons']) && $package_permissions['special_icons'] == 1) || $parent_id != NULL)
                                            {
                                                $html .= '<option value="'.$ing["id"].'"';
                                                    if(in_array($ing["id"],$item_ingredients))
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .='>'.$ing["name"].'</option>';
                                            }
                                        }
                                    }
                                $html .= '</select>';
                            $html .= '</div>';

                            // Tags
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<label class="form-label" for="tags">'.__('Tags').'</label>';
                                $html .= '<select name="tags[]" id="tags" class="form-select" multiple>';
                                if(count($tags) > 0)
                                {
                                    foreach($tags as $tag)
                                    {
                                        $html .= '<option value="'.$tag[$name_key].'"';
                                        if(in_array($tag[$name_key],$lang_tag_data))
                                        {
                                            $html .= 'selected';
                                        }
                                        $html .='>'.$tag[$name_key].'</option>';
                                    }
                                }
                                $html .= '</select>';
                            $html .= '</div>';

                            // Calories
                            $html .= '<div class="col-md-12 mb-3 calories_div">';
                                $html .= '<label class="form-label" for="calories">'.__('Calories').'</label>';
                                $html .= '<input type="text" name="calories" id="calories" class="form-control" value="'.$calories.'">';
                            $html .= '</div>';

                            // Order Attributes
                            if((isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1))
                            {
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<label class="form-label" for="options">'.__('Attributes').'</label>';
                                    $html .= '<select name="options[]" id="options" class="form-select" multiple>';
                                        if(count($options) > 0)
                                        {
                                            foreach($options as $opt)
                                            {
                                                $html .= '<option value="'.$opt["id"].'"';
                                                    if(in_array($opt["id"],$item_options))
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .='>'.$opt[$option_title_key].'</option>';
                                            }
                                        }
                                    $html .= '</select>';
                                $html .= '</div>';
                            }

                            // Toggle Buttons
                            $html .= '<div class="col-md-12 mb-3 mt-1">';
                                $html .= '<div class="row">';

                                    $html .= '<div class="col-md-6 mark_new mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="mark_new" name="is_new" value="1" '.$item_is_new.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="mark_new" class="form-label">'.__('New').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 mark_sign mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="mark_sign" name="is_sign" value="1" '.$item_as_sign.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="mark_sign" class="form-label">'.__('Recommended').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 day_special mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="day_special" name="day_special" value="1" '.$item_day_special.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="day_special" class="form-label">'.__('Day Special').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="publish" name="published" value="1" '.$item_published.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="publish" class="form-label">'.__('Published').'</label>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-6 review_rating mb-3">';
                                        $html .= '<label class="switch me-2">';
                                            $html .= '<input type="checkbox" id="review_rating" name="review_rating" value="1" '.$review_rating.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                        $html .= '<label for="review_rating" class="form-label">'.__('Review & Rating').'</label>';
                                    $html .= '</div>';

                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';

                    $html .= '</form>';
                $html .= '</div>';
            $html .= '</div>';

        }

        return $html;

    }



    // Function Delete Item Image
    public function deleteItemImage($id)
    {
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
        $item = Items::find($id);

        if($item)
        {
            $item_image = isset($item['image']) ? $item['image'] : '';

            if(!empty($item_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_image);
            }

            $item->image = "";
            $item->update();
        }

        return redirect()->route('items')->with('success',"Item Image has been Removed SuccessFully...");

    }



    // Function for Sorting Items.
    public function sorting(Request $request)
    {
        $sort_array = $request->sortArr;

        foreach ($sort_array as $key => $value)
        {
    		$key = $key+1;
    		Items::where('id',$value)->update(['order_key'=>$key]);
    	}

        return response()->json([
            'success' => 1,
            'message' => "Item has been Sorted SuccessFully....",
        ]);

    }


    // Functon for Delete Item Price
    public function deleteItemPrice(Request $request)
    {
        $price_id = $request->price_id;

        ItemPrice::where('id',$price_id)->delete();

        return response()->json([
            'success' => 1,
            'message' => 'Item Price has been Removed..',
        ]);
    }

}
