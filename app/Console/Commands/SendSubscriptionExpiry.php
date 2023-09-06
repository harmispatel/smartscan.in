<?php

namespace App\Console\Commands;

use App\Models\UsersSubscriptions;
use Illuminate\Console\Command;

class SendSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendmail:before-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Mail to Client Before Expire his Subscription.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users_subscriptions = UsersSubscriptions::with(['user'])->get();
        $admin_settings = getAdminSettings();
        $subscription_expire_mail = (isset($admin_settings['subscription_expire_mail'])) ? $admin_settings['subscription_expire_mail'] : '';
        $days_for_send_first_expiry_mail = (isset($admin_settings['days_for_send_first_expiry_mail'])) ? $admin_settings['days_for_send_first_expiry_mail'] : 15;

        // Contact US emails
        $contact_us_email = (isset($admin_settings['contact_us_email']) && !empty($admin_settings['contact_us_email'])) ? unserialize($admin_settings['contact_us_email']) : [];
        if(count($contact_us_email) > 0)
        {
            $contact_us_email = $contact_us_email[0];
        }
        else
        {
            $contact_us_email = '';
        }

        // Subscription Expiry Mails
        $subscription_expiry_mails = (isset($admin_settings['subscription_expiry_mails']) && !empty($admin_settings['subscription_expiry_mails'])) ? unserialize($admin_settings['subscription_expiry_mails']) : [];

        if(count($users_subscriptions) > 0)
        {
            foreach($users_subscriptions as $user_subscription)
            {
                $remaining_days =  (isset($user_subscription['end_date'])) ? \Carbon\Carbon::now()->diffInDays($user_subscription['end_date'], false) : '';
                $end_date = (isset($user_subscription['end_date'])) ? date('d-m-Y h:i:s A',strtotime($user_subscription['end_date'])) : '';

                if(($remaining_days == $days_for_send_first_expiry_mail) || ($remaining_days == $days_for_send_first_expiry_mail))
                {
                    $user_mail = (isset($user_subscription->user->email)) ? $user_subscription->user->email : '';
                    $user_shop = (isset($user_subscription->user->hasOneShop->shop)) ? $user_subscription->user->hasOneShop->shop : '';
                    $shop_logo = (isset($user_shop['logo'])) ? $user_shop['logo'] : '';
                    $shop_logo = '<img src="'.$shop_logo.'" width="100">';
                    $shop_name = (isset($user_shop['name'])) ? $user_shop['name'] : '';
                    $shop_url = (isset($user_shop['shop_slug'])) ? $user_shop['shop_slug'] : '';
                    $shop_url = asset($shop_url);
                    $shop_name = '<a href="'.$shop_url.'">'.$shop_name.'</a>';

                    // Sent Mail to Client
                    if(!empty($contact_us_email) && !empty($user_mail))
                    {
                        $to = $user_mail;
                        $subject = "Subscription near to Expire";
                        $message = $subscription_expire_mail;
                        $message = str_replace('{shop_logo}',$shop_logo,$message);
                        $message = str_replace('{shop_name}',$shop_name,$message);
                        $message = str_replace('{expiry_date}',$end_date,$message);

                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                        // More headers
                        $headers .= 'From: <'.$contact_us_email.'>' . "\r\n";

                        mail($to,$subject,$message,$headers);
                    }


                    // Sent Mail to Admins
                    if(count($subscription_expiry_mails) > 0)
                    {
                        foreach($subscription_expiry_mails as $sub_ex_mail)
                        {
                            $to = $sub_ex_mail;
                            $subject = "Client Subscription Near to Expire";

                            $message = $subscription_expire_mail;
                            $message = str_replace('{shop_logo}',$shop_logo,$message);
                            $message = str_replace('{shop_name}',$shop_name,$message);
                            $message = str_replace('{expiry_date}',$end_date,$message);

                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                            // More headers
                            $headers .= 'From: <'.$contact_us_email.'>' . "\r\n";

                            mail($to,$subject,$message,$headers);
                        }
                    }
                }
            }
        }
    }
}
