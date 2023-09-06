<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\Category;
use App\Models\CategoryImages;
use App\Models\CategoryVisit;
use App\Models\Items;
use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{

    // Get all Categories
    public function index($uri = NULL)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $categories = Category::query();

        $cat_type_arr = ['page','link','gallery','check_in','pdf_page'];

        if(!empty($uri) && is_numeric($uri))
        {
            $cat = Category::with(['categoryImages'])->where('id',$uri)->first();
            $category_type = (isset($cat->category_type)) ? $cat->category_type : '';
            if(empty($category_type) || $category_type != 'parent_category')
            {
                return redirect()->route('categories')->with('error','This Action is Unauthorized!');
            }
            $categories = $categories->where('parent_id',$uri);
        }
        elseif(!empty($uri) && !is_numeric($uri))
        {
            if(in_array($uri,$cat_type_arr))
            {
                $cat = '';
                $categories = $categories->where('category_type',$uri);
            }
            else
            {
                if($uri == 'product_category')
                {
                    return redirect()->route('categories');
                }
                return redirect()->route('categories')->with('error','This Action is Unauthorized!');
            }
        }
        else
        {
            $cat = '';
            $categories = $categories->where('parent_id',$uri)->whereIn('category_type',['product_category','parent_category']);
        }

        $categories = $categories->where('shop_id',$shop_id)->orderBy('order_key')->get();
        $data['categories'] = $categories;
        $data['parent_cat_id'] = $uri;
        $data['cat_details'] = $cat;
        $data['parent_categories'] = Category::where('shop_id',$shop_id)->where('parent_category',1)->get();

        if($uri == 'page' || $uri == 'link' || $uri == 'pdf_page' || $uri == 'check_in')
        {
            return view('client.categories.categories_list',$data);
        }
        else
        {
            return view('client.categories.categories',$data);
        }
    }



    // Function for Store New Category
    public function store(Request $request)
    {
        $rules = [
            'name'   => 'required',
        ];

        $category_type = $request->category_type;
        $schedule_type = $request->schedule_type;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if($category_type == 'link')
        {
            $rules += [
                'url' => 'required',
            ];
        }

        if($schedule_type == 'date')
        {
            $rules += [
                'start_date' => 'required',
                'end_date' => 'required',
            ];
        }

        $request->validate($rules);

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $category_name_key = $lang_code."_name";
        $category_description_key = $lang_code."_description";

        $name = $request->name;
        $description = $request->description;
        $published = isset($request->published) ? $request->published : 0;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $max_category_order_key = Category::max('order_key');
        $category_order = (isset($max_category_order_key) && !empty($max_category_order_key)) ? ($max_category_order_key + 1) : 1;

        $schedule_arr = $request->schedule_array;
        $schedule = isset($request->schedule) ? $request->schedule : 0;

        try
        {
            $category = new Category();
            $category->name = $name;
            $category->category_type = $category_type;
            $category->$category_name_key = $name;
            $category->schedule_type = $schedule_type;
            $category->schedule = $schedule;

            if($schedule_type == 'time')
            {
                $category->schedule_value = $schedule_arr;
            }
            else
            {
                $category->sch_start_date = $start_date;
                $category->sch_end_date = $end_date;
            }


            // Description
            if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'check_in')
            {
                $category->description = $description;
                $category->$category_description_key = $description;
            }

            // Cover
            if($category_type == 'page' || $category_type == 'link' || $category_type == 'gallery' || $category_type == 'check_in' || $category_type == 'parent_category' || $category_type == 'pdf_page')
            {
                if($request->hasFile('cover'))
                {
                    $cover_name = "cover_".time().".". $request->file('cover')->getClientOriginalExtension();
                    $request->file('cover')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $cover_name);
                    $category->cover = $cover_name;
                }
            }

            // URL
            if($category_type == 'link')
            {
                $category->link_url = isset($request->url) ? $request->url : '';
            }

            // Bg Color
            if($category_type == 'check_in')
            {
                $category->styles = isset($request->checkin_styles) ? serialize($request->checkin_styles) : '';
            }

            // Parent Category
            if($category_type == 'parent_category')
            {
                if(isset($request->parent_cat) && !empty($request->parent_cat) && $request->parent_cat == 0)
                {
                    $category->parent_category = 1;
                }
                elseif(empty($request->parent_cat) || !isset($request->parent_cat))
                {
                    $category->parent_category = 1;
                }
                else
                {
                    $category->parent_id = $request->parent_cat;
                    $category->category_type = 'product_category';
                }
            }

            // Pdf File
            if($category_type == 'pdf_page')
            {
                if($request->hasFile('pdf'))
                {
                    $file_name = "pdf_".time().".". $request->file('pdf')->getClientOriginalExtension();
                    $request->file('pdf')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $file_name);
                    $category->file = $file_name;
                }
            }

            if(isset($request->parent_cat_id) && !empty($request->parent_cat_id))
            {
                $category->parent_id = $request->parent_cat_id;
            }

            $category->published = $published;
            $category->shop_id = $shop_id;
            $category->order_key = $category_order;
            $category->save();

            // Multiple Images
            if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'gallery' || $category_type == 'parent_category')
            {
                // Insert Category Image if is Exists
                $all_images = (isset($request->og_image)) ? $request->og_image : [];
                if(count($all_images) > 0)
                {
                    foreach($all_images as $image)
                    {
                        $image_token = genratetoken(10);
                        $og_image = $image;
                        $image_arr = explode(";base64,", $og_image);
                        $image_type_ext = explode("image/", $image_arr[0]);
                        $image_base64 = base64_decode($image_arr[1]);

                        $imgname = "category_".$image_token.".".$image_type_ext[1];
                        $img_path = public_path('client_uploads/shops/'.$shop_slug.'/categories/'.$imgname);
                        file_put_contents($img_path,$image_base64);
                        // $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories'), $imgname);

                        // Insert Image
                        $new_img = new CategoryImages();
                        $new_img->category_id = $category->id;
                        $new_img->image = $imgname;
                        $new_img->save();

                    }
                }
            }


            return response()->json([
                'success' => 1,
                'message' => "Category has been Inserted SuccessFully....",
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



    // Function for Delete Category
    public function destroy(Request $request)
    {
        try
        {
            $id = $request->id;
            $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

            // Category Items Count
            $items_count = Items::where('category_id',$id)->count();

            // Category Details
            $category_details = Category::where('id',$id)->first();

            if($items_count > 0)
            {
                return response()->json([
                    'success' => 0,
                    'message' => "You cannot delete a category as long as there are any items in the category!",
                ]);
            }
            else
            {
                $category_images = CategoryImages::where('category_id',$id)->get();

                if(count($category_images) > 0)
                {
                    foreach($category_images as $cat_image)
                    {
                        // Delete Category Image
                        if(!empty($cat_image->image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image->image))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image->image);
                        }
                    }
                }


                // PDF file Delete
                $pdf_file = isset($category_details['file']) ? $category_details['file'] : '';
                if(!empty($pdf_file) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$pdf_file))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$pdf_file);
                }


                // Cover Image Delete
                $cover_image = isset($category_details['cover']) ? $category_details['cover'] : '';
                if(!empty($cover_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cover_image))
                {
                    unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cover_image);
                }


                // Delete Category
                Category::where('id',$id)->delete();

                // Delete Category Visists
                CategoryVisit::where('category_id',$id)->delete();

                // Delete Category Images from DB
                CategoryImages::where('category_id',$id)->delete();

                return response()->json([
                    'success' => 1,
                    'message' => "Category has been Deleted SuccessFully....",
                ]);
            }

        }
        catch (\Throwable $th)
        {
            return response()->json([
                'success' => 0,
                'message' => "Internal Server Error!",
            ]);
        }
    }



    // Function for Edit Category
    public function edit(Request $request)
    {
        $category_id = $request->id;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        try
        {
            // Category Details
            $category = Category::where('id',$category_id)->first();

            // Get all Parent Categories
            $parent_categories = Category::where('shop_id',$shop_id)->where('id','!=',$category_id)->where('parent_category',1)->get();

            // Category Types
            $category_types = [
                'product_category' => 'Category',
                'page' => 'Page',
                'link' => 'Link',
                'gallery' => 'Image Gallery',
                'check_in' => 'Check-In Page',
                'pdf_page' => 'PDF Category',
            ];

            if($category->parent_id == null)
            {
                $category_types['parent_category'] = 'Child Category';
            }

            // Categories Images
            $category_images = CategoryImages::where('category_id',$category_id)->get();

            // Get Language Settings
            $language_settings = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

            // Primary Language Details
            $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
            $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
            $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';
            $category_name_key = $primary_lang_code."_name";
            $category_desc_key = $primary_lang_code."_description";
            $primary_input_lang_code = "'$primary_lang_code'";

            // Category Details
            $category_status = (isset($category['published']) && $category['published'] == 1) ? 'checked' : '';
            $schedule = isset($category->schedule) ? $category->schedule : 0;
            $schedule_active_text = ($schedule == 1) ? 'Scheduling Active' : 'Scheduling Not Active';
            $schedule_active = ($schedule == 1) ? 'checked' : '';
            $schedule_arr = isset($category->schedule_value) ? json_decode($category->schedule_value,true) : [];
            $category_name = (isset($category[$category_name_key])) ? $category[$category_name_key] : '';
            $category_desc = (isset($category[$category_desc_key])) ? $category[$category_desc_key] : '';
            $root_parent_cat_checked = ($category['parent_category'] == 1) ? 'checked' : '';

            // Additional Languages
            $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

            // Check In Page Styles
            $check_page_style = (isset($category['styles']) && !empty($category['styles'])) ? unserialize($category['styles']) : '';
            $bg_color = isset($check_page_style['background_color']) ? $check_page_style['background_color'] : '';
            $font_color = isset($check_page_style['font_color']) ? $check_page_style['font_color'] : '';
            $btn_color = isset($check_page_style['button_color']) ? $check_page_style['button_color'] : '';
            $btn_text_color = isset($check_page_style['button_text_color']) ? $check_page_style['button_text_color'] : '';

            if(count($additional_languages) > 0)
            {
                $html = '';
                $html .= '<div class="lang-tab">';
                    // Primary Language
                    $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                    // Additional Language
                    foreach($additional_languages as $value)
                    {
                        // Additional Language Details
                        $add_lang_detail = Languages::where('id',$value->language_id)->first();
                        $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                        $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                        $html .= '<a class="text-uppercase" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                    }
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="edit_category_form" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="category_id" id="category_id" value="'.$category['id'].'">';
                            $html .= '<input type="hidden" name="category_type" id="category_type" value="'.$category->category_type.'">';

                            // Category Type
                            // $html .= '<div class="row mb-3">';
                            //     $html .= '<div class="col-md-12">';
                            //         $html .= '<label class="form-label" for="category_type">'.__('Type').'</label>';
                            //         $html .= '<select onchange="changeElements(\'editCategoryModal\')" name="category_type" id="category_type" class="form-select category_type">';
                            //             foreach($category_types as $cat_type_key => $cat_type)
                            //             {
                            //                 $html .= '<option value="'.$cat_type_key.'"';

                            //                     if($cat_type_key == $category->category_type)
                            //                     {
                            //                         $html .= 'selected';
                            //                     }

                            //                 $html.='>'.$cat_type.'</option>';
                            //             }
                            //         $html .= '</select>';
                            //     $html .= '</div>';
                            //     $html .= '</div>';
                            // $html .= '</div>';

                            // Category
                            $html .= '<div class="row mb-3 cat_div">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'.__('Category').'</label>';
                                    $html .= '<div id="categories_div">';
                                        $html .= '<input type="radio" name="parent_cat" id="root" value="0" '.$root_parent_cat_checked.'> <label for="root">Root</label> <br>';
                                        if(count($parent_categories) > 0)
                                        {
                                            foreach ($parent_categories as $key => $pcategory)
                                            {
                                                $parent_cat_check = ($pcategory->parent_id == $pcategory->id) ? 'checked' : '';
                                                $html .= '<input type="radio" name="parent_cat" id="pcat_'.$key.'" value="'.$pcategory->id.'" '.$parent_cat_check.'> <label for="pcat_'.$key.'">'.$pcategory->name.'</label><br>';
                                            }
                                        }
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Name
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category_name">'.__('Name').'</label>';
                                $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$category_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Sort Order
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="sort_order">'.__('Sort Order').'</label>';
                                $html .= '<input type="text" name="sort_order" id="sort_order" class="form-control" value="'.$category['order_key'].'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Url
                            $url_active = ($category->category_type == 'link') ? 'block' : 'none';
                            $html .= '<div class="row mb-3 url" style="display: '.$url_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="url">'.__('URL').'</label>';
                                    $html .= '<input type="text" name="url" id="url" class="form-control" value="'.$category->link_url.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $check_page_style_active = ($category->category_type == 'check_in') ? 'block' : 'none';

                            // Background Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="background_color">'.__('Background Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[background_color]" id="background_color" class="form-control" value="'.$bg_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Font Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="font_color">'.__('Font Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[font_color]" id="font_color" class="form-control" value="'.$font_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="button_color">'.__('Button Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[button_color]" id="button_color" class="form-control" value="'.$btn_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button Text Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="button_text_color">'.__('Button Text Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[button_text_color]" id="button_text_color" class="form-control" value="'.$btn_text_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Description
                            $html .= '<div class="row mb-3 description">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="category_description">'.__('Desription').'</label>';
                                    $html .= '<textarea name="category_description" id="category_description" class="form-control category_description" rows="3">'.$category_desc.'</textarea>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Images
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 d-flex flex-wrap" id="edit_images_div">';
                                    if($category->category_type == 'product_category' || $category->category_type == 'page' || $category->category_type == 'gallery' || $category->category_type == 'parent_category')
                                    {
                                        if(count($category_images) > 0)
                                        {
                                            foreach($category_images as $key => $cat_image)
                                            {
                                                $no = $key + 1;

                                                if(!empty($cat_image['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']))
                                                {
                                                    $html .= '<div class="inner-img edit_img_'.$no.'">';
                                                        $html .= '<img src="'.asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']).'" class="w-100 h-100">';
                                                        $html .= '<a class="btn btn-sm btn-danger del-pre-btn" onclick="deleteCategoryImage('.$no.','.$cat_image->id.')"><i class="fa fa-trash"></i></a>';
                                                    $html .= '</div>';
                                                }
                                            }
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 mb-2 d-flex flex-wrap" id="images_div"></div>';
                                $html .= '<div class="col-md-12 mul-image" id="img-val"></div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3 mul-image">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('Image').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<div id="img-label">';
                                        $html .= '<label for="category_image">Upload Images</label>';
                                        $html .= '<input type="file" name="category_image" id="category_image" class="form-control category_image" onchange="imageCropper(\'editCategoryModal\',this)" style="display:none">';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<code class="img-upload-label">Upload Image in (400*400) Dimensions</code>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                    $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                    $html .= '<div class="mt-3">';
                                        $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'editCategoryModal\')">Save</a>';
                                        $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                        $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'editCategoryModal\')">Cancel</a>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                    $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Cover Image
                            $cover_active = ($category->category_type == 'page' || $category->category_type == 'link' || $category->category_type == 'gallery' || $category->category_type == 'check_in' || $category->category_type == 'parent_category' || $category->category_type == 'pdf_page') ? '' : 'none';
                            if(!empty($category->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover))
                            {
                                $cover_image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover);
                            }
                            else
                            {
                                $cover_image = asset('public/client_images/not-found/no_image_1.jpg');
                            }
                            $html .= '<div class="row mb-3 cover" style="display: '.$cover_active.'" id="cover_label">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('Thumbnail').'</label>';
                                    $html .= '<input type="file" onchange="CoverPreview(\'editCategoryModal\',this)" id="cover" name="cover" style="display: none">';
                                    $html .= '<div class="page-cover">';
                                        $html .= '<label for="cover" id="upload-page-cover-image">';
                                            $html .= '<img src="'.$cover_image.'" class="w-100 h-100">';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // PDF File
                            $pdf_active = ($category->category_type == 'pdf_page') ? '' : 'none';
                            $html .= '<div class="row mb-3 pdf" style="display: '.$pdf_active.'" id="pdf_label">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('PDF File').'</label>';
                                    $html .= '<input type="file" onchange="PdfPreview(\'editCategoryModal\',this)" id="pdf" name="pdf" style="display: none">';
                                    $html .= '<div class="pdf-file">';
                                        $html .= '<label for="pdf" id="upload-pdf-file">';
                                            $html .= '<img src="'.asset('public/client_images/not-found/no_image_1.jpg').'" class="w-100 h-100">';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<h4 class="mt-2" id="pdf-name">'.$category->file.'</h4>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Status
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label me-3" for="published">'.__('Published').'</label>';
                                    $html .= '<label class="switch">';
                                        $html .= '<input type="checkbox" id="published" name="published" value="1" '.$category_status.'>';
                                        $html .= '<span class="slider round">';
                                            $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                            $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                        $html .= '</span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Schedule
                            $schedule_type = (isset($category->schedule_type) && !empty($category->schedule_type)) ? $category->schedule_type : 'time';
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<div class="input-label text-primary schedule-toggle">';
                                        $html .= '<i class="fa fa-clock" onclick="$(\'#editCategoryModal #schedule-main-div\').toggle()"></i> <span>'.$schedule_active_text.'</span>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12 mb-3" id="schedule-main-div" style="display: ';
                                $html .= ($schedule == 1) ? '' : 'none';
                                $html .= ';">';
                                    $html .= '<div class="row">';
                                        $html .= '<div class="col-md-6 mb-2">';
                                            $html .= '<select name="schedule_type" id="schedule_type" onchange="changeScheduleType(\'editCategoryModal\')" class="form-select">';
                                                $html .= '<option value="time"';
                                                    if($schedule_type == 'time')
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>Time</option>';
                                                $html .= '<option value="date"';
                                                    if($schedule_type == 'date')
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>Date</option>';
                                            $html .= '</select>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-6 text-end">';
                                            $html .= '<label class="switch">';
                                                $html .= '<input type="checkbox" id="schedule" name="schedule" value="1" onchange="changeScheduleLabel(\'editCategoryModal\')" '.$schedule_active.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-12 sc_inner sc_time">';
                                            $html .= '<div class="sc_array_section" id="sc_array_section">';
                                                if(count($schedule_arr) > 0)
                                                {
                                                    foreach($schedule_arr as $key => $sched)
                                                    {
                                                        $schd_name = isset($sched['name']) ? $sched['name'] : '';
                                                        $active_day = ($sched['enabled'] == 1) ? 'checked' : '';
                                                        $time_arr = $sched['timesSchedules'];

                                                        $html .= '<div class="p-2" id="'.$key.'_sec">';
                                                            $html .= '<div class="text-center">';
                                                                $html .= '<input type="checkbox" class="me-2" name="" id="'.$key.'" '.$active_day.'> <label for="'.$key.'">'.$schd_name.'</label>';
                                                            $html .= '</div>';

                                                            $html .= '<div class="sch-sec">';
                                                                if(count($time_arr) > 0)
                                                                {
                                                                    foreach($time_arr as $tkey => $sc_time)
                                                                    {
                                                                        $time_key = $tkey + 1;
                                                                        $sc_start_time = isset($sc_time['startTime']) ? $sc_time['startTime'] : '';
                                                                        $sc_end_time = isset($sc_time['endTime']) ? $sc_time['endTime'] : '';

                                                                        $html .= '<div class="sch_'.$time_key.'">';
                                                                            if($time_key > 1)
                                                                            {
                                                                                $html .= '<div class="sch-minus"><i class="bi bi-dash-circle" onclick="$(\'#editCategoryModal #'.$key.'_sec .sch_'.$time_key.'\').remove()"></i></div>';
                                                                            }
                                                                            $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime" value="'.$sc_start_time.'">';
                                                                            $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime" value="'.$sc_end_time.'">';
                                                                        $html .= '</div>';

                                                                    }
                                                                }
                                                            $html .= '</div>';

                                                            $html .= '<div class="sch-plus">';
                                                                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\''.$key.'_sec\',\'editCategoryModal\')"></i>';
                                                            $html .= '</div>';

                                                        $html .= '</div>';
                                                    }
                                                }
                                                else
                                                {
                                                    $html .= $this->getTimeScheduleArray();
                                                }
                                            $html .= '</div>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-12 sc_date" style="display: none;">';
                                            $html .= '<div class="row">';
                                                $html .= '<div class="col-md-6">';
                                                    $html .= '<label for="start_date" class="form-label">Start Date</label>';
                                                    $html .= '<input type="date" name="start_date" id="start_date" class="form-control" value="'.$category->sch_start_date.'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-6">';
                                                    $html .= '<label for="end_date" class="form-label">End Date</label>';
                                                    $html .= '<input type="date" name="end_date" id="end_date" class="form-control" value="'.$category->sch_end_date.'">';
                                                $html .= '</div>';
                                            $html .= '</div>';
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
                    $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
                $html .= '</div>';

                $html .= '<hr>';

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-12">';
                        $html .= '<form id="edit_category_form" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="category_id" id="category_id" value="'.$category['id'].'">';
                            $html .= '<input type="hidden" name="category_type" id="category_type" value="'.$category->category_type.'">';

                            // Category Type
                            // $html .= '<div class="row mb-3">';
                            //     $html .= '<div class="col-md-12">';
                            //         $html .= '<label class="form-label" for="category_type">'.__('Type').'</label>';
                            //         $html .= '<select onchange="changeElements(\'editCategoryModal\')" name="category_type" id="category_type" class="form-select category_type">';
                            //             foreach($category_types as $cat_type_key => $cat_type)
                            //             {
                            //                 $html .= '<option value="'.$cat_type_key.'"';

                            //                     if($cat_type_key == $category->category_type)
                            //                     {
                            //                         $html .= 'selected';
                            //                     }

                            //                 $html.='>'.$cat_type.'</option>';
                            //             }
                            //         $html .= '</select>';
                            //     $html .= '</div>';
                            //     $html .= '</div>';
                            // $html .= '</div>';

                            // Category
                            $html .= '<div class="row mb-3 cat_div">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'.__('Category').'</label>';
                                    $html .= '<div id="categories_div">';
                                        $html .= '<input type="radio" name="parent_cat" id="root" value="0" '.$root_parent_cat_checked.'> <label for="root">Root</label> <br>';
                                        if(count($parent_categories) > 0)
                                        {
                                            foreach ($parent_categories as $key => $pcategory)
                                            {
                                                $parent_cat_check = ($pcategory->parent_id == $pcategory->id) ? 'checked' : '';
                                                $html .= '<input type="radio" name="parent_cat" id="pcat_'.$key.'" value="'.$pcategory->id.'" '.$parent_cat_check.'> <label for="pcat_'.$key.'">'.$pcategory->name.'</label><br>';
                                            }
                                        }
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Name
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category_name">'.__('Name').'</label>';
                                $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$category_name.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Sort Order
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="sort_order">'.__('Sort Order').'</label>';
                                $html .= '<input type="text" name="sort_order" id="sort_order" class="form-control" value="'.$category['order_key'].'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Url
                            $url_active = ($category->category_type == 'link') ? 'block' : 'none';
                            $html .= '<div class="row mb-3 url" style="display: '.$url_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="url">'.__('URL').'</label>';
                                    $html .= '<input type="text" name="url" id="url" class="form-control" value="'.$category->link_url.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $check_page_style_active = ($category->category_type == 'check_in') ? 'block' : 'none';

                            // Background Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="background_color">'.__('Background Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[background_color]" id="background_color" class="form-control" value="'.$bg_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Font Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="font_color">'.__('Font Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[font_color]" id="font_color" class="form-control" value="'.$font_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="button_color">'.__('Button Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[button_color]" id="button_color" class="form-control" value="'.$btn_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Button Text Color
                            $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="button_text_color">'.__('Button Text Color').'</label>';
                                    $html .= '<input type="color" name="checkin_styles[button_text_color]" id="button_text_color" class="form-control" value="'.$btn_text_color.'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Description
                            $html .= '<div class="row mb-3 description">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label" for="category_description">'.__('Desription').'</label>';
                                    $html .= '<textarea name="category_description" id="category_description" class="form-control category_description" rows="3">'.$category_desc.'</textarea>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Images
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 d-flex flex-wrap" id="edit_images_div">';
                                    if($category->category_type == 'product_category' || $category->category_type == 'page' || $category->category_type == 'gallery' || $category->category_type == 'parent_category')
                                    {
                                        if(count($category_images) > 0)
                                        {
                                            foreach($category_images as $key => $cat_image)
                                            {
                                                $no = $key + 1;

                                                if(!empty($cat_image['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']))
                                                {
                                                    $html .= '<div class="inner-img edit_img_'.$no.'">';
                                                        $html .= '<img src="'.asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']).'" class="w-100 h-100">';
                                                        $html .= '<a class="btn btn-sm btn-danger del-pre-btn" onclick="deleteCategoryImage('.$no.','.$cat_image->id.')"><i class="fa fa-trash"></i></a>';
                                                    $html .= '</div>';
                                                }
                                            }
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 mb-2 d-flex flex-wrap" id="images_div"></div>';
                                $html .= '<div class="col-md-12 mul-image" id="img-val"></div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3 mul-image">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('Image').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<div id="img-label">';
                                        $html .= '<label for="category_image">Upload Images</label>';
                                        $html .= '<input type="file" name="category_image" id="category_image" class="form-control category_image" onchange="imageCropper(\'editCategoryModal\',this)" style="display:none">';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<code class="img-upload-label">Upload Image in (400*400) Dimensions</code>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                    $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                    $html .= '<div class="mt-3">';
                                        $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'editCategoryModal\')">Save</a>';
                                        $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                        $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'editCategoryModal\')">Cancel</a>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                    $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Cover Image
                            $cover_active = ($category->category_type == 'page' || $category->category_type == 'link' || $category->category_type == 'gallery' || $category->category_type == 'check_in' || $category->category_type == 'parent_category' || $category->category_type == 'pdf_page') ? '' : 'none';
                            if(!empty($category->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover))
                            {
                                $cover_image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover);
                            }
                            else
                            {
                                $cover_image = asset('public/client_images/not-found/no_image_1.jpg');
                            }
                            $html .= '<div class="row mb-3 cover" style="display: '.$cover_active.'" id="cover_label">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('Thumbnail').'</label>';
                                    $html .= '<input type="file" onchange="CoverPreview(\'editCategoryModal\',this)" id="cover" name="cover" style="display: none">';
                                    $html .= '<div class="page-cover">';
                                        $html .= '<label for="cover" id="upload-page-cover-image">';
                                            $html .= '<img src="'.$cover_image.'" class="w-100 h-100">';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // PDF File
                            $pdf_active = ($category->category_type == 'pdf_page') ? '' : 'none';
                            $html .= '<div class="row mb-3 pdf" style="display: '.$pdf_active.'" id="pdf_label">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label">'. __('PDF File').'</label>';
                                    $html .= '<input type="file" onchange="PdfPreview(\'editCategoryModal\',this)" id="pdf" name="pdf" style="display: none">';
                                    $html .= '<div class="pdf-file">';
                                        $html .= '<label for="pdf" id="upload-pdf-file">';
                                            $html .= '<img src="'.asset('public/client_images/not-found/no_image_1.jpg').'" class="w-100 h-100">';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<h4 class="mt-2" id="pdf-name">'.$category->file.'</h4>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Status
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12">';
                                    $html .= '<label class="form-label me-3" for="published">'.__('Published').'</label>';
                                    $html .= '<label class="switch">';
                                        $html .= '<input type="checkbox" id="published" name="published" value="1" '.$category_status.'>';
                                        $html .= '<span class="slider round">';
                                            $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                            $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                        $html .= '</span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            // Schedule
                            $schedule_type = (isset($category->schedule_type) && !empty($category->schedule_type)) ? $category->schedule_type : 'time';
                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-12 mb-3">';
                                    $html .= '<div class="input-label text-primary schedule-toggle">';
                                        $html .= '<i class="fa fa-clock" onclick="$(\'#editCategoryModal #schedule-main-div\').toggle()"></i> <span>'.$schedule_active_text.'</span>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-12 mb-3" id="schedule-main-div" style="display: ';
                                $html .= ($schedule == 1) ? '' : 'none';
                                $html .= ';">';
                                    $html .= '<div class="row">';
                                        $html .= '<div class="col-md-6 mb-2">';
                                            $html .= '<select name="schedule_type" id="schedule_type" onchange="changeScheduleType(\'editCategoryModal\')" class="form-select">';
                                                $html .= '<option value="time"';
                                                    if($schedule_type == 'time')
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>Time</option>';
                                                $html .= '<option value="date"';
                                                    if($schedule_type == 'date')
                                                    {
                                                        $html .= 'selected';
                                                    }
                                                $html .= '>Date</option>';
                                            $html .= '</select>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-6 text-end">';
                                            $html .= '<label class="switch">';
                                                $html .= '<input type="checkbox" id="schedule" name="schedule" value="1" onchange="changeScheduleLabel(\'editCategoryModal\')" '.$schedule_active.'>';
                                                $html .= '<span class="slider round">';
                                                    $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                    $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                                $html .= '</span>';
                                            $html .= '</label>';
                                        $html .= '</div>';
                                        $html .= '<div class="col-md-12 sc_inner sc_time">';
                                            $html .= '<div class="sc_array_section" id="sc_array_section">';
                                                if(count($schedule_arr) > 0)
                                                {
                                                    foreach($schedule_arr as $key => $sched)
                                                    {
                                                        $schd_name = isset($sched['name']) ? $sched['name'] : '';
                                                        $active_day = ($sched['enabled'] == 1) ? 'checked' : '';
                                                        $time_arr = $sched['timesSchedules'];

                                                        $html .= '<div class="p-2" id="'.$key.'_sec">';
                                                            $html .= '<div class="text-center">';
                                                                $html .= '<input type="checkbox" class="me-2" name="" id="'.$key.'" '.$active_day.'> <label for="'.$key.'">'.$schd_name.'</label>';
                                                            $html .= '</div>';

                                                            $html .= '<div class="sch-sec">';
                                                                if(count($time_arr) > 0)
                                                                {
                                                                    foreach($time_arr as $tkey => $sc_time)
                                                                    {
                                                                        $time_key = $tkey + 1;
                                                                        $sc_start_time = isset($sc_time['startTime']) ? $sc_time['startTime'] : '';
                                                                        $sc_end_time = isset($sc_time['endTime']) ? $sc_time['endTime'] : '';

                                                                        $html .= '<div class="sch_'.$time_key.'">';
                                                                            if($time_key > 1)
                                                                            {
                                                                                $html .= '<div class="sch-minus"><i class="bi bi-dash-circle" onclick="$(\'#editCategoryModal #'.$key.'_sec .sch_'.$time_key.'\').remove()"></i></div>';
                                                                            }
                                                                            $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime" value="'.$sc_start_time.'">';
                                                                            $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime" value="'.$sc_end_time.'">';
                                                                        $html .= '</div>';

                                                                    }
                                                                }
                                                            $html .= '</div>';

                                                            $html .= '<div class="sch-plus">';
                                                                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\''.$key.'_sec\',\'editCategoryModal\')"></i>';
                                                            $html .= '</div>';

                                                        $html .= '</div>';
                                                    }
                                                }
                                                else
                                                {
                                                    $html .= $this->getTimeScheduleArray();
                                                }
                                            $html .= '</div>';
                                        $html .= '</div>';

                                        $html .= '<div class="col-md-12 sc_date" style="display: none;">';
                                            $html .= '<div class="row">';
                                                $html .= '<div class="col-md-6">';
                                                    $html .= '<label for="start_date" class="form-label">Start Date</label>';
                                                    $html .= '<input type="date" name="start_date" id="start_date" class="form-control" value="'.$category->sch_start_date.'">';
                                                $html .= '</div>';
                                                $html .= '<div class="col-md-6">';
                                                    $html .= '<label for="end_date" class="form-label">End Date</label>';
                                                    $html .= '<input type="date" name="end_date" id="end_date" class="form-control" value="'.$category->sch_end_date.'">';
                                                $html .= '</div>';
                                            $html .= '</div>';
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
                'message' => "Category Details has been Retrived Successfully..",
                'data'=> $html,
                'primary_code' => $primary_lang_code,
                'category_type' => $category->category_type,
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


    // Function for Time Schedule Array
    public function getTimeScheduleArray()
    {
        $html = '';

        $html .= '<div class="p-2" id="sunday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="sunday"> <label for="sunday">Sun</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'sunday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="monday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="monday"> <label for="monday">Mon</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'monday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="tuesday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="tuesday"> <label for="tuesday">Tue</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'tuesday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="wednesday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="wednesday"> <label for="wednesday">Wed</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'wednesday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="thursday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="thursday"> <label for="thursday">Thu</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'thursday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="friday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="friday"> <label for="friday">Fri</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'friday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="p-2" id="saturday_sec">';
            $html .= '<div class="text-center">';
                $html .= '<input type="checkbox" class="me-2" name="" id="saturday"> <label for="saturday">Sat</label>';
            $html .= '</div>';
            $html .= '<div class="sch-sec">';
                $html .= '<div class="sch_1">';
                    $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime">';
                    $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime">';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="sch-plus">';
                $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\'saturday_sec\',\'editCategoryForm\')"></i>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }


    // Function for Update Category
    public function update(Request $request)
    {
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $category_type = $request->category_type;
        $category_id = $request->category_id;
        $category_name = $request->category_name;
        $category_description = $request->category_description;
        $published = isset($request->published) ? $request->published : 0;
        $schedule_arr = $request->schedule_array;
        $schedule = isset($request->schedule) ? $request->schedule : 0;
        $schedule_type = $request->schedule_type;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $active_lang_code = $request->active_lang_code;

        $name_key = $active_lang_code."_name";
        $description_key = $active_lang_code."_description";

        $rules = [
            'category_name' => 'required',
        ];

        if($category_type == 'link')
        {
            $rules += [
                'url' => 'required',
            ];
        }

        if($schedule_type == 'date')
        {
            $rules += [
                'start_date' => 'required',
                'end_date' => 'required',
            ];
        }

        $request->validate($rules);

        try
        {
            $category = Category::find($category_id);

            if($category)
            {
                $category->name = $category_name;
                $category->$name_key = $category_name;
                $category->category_type = $category_type;
                $category->order_key = $request->sort_order;

                // Description
                if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'check_in')
                {
                    $category->description = $category_description;
                    $category->$description_key = $category_description;
                }

                // Cover
                if($category_type == 'page' || $category_type == 'link' || $category_type == 'gallery' || $category_type == 'check_in' || $category_type == 'parent_category' || $category_type == 'pdf_page')
                {
                    if($request->hasFile('cover'))
                    {
                        // Delete Old Cover
                        $old_cover = isset($category->cover) ? $category->cover : '';
                        if(!empty($old_cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_cover))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_cover);
                        }

                        $cover_name = "cover_".time().".". $request->file('cover')->getClientOriginalExtension();
                        $request->file('cover')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $cover_name);
                        $category->cover = $cover_name;
                    }
                }

                // URL
                if($category_type == 'link')
                {
                    $category->link_url = isset($request->url) ? $request->url : '';
                }

                // Bg Color
                if($category_type == 'check_in')
                {
                    $category->styles = isset($request->checkin_styles) ? serialize($request->checkin_styles) : '';
                }

                // Parent Category
                if($category_type == 'parent_category')
                {
                    if(isset($request->parent_cat) && !empty($request->parent_cat) && $request->parent_cat == 0)
                    {
                        $category->parent_category = 1;
                    }
                    elseif(empty($request->parent_cat) || !isset($request->parent_cat))
                    {
                        $category->parent_category = 1;
                    }
                    else
                    {
                        $category->parent_id = $request->parent_cat;
                        $category->category_type = 'product_category';
                    }
                }

                // Pdf File
                if($category_type == 'pdf_page')
                {
                    if($request->hasFile('pdf'))
                    {
                        // Delete Old PDF
                        $old_pdf = isset($category->file) ? $category->file : '';
                        if(!empty($old_pdf) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_pdf))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_pdf);
                        }

                        $file_name = "pdf_".time().".". $request->file('pdf')->getClientOriginalExtension();
                        $request->file('pdf')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $file_name);
                        $category->file = $file_name;
                    }
                }

                if(isset($request->parent_cat_id) && !empty($request->parent_cat_id))
                {
                    $category->parent_id = $request->parent_cat_id;
                }

                $category->published = $published;
                $category->schedule = $schedule;
                $category->schedule_type = $schedule_type;

                if($schedule_type == 'time')
                {
                    $category->schedule_value = $schedule_arr;
                }
                else
                {
                    $category->sch_start_date = $start_date;
                    $category->sch_end_date = $end_date;
                }

                // Multiple Images
                if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'gallery' || $category_type == 'parent_category')
                {
                    // Insert Category Image if is Exists
                    $all_images = (isset($request->og_image)) ? $request->og_image : [];
                    if(count($all_images) > 0)
                    {
                        // Delete Old Images
                        if($category_type != 'gallery')
                        {
                            CategoryImages::where('category_id',$category_id)->delete();
                        }

                        foreach($all_images as $image)
                        {
                            $image_token = genratetoken(10);
                            $og_image = $image;
                            $image_arr = explode(";base64,", $og_image);
                            $image_type_ext = explode("image/", $image_arr[0]);
                            $image_base64 = base64_decode($image_arr[1]);

                            $imgname = "category_".$image_token.".".$image_type_ext[1];
                            $img_path = public_path('client_uploads/shops/'.$shop_slug.'/categories/'.$imgname);
                            file_put_contents($img_path,$image_base64);
                            // $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories'), $imgname);

                            // Insert Image
                            $new_img = new CategoryImages();
                            $new_img->category_id = $category_id;
                            $new_img->image = $imgname;
                            $new_img->save();
                        }
                    }
                }

                $category->update();
            }

            return response()->json([
                'success' => 1,
                'message' => "Category has been Updated SuccessFully....",
                'category_id' => $category_id,
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

        $category_id = $request->category_id;
        $sort_order = $request->sort_order;
        $published = isset($request->published) ? $request->published : 0;
        $schedule_arr = $request->schedule_array;
        $schedule = isset($request->schedule) ? $request->schedule : 0;
        $url = isset($request->url) ? $request->url : '';;
        $category_type = $request->category_type;
        $category_name = $request->category_name;
        $category_description = $request->category_description;
        $active_lang_code = $request->active_lang_code;
        $next_lang_code = $request->next_lang_code;
        $act_lang_name_key = $active_lang_code."_name";
        $act_lang_description_key = $active_lang_code."_description";
        $schedule_type = $request->schedule_type;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $rules = [
            'category_name' => 'required',
        ];

        if($category_type == 'link')
        {
            $rules += [
                'url' => 'required',
            ];
        }

        if($schedule_type == 'date')
        {
            $rules += [
                'start_date' => 'required',
                'end_date' => 'required',
            ];
        }

        $request->validate($rules);

        try
        {
            // Update Category
            $category = Category::find($category_id);

            if($category)
            {
                $category->name = $category_name;
                $category->$act_lang_name_key = $category_name;
                $category->category_type = $category_type;
                $category->order_key = $sort_order;

                // Description
                if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'check_in')
                {
                    $category->description = $category_description;
                    $category->$act_lang_description_key = $category_description;
                }

                // Cover
                if($category_type == 'page' || $category_type == 'link' || $category_type == 'gallery' || $category_type == 'check_in' || $category_type == 'parent_category' || $category_type == 'pdf_page')
                {
                    if($request->hasFile('cover'))
                    {
                        // Delete Old Cover
                        $old_cover = isset($category->cover) ? $category->cover : '';
                        if(!empty($old_cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_cover))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_cover);
                        }

                        $cover_name = "cover_".time().".". $request->file('cover')->getClientOriginalExtension();
                        $request->file('cover')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $cover_name);
                        $category->cover = $cover_name;
                    }
                }

                // URL
                if($category_type == 'link')
                {
                    $category->link_url = $url;
                }

                // Bg Color
                if($category_type == 'check_in')
                {
                    $category->styles = isset($request->checkin_styles) ? serialize($request->checkin_styles) : '';
                }

                // Parent Category
                if($category_type == 'parent_category')
                {
                    if(isset($request->parent_cat) && !empty($request->parent_cat) && $request->parent_cat == 0)
                    {
                        $category->parent_category = 1;
                    }
                    elseif(empty($request->parent_cat) || !isset($request->parent_cat))
                    {
                        $category->parent_category = 1;
                    }
                    else
                    {
                        $category->parent_id = $request->parent_cat;
                        $category->category_type = 'product_category';
                    }
                }

                // Pdf File
                if($category_type == 'pdf_page')
                {
                    if($request->hasFile('pdf'))
                    {
                        // Delete Old PDF
                        $old_pdf = isset($category->file) ? $category->file : '';
                        if(!empty($old_pdf) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_pdf))
                        {
                            unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$old_pdf);
                        }

                        $file_name = "pdf_".time().".". $request->file('pdf')->getClientOriginalExtension();
                        $request->file('pdf')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories/'), $file_name);
                        $category->file = $file_name;
                    }
                }

                if(isset($request->parent_cat_id) && !empty($request->parent_cat_id))
                {
                    $category->parent_id = $request->parent_cat_id;
                }

                $category->published = $published;
                $category->schedule = $schedule;
                $category->schedule_type = $schedule_type;

                if($schedule_type == 'time')
                {
                    $category->schedule_value = $schedule_arr;
                }
                else
                {
                    $category->sch_start_date = $start_date;
                    $category->sch_end_date = $end_date;
                }

                // Multiple Images
                if($category_type == 'product_category' || $category_type == 'page' || $category_type == 'gallery' || $category_type == 'parent_category')
                {
                    // Insert Category Image if is Exists
                    $all_images = (isset($request->og_image)) ? $request->og_image : [];
                    if(count($all_images) > 0)
                    {
                        // Delete Old Images
                        if($category_type != 'gallery')
                        {
                            CategoryImages::where('category_id',$category_id)->delete();
                        }

                        foreach($all_images as $image)
                        {
                            $image_token = genratetoken(10);
                            $og_image = $image;
                            $image_arr = explode(";base64,", $og_image);
                            $image_type_ext = explode("image/", $image_arr[0]);
                            $image_base64 = base64_decode($image_arr[1]);

                            $imgname = "category_".$image_token.".".$image_type_ext[1];
                            $img_path = public_path('client_uploads/shops/'.$shop_slug.'/categories/'.$imgname);
                            file_put_contents($img_path,$image_base64);
                            // $request->file('image')->move(public_path('client_uploads/shops/'.$shop_slug.'/categories'), $imgname);

                            // Insert Image
                            $new_img = new CategoryImages();
                            $new_img->category_id = $category_id;
                            $new_img->image = $imgname;
                            $new_img->save();
                        }
                    }
                }

                $category->update();
            }

            // Get HTML Data
            $html_data = $this->getEditCategoryData($next_lang_code,$category_id);

            return response()->json([
                'success' => 1,
                'message' => "Category has been Updated SuccessFully....",
                'data' => $html_data,
                'category_type' => $category->category_type,
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


    // Function for Get Category Data
    public function getEditCategoryData($current_lang_code,$category_id)
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
            $category_name_key = $current_lang_code."_name";
            $category_description_key = $current_lang_code."_description";
        }
        else
        {
            $category_name_key = $primary_lang_code."_name";
            $category_description_key = $primary_lang_code."_description";
        }

        // Category Details
        $category = Category::where('id',$category_id)->first();
        $category_name = isset($category[$category_name_key]) ? $category[$category_name_key] : '';
        $category_description = isset($category[$category_description_key]) ? $category[$category_description_key] : '';
        $root_parent_cat_checked = ($category['parent_category'] == 1) ? 'checked' : '';
        $category_status = (isset($category['published']) && $category['published'] == 1) ? 'checked' : '';
        $schedule = isset($category->schedule) ? $category->schedule : 0;
        $schedule_active_text = ($schedule == 1) ? 'Scheduling Active' : 'Scheduling Not Active';
        $schedule_active = ($schedule == 1) ? 'checked' : '';
        $schedule_arr = isset($category->schedule_value) ? json_decode($category->schedule_value,true) : [];

        // Check In Page Styles
        $check_page_style = (isset($category['styles']) && !empty($category['styles'])) ? unserialize($category['styles']) : '';
        $bg_color = isset($check_page_style['background_color']) ? $check_page_style['background_color'] : '';
        $font_color = isset($check_page_style['font_color']) ? $check_page_style['font_color'] : '';
        $btn_color = isset($check_page_style['button_color']) ? $check_page_style['button_color'] : '';
        $btn_text_color = isset($check_page_style['button_text_color']) ? $check_page_style['button_text_color'] : '';

        // Get all Parent Categories
        $parent_categories = Category::where('shop_id',$shop_id)->where('id','!=',$category_id)->where('parent_category',1)->get();

        // Categories Images
        $category_images = CategoryImages::where('category_id',$category_id)->get();

        // Category Types
        $category_types = [
            'product_category' => 'Category',
            'page' => 'Page',
            'link' => 'Link',
            'gallery' => 'Image Gallery',
            'check_in' => 'Check-In Page',
            'pdf_page' => 'PDF Category',
        ];

        if($category->parent_id == null)
        {
            $category_types['parent_category'] = 'Child Category';
        }

        // Primary Active Tab
        $primary_active_tab = ($primary_lang_code == $current_lang_code) ? 'active' : '';

        if(count($additional_languages) > 0)
        {
            $html = '';
            $html .= '<div class="lang-tab">';
                // Primary Language
                $html .= '<a class="'.$primary_active_tab.' text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';

                // Additional Language
                foreach($additional_languages as $value)
                {
                    // Additional Language Details
                    $add_lang_detail = Languages::where('id',$value->language_id)->first();
                    $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                    $add_lang_name = isset($add_lang_detail->name) ? $add_lang_detail->name : '';

                    // Additional Active Tab
                    $additional_active_tab = ($add_lang_code == $current_lang_code) ? 'active' : '';

                    $html .= '<a class="'.$additional_active_tab.' text-uppercase" onclick="updateByCode(\''.$add_lang_code.'\')">'.$add_lang_code.'</a>';
                }
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="edit_category_form" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$current_lang_code.'">';
                        $html .= '<input type="hidden" name="category_id" id="category_id" value="'.$category['id'].'">';
                        $html .= '<input type="hidden" name="category_type" id="category_type" value="'.$category->category_type.'">';

                        // Category Type
                        // $html .= '<div class="row mb-3">';
                        //     $html .= '<div class="col-md-12">';
                        //         $html .= '<label class="form-label" for="category_type">'.__('Type').'</label>';
                        //         $html .= '<select onchange="changeElements(\'editCategoryModal\')" name="category_type" id="category_type" class="form-select category_type">';
                        //             foreach($category_types as $cat_type_key => $cat_type)
                        //             {
                        //                 $html .= '<option value="'.$cat_type_key.'"';

                        //                     if($cat_type_key == $category->category_type)
                        //                     {
                        //                         $html .= 'selected';
                        //                     }

                        //                 $html.='>'.$cat_type.'</option>';
                        //             }
                        //         $html .= '</select>';
                        //     $html .= '</div>';
                        //     $html .= '</div>';
                        // $html .= '</div>';

                        // Category
                        $html .= '<div class="row mb-3 cat_div">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'.__('Category').'</label>';
                                $html .= '<div id="categories_div">';
                                    $html .= '<input type="radio" name="parent_cat" id="root" value="0" '.$root_parent_cat_checked.'> <label for="root">Root</label> <br>';
                                    if(count($parent_categories) > 0)
                                    {
                                        foreach ($parent_categories as $key => $pcategory)
                                        {
                                            $parent_cat_check = ($pcategory->parent_id == $pcategory->id) ? 'checked' : '';
                                            $html .= '<input type="radio" name="parent_cat" id="pcat_'.$key.'" value="'.$pcategory->id.'" '.$parent_cat_check.'> <label for="pcat_'.$key.'">'.$pcategory->name.'</label><br>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Name
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                            $html .= '<label class="form-label" for="category_name">'.__('Name').'</label>';
                            $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$category_name.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Sort Order
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                            $html .= '<label class="form-label" for="sort_order">'.__('Sort Order').'</label>';
                            $html .= '<input type="text" name="sort_order" id="sort_order" class="form-control" value="'.$category['order_key'].'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Url
                        $url_active = ($category->category_type == 'link') ? 'block' : 'none';
                        $html .= '<div class="row mb-3 url" style="display: '.$url_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="url">'.__('URL').'</label>';
                                $html .= '<input type="text" name="url" id="url" class="form-control" value="'.$category->link_url.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $check_page_style_active = ($category->category_type == 'check_in') ? 'block' : 'none';

                        // Background Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="background_color">'.__('Background Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[background_color]" id="background_color" class="form-control" value="'.$bg_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Font Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="font_color">'.__('Font Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[font_color]" id="font_color" class="form-control" value="'.$font_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="button_color">'.__('Button Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[button_color]" id="button_color" class="form-control" value="'.$btn_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button Text Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="button_text_color">'.__('Button Text Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[button_text_color]" id="button_text_color" class="form-control" value="'.$btn_text_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Description
                        $html .= '<div class="row mb-3 description">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category_description">'.__('Desription').'</label>';
                                $html .= '<textarea name="category_description" id="category_description" class="form-control category_description" rows="3">'.$category_description.'</textarea>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Images
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 d-flex flex-wrap" id="edit_images_div">';
                                if($category->category_type == 'product_category' || $category->category_type == 'page' || $category->category_type == 'gallery' || $category->category_type == 'parent_category')
                                {
                                    if(count($category_images) > 0)
                                    {
                                        foreach($category_images as $key => $cat_image)
                                        {
                                            $no = $key + 1;

                                            if(!empty($cat_image['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']))
                                            {
                                                $html .= '<div class="inner-img edit_img_'.$no.'">';
                                                    $html .= '<img src="'.asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']).'" class="w-100 h-100">';
                                                    $html .= '<a class="btn btn-sm btn-danger del-pre-btn" onclick="deleteCategoryImage('.$no.','.$cat_image->id.')"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                            }
                                        }
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 mb-2 d-flex flex-wrap" id="images_div"></div>';
                            $html .= '<div class="col-md-12 mul-image" id="img-val"></div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3 mul-image">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('Image').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12">';
                                $html .= '<div id="img-label">';
                                    $html .= '<label for="category_image">Upload Images</label>';
                                    $html .= '<input type="file" name="category_image" id="category_image" class="form-control category_image" onchange="imageCropper(\'editCategoryModal\',this)" style="display:none">';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12">';
                                $html .= '<code class="img-upload-label">Upload Image in (400*400) Dimensions</code>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                $html .= '<div class="mt-3">';
                                    $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'editCategoryModal\')">Save</a>';
                                    $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                    $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'editCategoryModal\')">Cancel</a>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Cover Image
                        $cover_active = ($category->category_type == 'page' || $category->category_type == 'link' || $category->category_type == 'gallery' || $category->category_type == 'check_in' || $category->category_type == 'parent_category' || $category->category_type == 'pdf_page') ? '' : 'none';
                        if(!empty($category->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover))
                        {
                            $cover_image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover);
                        }
                        else
                        {
                            $cover_image = asset('public/client_images/not-found/no_image_1.jpg');
                        }
                        $html .= '<div class="row mb-3 cover" style="display: '.$cover_active.'" id="cover_label">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('Thumbnail').'</label>';
                                $html .= '<input type="file" onchange="CoverPreview(\'editCategoryModal\',this)" id="cover" name="cover" style="display: none">';
                                $html .= '<div class="page-cover">';
                                    $html .= '<label for="cover" id="upload-page-cover-image">';
                                        $html .= '<img src="'.$cover_image.'" class="w-100 h-100">';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // PDF File
                        $pdf_active = ($category->category_type == 'pdf_page') ? '' : 'none';
                        $html .= '<div class="row mb-3 pdf" style="display: '.$pdf_active.'" id="pdf_label">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('PDF File').'</label>';
                                $html .= '<input type="file" onchange="PdfPreview(\'editCategoryModal\',this)" id="pdf" name="pdf" style="display: none">';
                                $html .= '<div class="pdf-file">';
                                    $html .= '<label for="pdf" id="upload-pdf-file">';
                                        $html .= '<img src="'.asset('public/client_images/not-found/no_image_1.jpg').'" class="w-100 h-100">';
                                    $html .= '</label>';
                                $html .= '</div>';
                                $html .= '<h4 class="mt-2" id="pdf-name">'.$category->file.'</h4>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Status
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label me-3" for="published">'.__('Published').'</label>';
                                $html .= '<label class="switch">';
                                    $html .= '<input type="checkbox" id="published" name="published" value="1" '.$category_status.'>';
                                    $html .= '<span class="slider round">';
                                        $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                        $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                    $html .= '</span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Schedule
                        $schedule_type = (isset($category->schedule_type) && !empty($category->schedule_type)) ? $category->schedule_type : 'time';
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<div class="input-label text-primary schedule-toggle">';
                                    $html .= '<i class="fa fa-clock" onclick="$(\'#editCategoryModal #schedule-main-div\').toggle()"></i> <span>'.$schedule_active_text.'</span>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12 mb-3" id="schedule-main-div" style="display: ';
                            $html .= ($schedule == 1) ? '' : 'none';
                            $html .= ';">';
                                $html .= '<div class="row">';
                                    $html .= '<div class="col-md-6 mb-2">';
                                        $html .= '<select name="schedule_type" id="schedule_type" onchange="changeScheduleType(\'editCategoryModal\')" class="form-select">';
                                            $html .= '<option value="time"';
                                                if($schedule_type == 'time')
                                                {
                                                    $html .= 'selected';
                                                }
                                            $html .= '>Time</option>';
                                            $html .= '<option value="date"';
                                                if($schedule_type == 'date')
                                                {
                                                    $html .= 'selected';
                                                }
                                            $html .= '>Date</option>';
                                        $html .= '</select>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-6 text-end">';
                                        $html .= '<label class="switch">';
                                            $html .= '<input type="checkbox" id="schedule" name="schedule" value="1" onchange="changeScheduleLabel(\'editCategoryModal\')" '.$schedule_active.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-12 sc_inner sc_time">';
                                        $html .= '<div class="sc_array_section" id="sc_array_section">';
                                            if(count($schedule_arr) > 0)
                                            {
                                                foreach($schedule_arr as $key => $sched)
                                                {
                                                    $schd_name = isset($sched['name']) ? $sched['name'] : '';
                                                    $active_day = ($sched['enabled'] == 1) ? 'checked' : '';
                                                    $time_arr = $sched['timesSchedules'];

                                                    $html .= '<div class="p-2" id="'.$key.'_sec">';
                                                        $html .= '<div class="text-center">';
                                                            $html .= '<input type="checkbox" class="me-2" name="" id="'.$key.'" '.$active_day.'> <label for="'.$key.'">'.$schd_name.'</label>';
                                                        $html .= '</div>';

                                                        $html .= '<div class="sch-sec">';
                                                            if(count($time_arr) > 0)
                                                            {
                                                                foreach($time_arr as $tkey => $sc_time)
                                                                {
                                                                    $time_key = $tkey + 1;
                                                                    $sc_start_time = isset($sc_time['startTime']) ? $sc_time['startTime'] : '';
                                                                    $sc_end_time = isset($sc_time['endTime']) ? $sc_time['endTime'] : '';

                                                                    $html .= '<div class="sch_'.$time_key.'">';
                                                                        if($time_key > 1)
                                                                        {
                                                                            $html .= '<div class="sch-minus"><i class="bi bi-dash-circle" onclick="$(\'#editCategoryModal #'.$key.'_sec .sch_'.$time_key.'\').remove()"></i></div>';
                                                                        }
                                                                        $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime" value="'.$sc_start_time.'">';
                                                                        $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime" value="'.$sc_end_time.'">';
                                                                    $html .= '</div>';

                                                                }
                                                            }
                                                        $html .= '</div>';

                                                        $html .= '<div class="sch-plus">';
                                                            $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\''.$key.'_sec\',\'editCategoryModal\')"></i>';
                                                        $html .= '</div>';

                                                    $html .= '</div>';
                                                }
                                            }
                                            else
                                            {
                                                $html .= $this->getTimeScheduleArray();
                                            }
                                        $html .= '</div>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-12 sc_date" style="display: none;">';
                                        $html .= '<div class="row">';
                                            $html .= '<div class="col-md-6">';
                                                $html .= '<label for="start_date" class="form-label">Start Date</label>';
                                                $html .= '<input type="date" name="start_date" id="start_date" class="form-control" value="'.$category->sch_start_date.'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-6">';
                                                $html .= '<label for="end_date" class="form-label">End Date</label>';
                                                $html .= '<input type="date" name="end_date" id="end_date" class="form-control" value="'.$category->sch_end_date.'">';
                                            $html .= '</div>';
                                        $html .= '</div>';
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
                $html .= '<a class="active text-uppercase" onclick="updateByCode(\''.$primary_lang_code.'\')">'.$primary_lang_code.'</a>';
            $html .= '</div>';

            $html .= '<hr>';

            $html .= '<div class="row">';
                $html .= '<div class="col-md-12">';
                    $html .= '<form id="edit_category_form" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                        $html .= '<input type="hidden" name="category_id" id="category_id" value="'.$category['id'].'">';
                        $html .= '<input type="hidden" name="category_type" id="category_type" value="'.$category->category_type.'">';

                        // Category Type
                        // $html .= '<div class="row mb-3">';
                        //     $html .= '<div class="col-md-12">';
                        //         $html .= '<label class="form-label" for="category_type">'.__('Type').'</label>';
                        //         $html .= '<select onchange="changeElements(\'editCategoryModal\')" name="category_type" id="category_type" class="form-select category_type">';
                        //             foreach($category_types as $cat_type_key => $cat_type)
                        //             {
                        //                 $html .= '<option value="'.$cat_type_key.'"';

                        //                     if($cat_type_key == $category->category_type)
                        //                     {
                        //                         $html .= 'selected';
                        //                     }

                        //                 $html.='>'.$cat_type.'</option>';
                        //             }
                        //         $html .= '</select>';
                        //     $html .= '</div>';
                        //     $html .= '</div>';
                        // $html .= '</div>';

                        // Category
                        $html .= '<div class="row mb-3 cat_div">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'.__('Category').'</label>';
                                $html .= '<div id="categories_div">';
                                    $html .= '<input type="radio" name="parent_cat" id="root" value="0" '.$root_parent_cat_checked.'> <label for="root">Root</label> <br>';
                                    if(count($parent_categories) > 0)
                                    {
                                        foreach ($parent_categories as $key => $pcategory)
                                        {
                                            $parent_cat_check = ($pcategory->parent_id == $pcategory->id) ? 'checked' : '';
                                            $html .= '<input type="radio" name="parent_cat" id="pcat_'.$key.'" value="'.$pcategory->id.'" '.$parent_cat_check.'> <label for="pcat_'.$key.'">'.$pcategory->name.'</label><br>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Name
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                            $html .= '<label class="form-label" for="category_name">'.__('Name').'</label>';
                            $html .= '<input type="text" name="category_name" id="category_name" class="form-control" value="'.$category_name.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Sort Order
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                            $html .= '<label class="form-label" for="sort_order">'.__('Sort Order').'</label>';
                            $html .= '<input type="text" name="sort_order" id="sort_order" class="form-control" value="'.$category['order_key'].'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Url
                        $url_active = ($category->category_type == 'link') ? 'block' : 'none';
                        $html .= '<div class="row mb-3 url" style="display: '.$url_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="url">'.__('URL').'</label>';
                                $html .= '<input type="text" name="url" id="url" class="form-control" value="'.$category->link_url.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $check_page_style_active = ($category->category_type == 'check_in') ? 'block' : 'none';

                        // Background Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="background_color">'.__('Background Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[background_color]" id="background_color" class="form-control" value="'.$bg_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Font Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="font_color">'.__('Font Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[font_color]" id="font_color" class="form-control" value="'.$font_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="button_color">'.__('Button Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[button_color]" id="button_color" class="form-control" value="'.$btn_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Button Text Color
                        $html .= '<div class="row mb-3 chk_page_styles" style="display: '.$check_page_style_active.'">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="button_text_color">'.__('Button Text Color').'</label>';
                                $html .= '<input type="color" name="checkin_styles[button_text_color]" id="button_text_color" class="form-control" value="'.$btn_text_color.'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Description
                        $html .= '<div class="row mb-3 description">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label" for="category_description">'.__('Desription').'</label>';
                                $html .= '<textarea name="category_description" id="category_description" class="form-control category_description" rows="3">'.$category_description.'</textarea>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Images
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 d-flex flex-wrap" id="edit_images_div">';
                                if($category->category_type == 'product_category' || $category->category_type == 'page' || $category->category_type == 'gallery' || $category->category_type == 'parent_category')
                                {
                                    if(count($category_images) > 0)
                                    {
                                        foreach($category_images as $key => $cat_image)
                                        {
                                            $no = $key + 1;

                                            if(!empty($cat_image['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']))
                                            {
                                                $html .= '<div class="inner-img edit_img_'.$no.'">';
                                                    $html .= '<img src="'.asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image['image']).'" class="w-100 h-100">';
                                                    $html .= '<a class="btn btn-sm btn-danger del-pre-btn" onclick="deleteCategoryImage('.$no.','.$cat_image->id.')"><i class="fa fa-trash"></i></a>';
                                                $html .= '</div>';
                                            }
                                        }
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 mb-2 d-flex flex-wrap" id="images_div"></div>';
                            $html .= '<div class="col-md-12 mul-image" id="img-val"></div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3 mul-image">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('Image').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12">';
                                $html .= '<div id="img-label">';
                                    $html .= '<label for="category_image">Upload Images</label>';
                                    $html .= '<input type="file" name="category_image" id="category_image" class="form-control category_image" onchange="imageCropper(\'editCategoryModal\',this)" style="display:none">';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12">';
                                $html .= '<code class="img-upload-label">Upload Image in (400*400) Dimensions</code>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-8 img-crop-sec mb-2" style="display: none">';
                                $html .= '<img src="" alt="" id="resize-image" class="w-100 resize-image">';
                                $html .= '<div class="mt-3">';
                                    $html .= '<a class="btn btn-sm btn-success" onclick="saveCropper(\'editCategoryModal\')">Save</a>';
                                    $html .= '<a class="btn btn-sm btn-danger mx-2" onclick="resetCropper()">Reset</a>';
                                    $html .= '<a class="btn btn-sm btn-secondary" onclick="cancelCropper(\'editCategoryModal\')">Cancel</a>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-4 img-crop-sec" style="display: none;">';
                                $html .= '<div class="preview" style="width: 200px; height:200px; overflow: hidden;margin: 0 auto;"></div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Cover Image
                        $cover_active = ($category->category_type == 'page' || $category->category_type == 'link' || $category->category_type == 'gallery' || $category->category_type == 'check_in' || $category->category_type == 'parent_category' || $category->category_type == 'pdf_page') ? '' : 'none';
                        if(!empty($category->cover) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover))
                        {
                            $cover_image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$category->cover);
                        }
                        else
                        {
                            $cover_image = asset('public/client_images/not-found/no_image_1.jpg');
                        }
                        $html .= '<div class="row mb-3 cover" style="display: '.$cover_active.'" id="cover_label">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('Thumbnail').'</label>';
                                $html .= '<input type="file" onchange="CoverPreview(\'editCategoryModal\',this)" id="cover" name="cover" style="display: none">';
                                $html .= '<div class="page-cover">';
                                    $html .= '<label for="cover" id="upload-page-cover-image">';
                                        $html .= '<img src="'.$cover_image.'" class="w-100 h-100">';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // PDF File
                        $pdf_active = ($category->category_type == 'pdf_page') ? '' : 'none';
                        $html .= '<div class="row mb-3 pdf" style="display: '.$pdf_active.'" id="pdf_label">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label">'. __('PDF File').'</label>';
                                $html .= '<input type="file" onchange="PdfPreview(\'editCategoryModal\',this)" id="pdf" name="pdf" style="display: none">';
                                $html .= '<div class="pdf-file">';
                                    $html .= '<label for="pdf" id="upload-pdf-file">';
                                        $html .= '<img src="'.asset('public/client_images/not-found/no_image_1.jpg').'" class="w-100 h-100">';
                                    $html .= '</label>';
                                $html .= '</div>';
                                $html .= '<h4 class="mt-2" id="pdf-name">'.$category->file.'</h4>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Status
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<label class="form-label me-3" for="published">'.__('Published').'</label>';
                                $html .= '<label class="switch">';
                                    $html .= '<input type="checkbox" id="published" name="published" value="1" '.$category_status.'>';
                                    $html .= '<span class="slider round">';
                                        $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                        $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                    $html .= '</span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        // Schedule
                        $schedule_type = (isset($category->schedule_type) && !empty($category->schedule_type)) ? $category->schedule_type : 'time';
                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-12 mb-3">';
                                $html .= '<div class="input-label text-primary schedule-toggle">';
                                    $html .= '<i class="fa fa-clock" onclick="$(\'#editCategoryModal #schedule-main-div\').toggle()"></i> <span>'.$schedule_active_text.'</span>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-12 mb-3" id="schedule-main-div" style="display: ';
                            $html .= ($schedule == 1) ? '' : 'none';
                            $html .= ';">';
                                $html .= '<div class="row">';
                                    $html .= '<div class="col-md-6 mb-2">';
                                        $html .= '<select name="schedule_type" id="schedule_type" onchange="changeScheduleType(\'editCategoryModal\')" class="form-select">';
                                            $html .= '<option value="time"';
                                                if($schedule_type == 'time')
                                                {
                                                    $html .= 'selected';
                                                }
                                            $html .= '>Time</option>';
                                            $html .= '<option value="date"';
                                                if($schedule_type == 'date')
                                                {
                                                    $html .= 'selected';
                                                }
                                            $html .= '>Date</option>';
                                        $html .= '</select>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-6 text-end">';
                                        $html .= '<label class="switch">';
                                            $html .= '<input type="checkbox" id="schedule" name="schedule" value="1" onchange="changeScheduleLabel(\'editCategoryModal\')" '.$schedule_active.'>';
                                            $html .= '<span class="slider round">';
                                                $html .= '<i class="fa-solid fa-circle-check check_icon"></i>';
                                                $html .= '<i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i>';
                                            $html .= '</span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-md-12 sc_inner sc_time">';
                                        $html .= '<div class="sc_array_section" id="sc_array_section">';
                                            if(count($schedule_arr) > 0)
                                            {
                                                foreach($schedule_arr as $key => $sched)
                                                {
                                                    $schd_name = isset($sched['name']) ? $sched['name'] : '';
                                                    $active_day = ($sched['enabled'] == 1) ? 'checked' : '';
                                                    $time_arr = $sched['timesSchedules'];

                                                    $html .= '<div class="p-2" id="'.$key.'_sec">';
                                                        $html .= '<div class="text-center">';
                                                            $html .= '<input type="checkbox" class="me-2" name="" id="'.$key.'" '.$active_day.'> <label for="'.$key.'">'.$schd_name.'</label>';
                                                        $html .= '</div>';

                                                        $html .= '<div class="sch-sec">';
                                                            if(count($time_arr) > 0)
                                                            {
                                                                foreach($time_arr as $tkey => $sc_time)
                                                                {
                                                                    $time_key = $tkey + 1;
                                                                    $sc_start_time = isset($sc_time['startTime']) ? $sc_time['startTime'] : '';
                                                                    $sc_end_time = isset($sc_time['endTime']) ? $sc_time['endTime'] : '';

                                                                    $html .= '<div class="sch_'.$time_key.'">';
                                                                        if($time_key > 1)
                                                                        {
                                                                            $html .= '<div class="sch-minus"><i class="bi bi-dash-circle" onclick="$(\'#editCategoryModal #'.$key.'_sec .sch_'.$time_key.'\').remove()"></i></div>';
                                                                        }
                                                                        $html .= '<input type="time" class="form-control mt-2" name="startTime" id="startTime" value="'.$sc_start_time.'">';
                                                                        $html .= '<input type="time" class="form-control mt-2" name="endTime" id="endTime" value="'.$sc_end_time.'">';
                                                                    $html .= '</div>';

                                                                }
                                                            }
                                                        $html .= '</div>';

                                                        $html .= '<div class="sch-plus">';
                                                            $html .= '<i class="bi bi-plus-circle" onclick="addNewSchedule(\''.$key.'_sec\',\'editCategoryModal\')"></i>';
                                                        $html .= '</div>';

                                                    $html .= '</div>';
                                                }
                                            }
                                            else
                                            {
                                                $html .= $this->getTimeScheduleArray();
                                            }
                                        $html .= '</div>';
                                    $html .= '</div>';

                                    $html .= '<div class="col-md-12 sc_date" style="display: none;">';
                                        $html .= '<div class="row">';
                                            $html .= '<div class="col-md-6">';
                                                $html .= '<label for="start_date" class="form-label">Start Date</label>';
                                                $html .= '<input type="date" name="start_date" id="start_date" class="form-control" value="'.$category->sch_start_date.'">';
                                            $html .= '</div>';
                                            $html .= '<div class="col-md-6">';
                                                $html .= '<label for="end_date" class="form-label">End Date</label>';
                                                $html .= '<input type="date" name="end_date" id="end_date" class="form-control" value="'.$category->sch_end_date.'">';
                                            $html .= '</div>';
                                        $html .= '</div>';
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


    // Function for Change Category Status
    public function status(Request $request)
    {
        try
        {
            $id = $request->id;
            $published = $request->status;

            $category = Category::find($id);
            $category->published = $published;
            $category->update();

            return response()->json([
                'success' => 1,
                'message' => "Category Status has been Changed Successfully..",
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



    // Function for Filtered Categories
    public function searchCategories(Request $request)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';
        $keyword = $request->keywords;
        $parent_category_id = $request->par_cat_id;

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
            $categories = Category::with(['categoryImages'])->where($name_key,'LIKE','%'.$keyword.'%')->where('shop_id',$shop_id);

            if((empty($parent_category_id)) || (!empty($parent_category_id) && is_numeric($parent_category_id)))
            {
                $categories = $categories->where('parent_id',$parent_category_id);
            }
            else
            {
                $categories = $categories->where('category_type',$parent_category_id);
            }

            $categories = $categories->orderBy('order_key')->get();
            $html = '';

            if(count($categories) > 0)
            {
                foreach($categories as $category)
                {
                    $newStatus = ($category->published == 1) ? 0 : 1;
                    $checked = ($category->published == 1) ? 'checked' : '';

                    // Category Type
                    $category_type = '';
                    if($category->category_type == 'product_category')
                    {
                        $category_type = 'Category';
                    }
                    elseif ($category->category_type == 'page')
                    {
                        $category_type = 'Page';
                    }
                    elseif ($category->category_type == 'link')
                    {
                        $category_type = 'Link';
                    }
                    elseif ($category->category_type == 'gallery')
                    {
                        $category_type = 'Image Gallery';
                    }
                    elseif ($category->category_type == 'check_in')
                    {
                        $category_type = 'Check-In Page';
                    }
                    elseif ($category->category_type == 'parent_category')
                    {
                        $category_type = 'Child Category';
                    }
                    elseif ($category->category_type == 'pdf_page')
                    {
                        $category_type = 'PDF';
                    }


                    if($category->category_type == 'page' || $category->category_type == 'gallery' || $category->category_type == 'link' || $category->category_type == 'check_in' || $category->category_type == 'parent_category' || $category->category_type == 'pdf_page')
                    {
                        $cat_image = isset($category->cover) ? $category->cover : '';
                    }
                    else
                    {
                        $cat_image = isset($category->categoryImages[0]['image']) ? $category->categoryImages[0]['image'] : '';
                    }


                    if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
                    {
                        $image = asset('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image);
                    }
                    else
                    {
                        $image = asset('public/client_images/not-found/no_image_1.jpg');
                    }

                    $par_cat_url = route('categories',$category->id);

                    $html .= '<div class="col-md-3">';
                        $html .= '<div class="item_box">';
                            $html .= '<div class="item_img">';
                                $html .= '<a><img src="'.$image.'" class="w-100"></a>';
                                $html .= '<div class="edit_item_bt">';

                                    if($category->category_type == 'product_category')
                                    {
                                        $html .= '<button type="button" class="btn edit_item">'.__('ADD OR EDIT ITEMS').'</button>';
                                    }

                                    if($category->category_type == 'parent_category')
                                    {
                                        $html .= '<a href="'.$par_cat_url.'" class="btn edit_item">'.__("ADD OR EDIT CHILD CATEGORY").'</a>';
                                    }

                                    $html .= '<button class="btn edit_category" onclick="editCategory('.$category->id.')">'.__('EDIT').'</button>';
                                $html .= '</div>';
                                if($category->category_type == 'parent_category')
                                {
                                    $html .= '<a class="btn-search-cat" href="'.$par_cat_url.'"><i class="fa-solid fa-magnifying-glass"></i></a>';
                                }
                                $html .= '<a class="delet_bt" onclick="deleteCategory('.$category->id.')" style="cursor: pointer;"><i class="fa-solid fa-trash"></i></a>';
                                $html .= '<a class="cat_edit_bt" onclick="editCategory('.$category->id.')"><i class="fa-solid fa-edit"></i></a>';

                                $item_redirect_path = route('items',$category->id);

                                if($category->category_type == 'product_category')
                                {
                                    $html .= '<a class="item_add_bt" href="'.$item_redirect_path.'"><i class="fa-solid fa-add"></i></a>';
                                }

                            $html .= '</div>';
                            $html .= '<div class="item_info">';
                                $html .= '<div class="item_name">';
                                    $html .= '<h3>'.$category->en_name.'</h3>';
                                    $html .= '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" role="switch" id="status" onclick="changeStatus('.$category->id.','.$newStatus.')" value="1" '.$checked.'></div>';
                                $html .= '</div>';
                                $html .= '<h2>'.$category_type.'</h2>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';


                }
            }

            $html .= '<div class="col-md-3">';
                $html .= '<div class="item_box">';
                    $html .= '<div class="item_img add_category">';
                        $html .= '<a data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="add_category_bt" id="NewCategoryBtn"><i class="fa-solid fa-plus"></i></a>';
                    $html .= '</div>';
                    $html .= '<div class="item_info text-center"><h2>'.__('Product Category').'</h2></div>';
                $html .= '</div>';
            $html .= '</div>';

            return response()->json([
                'success' => 1,
                'message' => "Categories has been retrived Successfully...",
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



    // Function Delete Category Image
    public function deleteCategoryImage($id)
    {
        $category = Category::find($id);
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        if($category)
        {
            $cat_image = isset($category['image']) ? $category['image'] : '';

            if(!empty($cat_image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$cat_image);
            }

            $category->image = "";
            $category->update();
        }

        return redirect()->route('categories')->with('success',"Category Image has been Removed SuccessFully...");

    }



    // Delete Multiple Images
    public function deleteCategoryImages(Request $request)
    {
        $image_id = $request->img_id;
        $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

        try
        {
            $cat_image = CategoryImages::where('id',$image_id)->first();
            $image = isset($cat_image->image) ? $cat_image->image : '';

            if(!empty($image) && file_exists('public/client_uploads/shops/'.$shop_slug.'/categories/'.$image))
            {
                unlink('public/client_uploads/shops/'.$shop_slug.'/categories/'.$image);
            }

            CategoryImages::where('id',$image_id)->delete();

            return response()->json([
                'success' => 1,
                "message" => "Image has been Removed SuccessFully...",
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


    // Function for Sorting Category.
    public function sorting(Request $request)
    {
        $sort_array = $request->sortArr;

        foreach ($sort_array as $key => $value)
        {
    		$key = $key+1;
    		Category::where('id',$value)->update(['order_key'=>$key]);
    	}

        return response()->json([
            'success' => 1,
            'message' => "Category has been Sorted SuccessFully....",
        ]);

    }
}
