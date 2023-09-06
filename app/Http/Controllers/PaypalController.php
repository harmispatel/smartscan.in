<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Api\{Amount, Details, Item,ItemList,Payer,Payment,PaymentExecution,RedirectUrls,Transaction};
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use App\Models\{Items,Shop,AdditionalLanguage,ItemPrice, OptionPrice, Order, OrderItems, User, UserShop};
use Exception;
use Magarrent\LaravelCurrencyFormatter\Facades\Currency;

class PaypalController extends Controller
{
    private $_api_context;

    public function payWithpaypal($shop_slug)
    {
        $all_item = [];
        $checkout_type = session()->get('checkout_type');

        if(empty($checkout_type))
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','UnAuthorized Request!');
        }

        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        $user_id = (isset( $data['shop_details']->usershop->user->id)) ?  $data['shop_details']->usershop->user->id : '';

        $user_details = User::where('id',$user_id)->first();
        $sgst = (isset($user_details['sgst'])) ? $user_details['sgst'] : 0;
        $cgst = (isset($user_details['cgst'])) ? $user_details['cgst'] : 0;

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

        $shop_settings = getClientSettings($shop_id);

        // Shop Currency
        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Get all Additional Language of Shop
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

        $discount_per = session()->get('discount_per');
        $discount_type = session()->get('discount_type');

        // Keys
        $name_key = $current_lang_code."_name";
        $label_key = $current_lang_code."_label";

        $final_amount = 0;

