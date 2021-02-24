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
        ->select('payment_table.id', 'payment_table.current_payment_id', 'payment_table.subscription_type')->paginate(5);

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

                $subscriber['id'] = $razorpay_data["id"];
                $subscriber['plan'] = $razorpay_data["plan_id"];
                $subscriber['status'] = $razorpay_data["status"];
                $subscriber['subscription_start'] = $razorpay_data["current_start"];
                $subscriber['subscription_end'] = $razorpay_data["current_end"];

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
                $subscriber['google_purchase'] = $google["subscriber"]["entitlements"];
                $data[$key] = $subscriber;
            }
            else
            {
                echo "Not Found";
            }

        }
        
    }
    print_r($data);

    
    
    
    
});
