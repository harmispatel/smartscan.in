<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\Languages;
use App\Models\Option;
use App\Models\OptionPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $data['shop_id'] = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        $data['options'] = Option::with(['optionPrices'])->where('shop_id',$data['shop_id'])->get();

        // Subscrption ID
        $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

        // Get Package Permissions
        $package_permissions = getPackagePermission($subscription_id);

        if(isset($package_permissions['ordering']) && !empty($package_permissions['ordering']) && $package_permissions['ordering'] == 1)
        {
            return view('client.options.options',$data);
        }
        else
        {
            return redirect()->route('client.dashboard')->with('error','Unauthorized Action!');
        }

    }


    // Show the form for creating a new resource.
    public function create()
    {
        //
    }


    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'option.name.*' => 'required',
            'option.price.*' => 'required',
        ]);

        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Language Details
        $language_detail = Languages::where('id',$primary_lang_id)->first();
        $lang_code = isset($language_detail->code) ? $language_detail->code : '';

        $title_key = $lang_code."_title";
        $name_key = $lang_code."_name";

        $options = isset($request->option) ? $request->option : [];
        $multiple_selection = isset($request->multiple_selection) ? $request->multiple_selection : 0;
        $enabled_price = isset($request->enabled_price) ? $request->enabled_price : 0;

        if(count($options) > 0)
        {
            try
            {
                // Insert Option
                $option = new Option();
                $option->shop_id = $shop_id;
                $option->title = $request->title;
                $option->multiple_select = $multiple_selection;
                $option->enabled_price = $enabled_price;

                if($multiple_selection == 1)
                {
                    $option->pre_select = 1;
                }

                $option->$title_key = $request->title;
                $option->save();

                // Insert Option Price
                $option_name_arr = $options['name'];
                $option_price_arr = $options['price'];

                if($option)
                {
                    if(count($option_name_arr) > 0)
                    {
                        foreach($option_name_arr as $key => $opt_name)
                        {
                            $opt_price = isset($option_price_arr[$key]) ? $option_price_arr[$key] : '';
                            $option_price = new OptionPrice();
                            $option_price->option_id = $option->id;
                            $option_price->shop_id = $shop_id;
                            $option_price->price = $opt_price;
                            $option_price->name = $opt_name;
                            $option_price->$name_key = $opt_name;
                            $option_price->save();
                        }
                    }
                }

                return response()->json([
                    'success' => 1,
                    'message' => "Option has been Inserted SuccessFully...",
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
        else
        {
            $request->validate([
                'option' => 'required',
            ]);
        }

    }


    // Display the specified resource.
    public function show(Option $option)
    {
        //
    }


    // Show the form for editing the specified resource.
    public function edit(Request $request)
    {
        $option_id = $request->id;
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        try
        {
            // Option Details
            $option_details = Option::with(['optionPrices'])->where('id',$option_id)->first();
            $multiple_selection_active = (isset($option_details['multiple_select']) && $option_details['multiple_select'] == 1) ? 'checked' : '';
            $enable_price_active = (isset($option_details['enabled_price']) && $option_details['enabled_price'] == 1) ? 'checked' : '';
            $pre_selection_active = (isset($option_details['pre_select']) && $option_details['pre_select'] == 1) ? 'checked' : '';
            $option_prices = (isset($option_details['optionPrices'])) ? $option_details['optionPrices'] : [];

            // Get Language Settings
            $language_settings = clientLanguageSettings($shop_id);
            $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

            // Primary Language Details
            $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
            $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
            $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

            // Additional Languages
            $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

            // Dynamic Language Bar
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
                        $html .= '<form id="editOptionForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="option_id" id="option_id" value="'.$option_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="title" class="form-label">'. __('Title') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<input type="text" name="title" id="title" class="form-control" placeholder="Enter Option Title" value="'.$option_details[$primary_lang_code."_title"].'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="multiple_selection" class="form-label">'. __('Multiple Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="multiple_selection" name="multiple_selection" '.$multiple_selection_active.' onchange="togglePreSelection()"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="enabled_price" class="form-label">'. __('Enabled Prices').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="enabled_price" name="enabled_price" '.$enable_price_active.' onchange="togglePrices(\'editOptionModal\')"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3 pre-select" style="display:';
                                if($multiple_selection_active == '')
                                {
                                    $html .= 'none';
                                }
                            $html .= '">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="pre_select" class="form-label">'. __('Pre Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="pre_select" name="pre_select" '.$pre_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4"><label class="form-label">'.__('Options').'</label></div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<div class="row">';

                                        if(count($option_prices) > 0)
                                        {
                                            $html .= '<div class="col-md-12 mb-2" id="option_sec">';
                                                foreach($option_prices as $key => $opt_price)
                                                {
                                                    $opt_name = isset($opt_price[$primary_lang_code."_name"]) ? $opt_price[$primary_lang_code."_name"] : '';
                                                    $price = isset($opt_price['price']) ? $opt_price['price'] : 0.00;
                                                    $opt_key = $key + 1;

                                                    $html .= '<div class="row mb-2 option" id="option_'.$opt_key.'">';
                                                        $html .= '<div class="col-md-6">';
                                                            $html .= '<input type="hidden" name="option[price_id][]" value="'.$opt_price['id'].'">';
                                                            $html .= '<input type="text" name="option[name][]" class="form-control" placeholder="Option Name" value="'.$opt_name.'">';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-md-4">';
                                                            $html .= '<input type="number" name="option[price][]" class="form-control opt-price" placeholder="Option Price" value="'.$price.'">';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-md-2">';
                                                            $html .= '<a class="btn btn-sm btn-danger" onclick="deleteOptionPrice('.$opt_price['id'].','.$opt_key.')"><i class="fa fa-trash"></i></a>';
                                                        $html .= '</div>';
                                                    $html .= '</div>';
                                                }
                                            $html .= '</div>';
                                        }

                                        $html .= '<div class="col-md-12"><a class="btn btn-sm btn-primary" onclick="addOption(\'editOptionForm\')">'. __('Add Option').'</a>
                                    </div>';
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
                        $html .= '<form id="editOptionForm" enctype="multipart/form-data">';

                            $html .= csrf_field();
                            $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                            $html .= '<input type="hidden" name="option_id" id="option_id" value="'.$option_details['id'].'">';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="title" class="form-label">'. __('Title') .'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<input type="text" name="title" id="title" class="form-control" placeholder="Enter Option Title" value="'.$option_details[$primary_lang_code."_title"].'">';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="multiple_selection" class="form-label">'. __('Multiple Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input onchange="togglePreSelection()" value="1" type="checkbox" id="multiple_selection" name="multiple_selection" '.$multiple_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="enabled_price" class="form-label">'. __('Enabled Prices').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="enabled_price" name="enabled_price" '.$enable_price_active.' onchange="togglePrices(\'editOptionModal\')"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3 pre-select" style="display:';
                                if($multiple_selection_active == '')
                                {
                                    $html .= 'none';
                                }
                            $html .= '">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="pre_select" class="form-label">'. __('Pre Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="pre_select" name="pre_select" '.$pre_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="row mb-3">';
                                $html .= '<div class="col-md-4"><label class="form-label">'.__('Options').'</label></div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<div class="row">';

                                        if(count($option_prices) > 0)
                                        {
                                            $html .= '<div class="col-md-12 mb-2" id="option_sec">';
                                                foreach($option_prices as $key => $opt_price)
                                                {
                                                    $opt_name = isset($opt_price[$primary_lang_code."_name"]) ? $opt_price[$primary_lang_code."_name"] : '';
                                                    $price = isset($opt_price['price']) ? $opt_price['price'] : 0.00;
                                                    $opt_key = $key + 1;

                                                    $html .= '<div class="row mb-2 option" id="option_'.$opt_key.'">';
                                                        $html .= '<div class="col-md-6">';
                                                            $html .= '<input type="hidden" name="option[price_id][]" value="'.$opt_price['id'].'">';
                                                            $html .= '<input type="text" name="option[name][]" class="form-control" placeholder="Option Name" value="'.$opt_name.'">';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-md-4">';
                                                            $html .= '<input type="number" name="option[price][]" class="form-control opt-price" placeholder="Option Price" value="'.$price.'">';
                                                        $html .= '</div>';
                                                        $html .= '<div class="col-md-2">';
                                                            $html .= '<a class="btn btn-sm btn-danger" onclick="deleteOptionPrice('.$opt_price['id'].','.$opt_key.')"><i class="fa fa-trash"></i></a>';
                                                        $html .= '</div>';
                                                    $html .= '</div>';
                                                }
                                            $html .= '</div>';
                                        }

                                        $html .= '<div class="col-md-12"><a class="btn btn-sm btn-primary" onclick="addOption(\'editOptionForm\')">'. __('Add Option').'</a>
                                    </div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</form>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            return response()->json([
                'success' => 1,
                'message' => 'Data has been Fetched SuccessFully..',
                'data' => $html,
                'enable_price' => $enable_price_active,
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


    // Update Option Data When Change Tab
    public function updateByLangCode(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $option_id = $request->option_id;
        $options = isset($request->option) ? $request->option : [];
        $multiple_selection = isset($request->multiple_selection) ? $request->multiple_selection : 0;
        $pre_select = isset($request->pre_select) ? $request->pre_select : 0;
        $enabled_price = isset($request->enabled_price) ? $request->enabled_price : 0;
        $active_lang_code = $request->active_lang_code;
        $next_lang_code = $request->next_lang_code;

        $request->validate([
            'title' => 'required',
            'option.name.*' => 'required',
            'option.price.*' => 'required',
        ]);

        if(count($options) > 0)
        {
            try
            {
                $update_title_key = $active_lang_code."_title";
                $update_name_key = $active_lang_code."_name";

                $option = Option::find($option_id);
                $option->title = $request->title;
                $option->multiple_select = $multiple_selection;
                $option->enabled_price = $enabled_price;

                if($multiple_selection == 1)
                {
                    $option->pre_select = $pre_select;
                }

                $option->$update_title_key = $request->title;
                $option->update();

                // Insert & Update Option Price
                $option_name_arr = $options['name'];
                $option_price_arr = $options['price'];
                $option_price_id_arr = $options['price_id'];

                if(count($option_name_arr) > 0)
                {
                    foreach($option_name_arr as $key => $opt_name)
                    {
                        $opt_price = isset($option_price_arr[$key]) ? $option_price_arr[$key] : '';
                        $opt_price_id = isset($option_price_id_arr[$key]) ? $option_price_id_arr[$key] : '';

                        if(!empty($opt_price_id) || $opt_price_id != '') // Update Price
                        {
                            $update_option_price = OptionPrice::find($opt_price_id);
                            $update_option_price->price = $opt_price;
                            $update_option_price->name = $opt_name;
                            $update_option_price->$update_name_key = $opt_name;
                            $update_option_price->update();
                        }
                        else
                        {
                            $option_price = new OptionPrice();
                            $option_price->option_id = $option_id;
                            $option_price->shop_id = $shop_id;
                            $option_price->price = $opt_price;
                            $option_price->name = $opt_name;
                            $option_price->$update_name_key = $opt_name;
                            $option_price->save();
                        }

                    }
                }

                $html_data = $this->getEditOptionData($next_lang_code,$option_id);

                $enable_price_active = (isset($option['enabled_price']) && $option['enabled_price'] == 1) ? 'checked' : '';

                return response()->json([
                    'success' => 1,
                    'message' => 'Data has been Updated SuccessFully...',
                    'data' => $html_data,
                    'enable_price' => $enable_price_active,
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
        else
        {
            $request->validate([
                'option' => 'required',
            ]);
        }

    }


    // Get Option Data By Language Code & Option ID
    public function getEditOptionData($current_lang_code,$option_id)
    {
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        // Option Details
        $option_details = Option::with(['optionPrices'])->where('id',$option_id)->first();
        $multiple_selection_active = (isset($option_details['multiple_select']) && $option_details['multiple_select'] == 1) ? 'checked' : '';
        $enable_price_active = (isset($option_details['enabled_price']) && $option_details['enabled_price'] == 1) ? 'checked' : '';
        $pre_selection_active = (isset($option_details['pre_select']) && $option_details['pre_select'] == 1) ? 'checked' : '';
        $option_prices = (isset($option_details['optionPrices'])) ? $option_details['optionPrices'] : [];

        // Get Language Settings
        $language_settings = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_settings['primary_language']) ? $language_settings['primary_language'] : '';

        // Primary Language Details
        $primary_language_detail = Languages::where('id',$primary_lang_id)->first();
        $primary_lang_code = isset($primary_language_detail->code) ? $primary_language_detail->code : '';
        $primary_lang_name = isset($primary_language_detail->name) ? $primary_language_detail->name : '';

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$shop_id)->get();

        // Primary Active Tab
        $primary_active_tab = ($primary_lang_code == $current_lang_code) ? 'active' : '';

        // Dynamic Language Bar
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
                    $html .= '<form id="editOptionForm" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$current_lang_code.'">';
                        $html .= '<input type="hidden" name="option_id" id="option_id" value="'.$option_details['id'].'">';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="title" class="form-label">'. __('Title') .'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<input type="text" name="title" id="title" class="form-control" placeholder="Enter Option Title" value="'.$option_details[$current_lang_code."_title"].'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="multiple_selection" class="form-label">'. __('Multiple Selection').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<label class="switch ms-2">';
                                    $html .= '<input type="checkbox" onchange="togglePreSelection()" id="multiple_selection" name="multiple_selection" '.$multiple_selection_active.' value="1"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="enabled_price" class="form-label">'. __('Enabled Prices').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<label class="switch ms-2">';
                                    $html .= '<input value="1" type="checkbox" id="enabled_price" name="enabled_price" '.$enable_price_active.' onchange="togglePrices(\'editOptionModal\')"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3 pre-select" style="display:';
                                if($multiple_selection_active == '')
                                {
                                    $html .= 'none';
                                }
                            $html .= '">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="pre_select" class="form-label">'. __('Pre Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="pre_select" name="pre_select" '.$pre_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4"><label class="form-label">'.__('Options').'</label></div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<div class="row">';

                                    if(count($option_prices) > 0)
                                    {
                                        $html .= '<div class="col-md-12 mb-2" id="option_sec">';
                                            foreach($option_prices as $key => $opt_price)
                                            {
                                                $opt_name = isset($opt_price[$current_lang_code."_name"]) ? $opt_price[$current_lang_code."_name"] : '';
                                                $price = isset($opt_price['price']) ? $opt_price['price'] : 0.00;
                                                $opt_key = $key + 1;

                                                $html .= '<div class="row mb-2 option" id="option_'.$opt_key.'">';
                                                    $html .= '<div class="col-md-6">';
                                                        $html .= '<input type="hidden" name="option[price_id][]" value="'.$opt_price['id'].'">';
                                                        $html .= '<input type="text" name="option[name][]" class="form-control" placeholder="Option Name" value="'.$opt_name.'">';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-4">';
                                                        $html .= '<input type="number" name="option[price][]" class="form-control opt-price" placeholder="Option Price" value="'.$price.'">';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-2">';
                                                        $html .= '<a class="btn btn-sm btn-danger" onclick="deleteOptionPrice('.$opt_price['id'].','.$opt_key.')"><i class="fa fa-trash"></i></a>';
                                                    $html .= '</div>';
                                                $html .= '</div>';
                                            }
                                        $html .= '</div>';
                                    }

                                    $html .= '<div class="col-md-12"><a class="btn btn-sm btn-primary" onclick="addOption(\'editOptionForm\')">'. __('Add Option').'</a>
                                </div>';
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
                    $html .= '<form id="editOptionForm" enctype="multipart/form-data">';

                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="active_lang_code" id="active_lang_code" value="'.$primary_lang_code.'">';
                        $html .= '<input type="hidden" name="option_id" id="option_id" value="'.$option_details['id'].'">';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="title" class="form-label">'. __('Title') .'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<input type="text" name="title" id="title" class="form-control" placeholder="Enter Option Title" value="'.$option_details[$primary_lang_code."_title"].'">';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="multiple_selection" class="form-label">'. __('Multiple Selection').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<label class="switch ms-2">';
                                    $html .= '<input value="1" type="checkbox" onchange="togglePreSelection()" id="multiple_selection" name="multiple_selection" '.$multiple_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4">';
                                $html .= '<label for="enabled_price" class="form-label">'. __('Enabled Prices').'</label>';
                            $html .= '</div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<label class="switch ms-2">';
                                    $html .= '<input value="1" type="checkbox" id="enabled_price" name="enabled_price" '.$enable_price_active.' onchange="togglePrices(\'editOptionModal\')"><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                $html .= '</label>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="row mb-3 pre-select" style="display:';
                                if($multiple_selection_active == '')
                                {
                                    $html .= 'none';
                                }
                            $html .= '">';
                                $html .= '<div class="col-md-4">';
                                    $html .= '<label for="pre_select" class="form-label">'. __('Pre Selection').'</label>';
                                $html .= '</div>';
                                $html .= '<div class="col-md-8">';
                                    $html .= '<label class="switch ms-2">';
                                        $html .= '<input value="1" type="checkbox" id="pre_select" name="pre_select" '.$pre_selection_active.'><span class="slider round"><i class="fa-solid fa-circle-check check_icon"></i><i class="fa-sharp fa-solid fa-circle-xmark uncheck_icon"></i></span>';
                                    $html .= '</label>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '<div class="row mb-3">';
                            $html .= '<div class="col-md-4"><label class="form-label">'.__('Options').'</label></div>';
                            $html .= '<div class="col-md-8">';
                                $html .= '<div class="row">';

                                    if(count($option_prices) > 0)
                                    {
                                        $html .= '<div class="col-md-12 mb-2" id="option_sec">';
                                            foreach($option_prices as $key => $opt_price)
                                            {
                                                $opt_name = isset($opt_price[$primary_lang_code."_name"]) ? $opt_price[$primary_lang_code."_name"] : '';
                                                $price = isset($opt_price['price']) ? $opt_price['price'] : 0.00;
                                                $opt_key = $key + 1;

                                                $html .= '<div class="row mb-2 option" id="option_'.$opt_key.'">';
                                                    $html .= '<div class="col-md-6">';
                                                        $html .= '<input type="hidden" name="option[price_id][]" value="'.$opt_price['id'].'">';
                                                        $html .= '<input type="text" name="option[name][]" class="form-control" placeholder="Option Name" value="'.$opt_name.'">';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-4">';
                                                        $html .= '<input type="number" name="option[price][]" class="form-control opt-price" placeholder="Option Price" value="'.$price.'">';
                                                    $html .= '</div>';
                                                    $html .= '<div class="col-md-2">';
                                                        $html .= '<a class="btn btn-sm btn-danger" onclick="deleteOptionPrice('.$opt_price['id'].','.$opt_key.')"><i class="fa fa-trash"></i></a>';
                                                    $html .= '</div>';
                                                $html .= '</div>';
                                            }
                                        $html .= '</div>';
                                    }

                                    $html .= '<div class="col-md-12"><a class="btn btn-sm btn-primary" onclick="addOption(\'editOptionForm\')">'. __('Add Option').'</a>
                                </div>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                    $html .= '</form>';
                $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }


    // Update the specified resource in storage.
    public function update(Request $request)
    {
        // Shop ID
        $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';

        $option_id = $request->option_id;
        $options = isset($request->option) ? $request->option : [];
        $multiple_selection = isset($request->multiple_selection) ? $request->multiple_selection : 0;
        $pre_select = isset($request->pre_select) ? $request->pre_select : 0;
        $enabled_price = isset($request->enabled_price) ? $request->enabled_price : 0;
        $active_lang_code = $request->active_lang_code;

        $request->validate([
            'title' => 'required',
            'option.name.*' => 'required',
            'option.price.*' => 'required',
        ]);

        if(count($options) > 0)
        {
            try
            {
                $update_title_key = $active_lang_code."_title";
                $update_name_key = $active_lang_code."_name";

                $option = Option::find($option_id);
                $option->title = $request->title;
                $option->multiple_select = $multiple_selection;
                $option->enabled_price = $enabled_price;

                if($multiple_selection == 1)
                {
                    $option->pre_select = $pre_select;
                }

                $option->$update_title_key = $request->title;
                $option->update();

                // Insert & Update Option Price
                $option_name_arr = $options['name'];
                $option_price_arr = $options['price'];
                $option_price_id_arr = $options['price_id'];

                if(count($option_name_arr) > 0)
                {
                    foreach($option_name_arr as $key => $opt_name)
                    {
                        $opt_price = isset($option_price_arr[$key]) ? $option_price_arr[$key] : '';
                        $opt_price_id = isset($option_price_id_arr[$key]) ? $option_price_id_arr[$key] : '';

                        if(!empty($opt_price_id) || $opt_price_id != '') // Update Price
                        {
                            $update_option_price = OptionPrice::find($opt_price_id);
                            $update_option_price->price = $opt_price;
                            $update_option_price->name = $opt_name;
                            $update_option_price->$update_name_key = $opt_name;
                            $update_option_price->update();
                        }
                        else
                        {
                            $option_price = new OptionPrice();
                            $option_price->option_id = $option_id;
                            $option_price->shop_id = $shop_id;
                            $option_price->price = $opt_price;
                            $option_price->name = $opt_name;
                            $option_price->$update_name_key = $opt_name;
                            $option_price->save();
                        }

                    }
                }

                return response()->json([
                    'success' => 1,
                    'message' => 'Option has been Updated SuccessFully...',
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
        else
        {
            $request->validate([
                'option' => 'required',
            ]);
        }
    }


    // Remove the specified resource from storage.
    public function destroy(Request $request)
    {
        $option_id = $request->id;

        try
        {
            // Delete Options Price
            OptionPrice::where('option_id',$option_id)->delete();

            // Delete Option
            Option::where('id',$option_id)->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Option has been Removed SuccessFully....',
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


    // Delete Option Price
    public function deleteOptionPrice(Request $request)
    {
        $price_id = $request->price_id;
        try
        {
            // Delete Option Price
            OptionPrice::where('id',$price_id)->delete();
            return response()->json([
                'success' => 1,
                'message' => 'Option has been Removed SuccessFully....',
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