        $paypal_config = getPayPalConfig($shop_slug);
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_config['client_id'],
            $paypal_config['secret'])
        );
        $this->_api_context->setConfig($paypal_config['settings']);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        // Get Cart Details
        $cart = session()->get('cart', []);

        if(count($cart) == 0)
        {
            return redirect()->route('restaurant',$shop_slug);
        }


        // Add Items
        foreach($cart as $cart_data)
        {
            if(count($cart_data) > 0)
            {
                foreach($cart_data as $cart_val)
                {
                    if(count($cart_val) > 0)
                    {
                        foreach($cart_val as $cart_item)
                        {
                            $otpions_arr = [];
                            $item_price = 0.00;
                            $total_amount = $cart_item['total_amount'];
                            $total_amount_text = $cart_item['total_amount_text'];
                            $categories_data = (isset($cart_item['categories_data']) && !empty($cart_item['categories_data'])) ? $cart_item['categories_data'] : [];

                            if(count($categories_data) > 0)
                            {
                                foreach ($categories_data as $option_id)
                                {
                                    $my_opt = $option_id;
                                    if(is_array($my_opt))
                                    {
                                        if(count($my_opt) > 0)
                                        {
                                            foreach ($my_opt as $optid)
                                            {
                                                $opt_price_dt = OptionPrice::where('id',$optid)->first();
                                                $opt_price = (isset($opt_price_dt['price'])) ? $opt_price_dt['price'] : 0.00;
                                                $item_price += $opt_price;
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $opt_price_dt = OptionPrice::where('id',$my_opt)->first();
                                        $opt_price = (isset($opt_price_dt['price'])) ? $opt_price_dt['price'] : 0.00;
                                        $item_price += $opt_price;
                                    }
                                }
                            }

                            // Item Details
                            $item_details = Items::where('id',$cart_item['item_id'])->first();
                            $item_discount = (isset($item_details['discount'])) ? $item_details['discount'] : 0;
                            $item_discount_type = (isset($item_details['discount_type'])) ? $item_details['discount_type'] : 'percentage';
                            $item_name = (isset($item_details[$name_key])) ? $item_details[$name_key] : '';

                            //Price Details
                            $price_detail = ItemPrice::where('id',$cart_item['option_id'])->first();
                            $price_label = (isset($price_detail[$label_key])) ? $price_detail[$label_key] : '';
                            $item_qty = $cart_item['quantity'];
                            if(isset($price_detail['price']))
                            {
                                if($item_discount > 0)
                                {
                                    if($item_discount_type == 'fixed')
                                    {
                                        $new_price = number_format($price_detail['price'] - $item_discount, 2);
                                    }
                                    else
                                    {
                                        $dis_per = $price_detail['price'] * $item_discount / 100;
                                        $new_price = number_format($price_detail['price'] - $dis_per, 2);;
                                    }
                                    $item_price += $new_price;
                                }
                                else
                                {
                                    $item_price += $price_detail['price'];
                                }
                            }

                            if(!empty($price_label))
                            {
                                $otpions_arr[] = $price_label;
                            }

                            $final_amount += $total_amount;

                            $item = new Item();
                            $item->setName($item_name);
                            $item->setCurrency($currency);
                            $item->setQuantity($item_qty);
                            $item->setPrice($item_price);
                            $all_item[] = $item;
                        }
                    }
                }
            }

        }

        // GST Amount
        if($cgst > 0 && $sgst > 0)
        {
            $gst_per =  $cgst + $sgst;

            if(count($all_item) > 0)
            {
                foreach($all_item as $key=> $a_item)
                {
                    $all_item[$key]->price = $a_item->price + ($a_item->price * $gst_per) / 100;
                }
            }

            $final_amount += ($final_amount * $gst_per) / 100;
        }


        $item_list = new ItemList();
        $item_list->setItems($all_item);

        $amount = new Amount();
        $amount->setCurrency($currency);

        $final_amount = number_format($final_amount,2);

        if($discount_per > 0)
        {
            if($discount_type == 'fixed')
            {
                $discount_amount = $discount_per;
            }
            else
            {
                $discount_amount = number_format(($final_amount * $discount_per) / 100,2);
            }
            $total = number_format($final_amount - $discount_amount,2);

            $amount->setTotal($total);
            $amount->setDetails( new Details([
                'subtotal' => $final_amount,
                'discount' => number_format($discount_amount,2),
                'currency' => $currency,
            ]));
        }
        else
        {
            $amount->setTotal($final_amount);
        }

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($item_list)->setDescription('Your transaction description')->setInvoiceNumber(uniqid());

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(route('paypal.payment.status',$shop_slug))->setCancelUrl(route('paypal.payment.status',$shop_slug));

        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)->setTransactions(array($transaction));

        try
        {
            $payment->create($this->_api_context);
        }
        catch (Exception $ex)
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','Payment Failed!');
        }

        foreach($payment->getLinks() as $link)
        {
            if($link->getRel() == 'approval_url')
            {
                $redirect_url = $link->getHref();
                break;
            }
        }

        // add payment ID to session
        session()->put('paypal_payment_id', $payment->getId());
        session()->save();

        if(isset($redirect_url))
        {
            // redirect to paypal
            return redirect($redirect_url);
        }
    }


    public function paymentCancel($shop_slug)
    {
       return redirect()->route('restaurant',$shop_slug)->with('error','Payment Cancel!');
    }


    public function getPaymentStatus($shop_slug, Request $request)
    {
        $cart = session()->get('cart', []);
        $discount_per = session()->get('discount_per');
        $discount_type = session()->get('discount_type');

        // Shop Details
        $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

        $user_id = (isset( $data['shop_details']->usershop->user->id)) ?  $data['shop_details']->usershop->user->id : '';

        $user_details = User::where('id',$user_id)->first();
        $sgst = (isset($user_details['sgst'])) ? $user_details['sgst'] : 0;
        $cgst = (isset($user_details['cgst'])) ? $user_details['cgst'] : 0;

        // Shop ID
        $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';
        $shop_name = isset($data['shop_details']->name) ? $data['shop_details']->name : '';
        $shop_url = (isset($data['shop_details']->shop_slug)) ? $data['shop_details']->shop_slug : '';
        $shop_url = asset($shop_url);
        $shop_name = '<a href="'.$shop_url.'">'.$shop_name.'</a>';
        $shop_logo = (isset($data['shop_details']->logo)) ? $data['shop_details']->logo : '';
        $shop_logo = '<img src="'.$shop_logo.'" width="200">';

        $shop_user = UserShop::with(['user'])->where('shop_id',$shop_id)->first();
        $contact_emails = (isset($shop_user->user['contact_emails']) && !empty($shop_user->user['contact_emails'])) ? unserialize($shop_user->user['contact_emails']) : [];

        $shop_settings = getClientSettings($shop_id);

        // Order Mail Template
        $orders_mail_form_client = (isset($shop_settings['orders_mail_form_client'])) ? $shop_settings['orders_mail_form_client'] : '';

        // Ip Address
        $user_ip = $request->ip();

        $total_amount = 0;
        $subtotal_amount = 0;
        $discount_amount = 0;
        $gst_amount = 0;
        $total_qty = 0;

        // Shop Currency
        $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

        // Primary Language Details
        $language_setting = clientLanguageSettings($shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $data['primary_language_details'] = getLangDetails($primary_lang_id);

        // Get all Additional Language of Shop
        $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

        // Current Languge Code
        $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

        // Order Settings
        $order_settings = getOrderSettings($shop_id);

        // Optional Fields
        $email_field = (isset($order_settings['email_field']) && $order_settings['email_field'] == 1) ? $order_settings['email_field'] : 0;
        $floor_field = (isset($order_settings['floor_field']) && $order_settings['floor_field'] == 1) ? $order_settings['floor_field'] : 0;
        $door_bell_field = (isset($order_settings['door_bell_field']) && $order_settings['door_bell_field'] == 1) ? $order_settings['door_bell_field'] : 0;
        $full_name_field = (isset($order_settings['full_name_field']) && $order_settings['full_name_field'] == 1) ? $order_settings['full_name_field'] : 0;
        $instructions_field = (isset($order_settings['instructions_field']) && $order_settings['instructions_field'] == 1) ? $order_settings['instructions_field'] : 0;
        $live_address_field = (isset($order_settings['live_address_field']) && $order_settings['live_address_field'] == 1) ? $order_settings['live_address_field'] : 0;

        if(isset($order_settings['auto_order_approval']) && $order_settings['auto_order_approval'] == 1)
        {
            $order_status = 'accepted';
            $is_new = 0;
        }
        else
        {
            $order_status = 'pending';
            $is_new = 1;
        }

        // Keys
        $name_key = $current_lang_code."_name";
        $label_key = $current_lang_code."_label";

        $order_details = session()->get('order_details');

        $paypal_config = getPayPalConfig($shop_slug);
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_config['client_id'],
            $paypal_config['secret'])
        );
        $this->_api_context->setConfig($paypal_config['settings']);

        // Get the payment ID before session clear
        $payment_id = session()->get('paypal_payment_id');

        if(empty($request->PayerID) || empty($request->token))
        {
            return redirect()->route('restaurant',$shop_slug)->with('error', 'Payment failed!');
        }

        $payment = Payment::get($payment_id, $this->_api_context);

        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($request->PayerID);

        //Execute the payment
        try
        {
            $result = $payment->execute($execution, $this->_api_context);
        }
        catch (\Throwable $th)
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','Payment Failed!');
        }

        if($result->getState() == 'approved') // payment made
        {
            $checkout_type = $order_details['checkout_type'];
            $payment_method = $order_details['payment_method'];

            // New Order
            $order = new Order();
            $order->shop_id = $shop_id;
            $order->ip_address = $user_ip;
            $order->currency = $currency;
            $order->checkout_type = $checkout_type;
            $order->payment_method = $payment_method;
            $order->order_status = $order_status;
            $order->is_new = $is_new;
            $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';

            if($checkout_type == 'takeaway')
            {
                if($full_name_field == 1)
                {
                    $order->firstname =  $order_details['name'];
                }
                else
                {
                    $order->firstname = $order_details['firstname'];
                    $order->lastname = $order_details['lastname'];
                }

                if($email_field == 1)
                {
                    $order->email =  $order_details['email'];
                }
                $order->phone =  $order_details['phone'];
            }
            elseif($checkout_type == 'table_service')
            {
                $order->table = $order_details['table'];
            }
            elseif($checkout_type == 'room_delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname =  $order_details['name'];
                }
                else
                {
                    $order->firstname = $order_details['firstname'];
                    $order->lastname = $order_details['lastname'];
                }
                $order->room = $order_details['room'];
                $order->delivery_time = (isset($order_details['delivery_time'])) ? $order_details['delivery_time'] : '';
            }
            elseif($checkout_type == 'delivery')
            {
                if($full_name_field == 1)
                {
                    $order->firstname =  $order_details['name'];
                }
                else
                {
                    $order->firstname = $order_details['firstname'];
                    $order->lastname = $order_details['lastname'];
                }

                if($email_field == 1)
                {
                    $order->email =  $order_details['email'];
                }

                $order->phone = $order_details['phone'];
                $order->address = $order_details['address'];

                if($live_address_field == 1)
                {
                    $order->latitude = $order_details['latitude'];
                    $order->longitude = $order_details['longitude'];
                }

                if($floor_field == 1)
                {
                    $order->floor = $order_details['floor'];
                }

                if($door_bell_field == 1)
                {
                    $order->door_bell = $order_details['door_bell'];
                }

                if($instructions_field == 1)
                {
                    $order->instructions = $order_details['instructions'];
                }
            }

            $order->save();

            // Insert Order Items
            if($order->id)
            {
                foreach($cart as $cart_data)
                {
                    if(count($cart_data) > 0)
                    {
                        foreach($cart_data as $cart_val)
                        {
                            if(count($cart_val) > 0)
                            {
                                foreach($cart_val as $cart_item)
                                {
                                    $otpions_arr = [];

                                    // Item Details
                                    $item_details = Items::where('id',$cart_item['item_id'])->first();
                                    $item_discount = (isset($item_details['discount'])) ? $item_details['discount'] : 0;
                                    $item_discount_type = (isset($item_details['discount_type'])) ? $item_details['discount_type'] : 'percentage';
                                    $item_name = (isset($item_details[$name_key])) ? $item_details[$name_key] : '';

                                    //Price Details
                                    $price_detail = ItemPrice::where('id',$cart_item['option_id'])->first();
                                    $price_label = (isset($price_detail[$label_key])) ? $price_detail[$label_key] : '';
                                    $item_price = (isset($price_detail['price'])) ? $price_detail['price'] : '';

                                    if($item_discount > 0)
                                    {
                                        if($item_discount_type == 'fixed')
                                        {
                                            $item_price = number_format($item_price - $item_discount,2);
                                        }
                                        else
                                        {
                                            $dis_per = $item_price * $item_discount / 100;
                                            $item_price = number_format($item_price - $dis_per,2);
                                        }
                                    }

                                    if(!empty($price_label))
                                    {
                                        $otpions_arr[] = $price_label;
                                    }


                                    $item_total_amount = $cart_item['total_amount'];
                                    $total_amount_text = $cart_item['total_amount_text'];
                                    $categories_data = (isset($cart_item['categories_data']) && !empty($cart_item['categories_data'])) ? $cart_item['categories_data'] : [];

                                    $subtotal_amount += $item_total_amount;
                                    $total_qty += $cart_item['quantity'];

                                    if(count($categories_data) > 0)
                                    {
                                        foreach($categories_data as $option_id)
                                        {
                                            $my_opt = $option_id;

                                            if(is_array($my_opt))
                                            {
                                                if(count($my_opt) > 0)
                                                {
                                                    foreach ($my_opt as $optid)
                                                    {
                                                        $opt_price_dt = OptionPrice::where('id',$optid)->first();$opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                        $otpions_arr[] = $opt_price_name;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $opt_price_dt = OptionPrice::where('id',$my_opt)->first();
                                                $opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                                $otpions_arr[] = $opt_price_name;
                                            }
                                        }
                                    }

                                    // Order Items
                                    $order_items = new OrderItems();
                                    $order_items->shop_id = $shop_id;
                                    $order_items->order_id = $order->id;
                                    $order_items->item_id = $cart_item['item_id'];
                                    $order_items->item_name = $item_name;
                                    $order_items->item_price = $item_price;
                                    $order_items->item_price_label = $price_label;
                                    $order_items->item_qty = $cart_item['quantity'];
                                    $order_items->sub_total = $item_total_amount;
                                    $order_items->sub_total_text = $total_amount_text;
                                    $order_items->item_price_label = $price_label;
                                    $order_items->options = serialize($otpions_arr);
                                    $order_items->save();
                                }
                            }
                        }
                    }
                }

                $update_order = Order::find($order->id);
                $update_order->order_subtotal = $subtotal_amount;

                $total_amount += $subtotal_amount;

                if($discount_per > 0)
                {
                    if($discount_type == 'fixed')
                    {
                        $discount_amount = $discount_per;
                    }
                    else
                    {
                        $discount_amount = ($subtotal_amount * $discount_per) / 100;
                    }

                    $update_order->discount_per = $discount_per;
                    $update_order->discount_type = $discount_type;
                    $update_order->discount_value = number_format($discount_amount,2);
                    $total_amount = $total_amount - $discount_amount;
                }

                // CGST & SGST
                if($cgst > 0 && $sgst > 0)
                {
                    $gst_per =  $cgst + $sgst;
                    $gst_amount = ( $total_amount * $gst_per) / 100;
                    $update_order->cgst = $cgst;
                    $update_order->sgst = $sgst;
                    $update_order->gst_amount = number_format($gst_amount,2);
                    $total_amount += $gst_amount;
                }

                $total_amount = $total_amount;

                $update_order->order_total = $total_amount;
                $update_order->order_total_text = Currency::currency($currency)->format($total_amount);
                $update_order->total_qty = $total_qty;
                $update_order->update();

                $from_email = (isset($order_details['email'])) ? $order_details['email'] : '';

                if($checkout_type == 'takeaway' || $checkout_type == 'delivery')
                {
                    $order_dt = Order::with(['order_items'])->where('id',$order->id)->first();
                    $order_items = (isset($order_dt->order_items) && count($order_dt->order_items) > 0) ? $order_dt->order_items : [];

                    // Sent Mail to Shop Owner
                    if(count($contact_emails) > 0 && !empty($orders_mail_form_client) && $email_field == 1)
                    {
                        foreach($contact_emails as $mail)
                        {
                            $to = $mail;
                            $subject = "New Order";

                            if($full_name_field == 1)
                            {
                                $fname = (isset($order_details['name'])) ? $order_details['name'] : '';
                                $lname = "";
                            }
                            else
                            {
                                $fname = (isset($order_details['firstname'])) ? $order_details['firstname'] : '';
                                $lname = (isset($order_details['lastname'])) ? $order_details['lastname'] : '';
                            }

                            $message = $orders_mail_form_client;
                            $message = str_replace('{shop_logo}',$shop_logo,$message);
                            $message = str_replace('{shop_name}',$shop_name,$message);
                            $message = str_replace('{firstname}',$fname,$message);
                            $message = str_replace('{lastname}',$lname,$message);
                            $message = str_replace('{order_id}',$order->id,$message);
                            $message = str_replace('{order_type}',$checkout_type,$message);
                            $message = str_replace('{payment_method}',$payment_method,$message);

                            // Order Items
                            $order_html  = "";
                            $order_html .= '<div>';
                                $order_html .= '<table style="width:100%; border:1px solid gray;border-collapse: collapse;">';
                                    $order_html .= '<thead style="background:lightgray; color:white">';
                                        $order_html .= '<tr style="text-transform: uppercase!important;    font-weight: 700!important;">';
                                            $order_html .= '<th style="text-align: left!important;width: 60%;padding:10px">Item</th>';
                                            $order_html .= '<th style="text-align: center!important;padding:10px">Qty.</th>';
                                            $order_html .= '<th style="text-align: right!important;padding:10px">Item Total</th>';
                                        $order_html .= '</tr>';
                                    $order_html .= '</thead>';
                                    $order_html .= '<tbody style="font-weight: 600!important;">';

                                        if(count($order_items) > 0)
                                        {
                                            foreach($order_items as $order_item)
                                            {
                                                $item_dt = itemDetails($order_item['item_id']);
                                                $item_image = (isset($item_dt['image']) && !empty($item_dt['image']) && file_exists('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image'])) ? asset('public/client_uploads/shops/'.$shop_slug.'/items/'.$item_dt['image']) : asset('public/client_images/not-found/no_image_1.jpg');
                                                $options_array = (isset($order_item['options']) && !empty($order_item['options'])) ? unserialize($order_item['options']) : '';
                                                if(count($options_array) > 0)
                                                {
                                                    $options_array = implode(', ',$options_array);
                                                }

                                                $order_html .= '<tr>';

                                                    $order_html .= '<td style="text-align: left!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= '<div style="align-items: center!important;display: flex!important;">';
                                                            $order_html .= '<a style="display: inline-block;
                                                            flex-shrink: 0;position: relative;border-radius: 0.75rem;">';
                                                                $order_html .= '<span style="width: 50px;
                                                                height: 50px;display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                font-weight: 500;background-repeat: no-repeat;
                                                                background-position: center center;
                                                                background-size: cover;
                                                                border-radius: 0.75rem; background-image:url('.$item_image.')"></span>';
                                                            $order_html .= '</a>';
                                                            $order_html .= '<div style="display: block;    margin-left: 3rem!important;">';
                                                                $order_html .= '<a style="font-weight: 700!important;color: #7e8299;
                                                                ">'.$order_item->item_name.'</a>';

                                                                if(!empty($options_array))
                                                                {
                                                                    $order_html .= '<div style="color: #a19e9e;display: block;">'.$options_array.'</div>';
                                                                }
                                                                else
                                                                {
                                                                    $order_html .= '<div style="color: #a19e9e;display: block;"></div>';
                                                                }

                                                            $order_html .= '</div>';
                                                        $order_html .= '</div>';
                                                    $order_html .= '</td>';

                                                    $order_html .= '<td style="text-align: center!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= $order_item['item_qty'];
                                                    $order_html .= '</td>';

                                                    $order_html .= '<td style="text-align: right!important;padding:10px; border-bottom:1px solid gray;">';
                                                        $order_html .= Currency::currency($currency)->format($order_item['sub_total']);
                                                    $order_html .= '</td>';

                                                $order_html .= '</tr>';
                                            }
                                        }

                                    $order_html .= '</tbody>';
                                $order_html .= '</table>';
                            $order_html .= '</div>';
                            $message = str_replace('{items}',$order_html,$message);

                            // Order Total
                            $order_total_html = "";
                            $order_total_html .= '<div>';
                                $order_total_html .= '<table style="width:50%; border:1px solid gray;border-collapse: collapse;">';
                                    $order_total_html .= '<tbody style="font-weight: 700!important;">';
                                        $order_total_html .= '<tr>';
                                            $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Sub Total : </td>';
                                            $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.Currency::currency($currency)->format($order_details->order_subtotal).'</td>';
                                        $order_total_html .= '</tr>';

                                        if($order_details->discount_per > 0)
                                        {
                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">Discount : </td>';
                                                if($order_details->discount_per == 'fixed')
                                                {
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.Currency::currency($currency)->format($order_details->discount_per).'</td>';
                                                }
                                                else
                                                {
                                                    $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">- '.$order_details->discount_per.'%</td>';
                                                }
                                            $order_total_html .= '</tr>';
                                        }

                                        if($order_details->cgst > 0 && $order_details->sgst > 0)
                                        {
                                            $gst_amt = $order_details->cgst + $order_details->sgst;
                                            $gst_amt = $order_details->gst_amount / $gst_amt;

                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('CGST.').' ('.$order_details->cgst.'%)</td>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->cgst * $gst_amt).'</td>';
                                            $order_total_html .= '</tr>';
                                            $order_total_html .= '<tr>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">'.__('SGST.').' ('.$order_details->sgst.'%)</td>';
                                                $order_total_html .= '<td style="padding:10px; border-bottom:1px solid gray">+ '.Currency::currency($currency)->format($order_details->sgst * $gst_amt).'</td>';
                                            $order_total_html .= '</tr>';
                                        }

                                        $order_total_html .= '<tr>';
                                            $order_total_html .= '<td style="padding:10px;">Total : </td>';
                                            $order_total_html .= '<td style="padding:10px;">';
                                                $order_total_html .= Currency::currency($currency)->format($order_details->order_total);
                                            $order_total_html .= '</td>';
                                        $order_total_html .= '</tr>';

                                    $order_total_html .= '</tbody>';
                                $order_total_html .= '</table>';
                            $order_total_html .= '</div>';
                            $message = str_replace('{total}',$order_total_html,$message);

                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                            // More headers
                            $headers .= 'From: <'.$from_email.'>' . "\r\n";

                            mail($to,$subject,$message,$headers);

                        }
                    }
                }
            }

            session()->forget('cart');
            session()->forget('order_details');
            session()->forget('paypal_payment_id');
            session()->forget('discount_per');
            session()->forget('discount_type');
            session()->forget('cust_lat');
            session()->forget('cust_long');
            session()->forget('cust_address');
            session()->save();

            return redirect()->route('shop.checkout.success',[$shop_slug,encrypt($order->id)]);
        }

        return redirect()->route('paypal.payment.cancel',$shop_slug);
    }
}
