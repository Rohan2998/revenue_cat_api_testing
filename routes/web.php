<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    $payment = DB::table('payment_table')
        ->select('payment_table.id', 'payment_table.current_payment_id', 'payment_table.subscription_type')->paginate(7);

    $data = array();

    foreach ($payment as $key => $value) 
    {
        $subscriber_id = $value->current_payment_id;

        if (strpos($subscriber_id, 'sub_') !== false) 
        {
            $razorpay_url = 'https://api.razorpay.com/v1/subscriptions/'.$value->current_payment_id;
            $razorpay = Http::withBasicAuth('rzp_test_ZzgQgd8tBbtp3V', 'Ukof9MNl9nRXK42TY3OrJRil')->get($razorpay_url);
                
            if($razorpay->status() == 200)
            {
                
                $subscriber = array();
                $razorpay_data = $razorpay->json();

                $subscriber['payment_gateway'] = "Razorpay";

                $subscriber['id'] = $razorpay_data["id"];

                if ($razorpay_data["plan_id"] == "plan_GYG3vdl3f3K0eG") 
                {
                    $subscriber['plan'] = "Pro subscription";    

                } 
                else if($razorpay_data["plan_id"] == "plan_GYG5IAP0rjn8J7") 
                {
                    $subscriber['plan'] = "Premium subscription";    
                }
                else
                {
                    $subscriber['plan'] = "Other";    
                }
                
                
                $subscriber['status'] = $razorpay_data["status"];

                $subscriber['subscription_start'] = gmdate("d M Y", $razorpay_data["current_start"]);

                $subscriber['subscription_end'] = gmdate("d M Y", $razorpay_data["current_end"]);

                $data[$key] = $subscriber;
                // print_r($razorpay_data["status"]);
                    
            }
            else
            {
                echo "Not Found";
            }
        }
        else
        {
            $url = 'https://api.revenuecat.com/v1/subscribers/'.$value->current_payment_id;

            $google = Http::withToken('sk_akuhUvqzXUjJKzSzzbwuaQnvtjkyZ')->get($url,['Content-Type: application/json']);
        
            if($google->status() == 200){

                $subscriber = array();

                $google_data = $google->json();

                $google_entitlements = $google_data["subscriber"]["entitlements"];

                $google_plan = "";
                
                $expires_date = "";

                $purchase_date = "";




                if (array_key_exists("ant_premium",$google_entitlements)) 
                {
                    $google_plan = $google_entitlements["ant_premium"]["product_identifier"];

                    $expires_date = $google_entitlements["ant_premium"]["expires_date"];

                    $purchase_date = $google_entitlements["ant_premium"]["purchase_date"];
                }
                else if(array_key_exists("ant_premium_yearly",$google_entitlements))
                {
                    $google_plan = $google_entitlements["ant_premium_yearly"]["product_identifier"];

                    $expires_date = $google_entitlements["ant_premium_yearly"]["expires_date"];

                    $purchase_date = $google_entitlements["ant_premium_yearly"]["purchase_date"];
                }
                else if(array_key_exists("ant_pro",$google_entitlements))
                {
                    $google_plan = $google_entitlements["ant_pro"]["product_identifier"];

                    $expires_date = $google_entitlements["ant_pro"]["expires_date"];

                    $purchase_date = $google_entitlements["ant_pro"]["purchase_date"];
                }
                



                $subscriber['payment_gateway'] = "Google In-App";

                $subscriber['id'] = $google_data["subscriber"]['original_app_user_id'];

                $subscriber['plan'] = $google_plan;

                $format = "d M Y"; //or something else that date() accepts as a format

                
                
                $today = date('d M Y');

                $expires = date_format(date_create($expires_date), $format);

                $purchase = date_format(date_create($purchase_date), $format);


                if ($today > $expires) 
                {
                    $subscriber['status'] = "expired";    
                } 
                else if ($purchase < $today && $today < $expires) {
                    $subscriber['status'] = "active";    
                }
                {
                    $subscriber['status'] = "expired";    
                }
                


                
                
                // date_format(date_create($time), $format);

                $subscriber['subscription_start'] = date_format(date_create($purchase_date), $format);

                $subscriber['subscription_end'] = date_format(date_create($expires_date), $format);

                $data[$key] = $subscriber;
                
                
                
            }
            else
            {
                echo "Not Found";
            }

        }
        
    }
    // print_r($data);

    
    return view("welcome", compact("data"));
    
    
    
    
    
});
