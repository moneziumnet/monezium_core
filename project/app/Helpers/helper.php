<?php

use App\Classes\MoneziumMailer;
use App\Models\Admin;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use App\Models\MerchantWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActionNotification;
use App\Models\UserWhatsapp;
use App\Models\UserTelegram;
use App\Models\PlanDetail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;


use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Support\Carbon as Carbontime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

if (!function_exists('getModule')) {
    function getModule($value)
    {
        if(auth()->guard('admin')->user()->role == 'admin') {
            $admin = tenancy()->central(function ($tenant) {
            if ($tenant) {
                return Admin::where('tenant_id', $tenant->id)->first();
                } else {
                    return auth()->guard('admin')->user();
                }
    
            });
        }
        elseif(auth()->guard('admin')->user()->role == 'staff') {
            $admin = auth()->guard('admin')->user();
        }
        $sections = explode(" , ", $admin->section);
        if (in_array($value, $sections)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('getUserModule')) {
    function isEnabledUserModule($module)
    {
        $sections = explode(" , ", auth()->user()->modules);
        if (in_array($module, $sections)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('showPrice')) {

    function showPrice($price, $currency)
    {
        $gs = Generalsetting::first();

        $price = round(($price) * getRate($currency), 2);

        if ($gs->currency_format == 0) {
            return $currency->symbol . numFormat($price, 2);
        } else {
            return numFormat($price, 2) . $currency->symbol;
        }
    }
}

if (!function_exists('getRate')) {
    function getRate($to_currency, $from_code = null)
    {
        $defaultCode = Currency::where('is_default', '=', 1)->first()->code;
        $from_code = $from_code ?? $defaultCode;
        $from_cur = Currency::where('code', $from_code)->first();
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=USD');
        $rate = json_decode($response->getBody());
        $to_code = $to_currency->code;
        $to_rate = $rate->data->rates->$to_code ?? $to_currency->rate;
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $from_code);
            $rate = json_decode($response->getBody());
            $code = $to_currency->code;
        } catch (\Exception $e) {
            return $to_rate / $from_cur->rate;
        }

        if (isset($rate->data->rates->$code)) {
            $from_rate = $rate->data->rates->$code;
        } else {
            $from_rate = $to_rate / $from_cur->rate;
        }
        return $from_rate;
    }
}

if (!function_exists('admin')) {
    function admin()
    {
        return auth()->guard('admin')->user();
    }
}

function getPhoto($filename)
{
    if ($filename) {
        if (file_exists('assets/images' . '/' . $filename)) {
            return asset('assets/images/' . $filename);
        } else {
            return asset('assets/images/default.png');
        }

    } else {
        return asset('assets/images/default.png');
    }
}

function loginIp()
{
    $info = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $_SERVER['REMOTE_ADDR']));
    return json_decode(json_encode($info));
}

if (!function_exists('convertedPrice')) {
    function convertedPrice($price, $currency)
    {
        return $price = $price * $currency->rate;
    }
}

if (!function_exists('defaultCurr')) {
    function defaultCurr()
    {
        return Currency::where('is_default', '=', 1)->first()->id;
    }
}

if (!function_exists('charge')) {
    function charge($slug)
    {
        $charge = Charge::where('slug', $slug)->first();
        return $charge->data;
    }
}

if (!function_exists('chargeCalc')) {

    function chargeCalc($charge, $amount, $rate = 1)
    {
        return ($charge->fixed_charge * $rate) + ($amount * ($charge->percent_charge / 100));
    }
}

if (!function_exists('numFormat')) {

    function numFormat($amount, $length = 0)
    {
        if (0 < $length) {
            return number_format($amount + 0, $length);
        }

        return $amount + 0;
    }
}

if (!function_exists('amount')) {

    function amount($amount, $type = 1, $length = 2)
    {
        if ($type == 2) {
            return numFormat($amount, 8);
        } else {
            return numFormat($amount, $length);
        }

    }
}

if (!function_exists('dateFormat')) {

    function dateFormat($date, $format = 'd M Y -- h:i a')
    {
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('randNum')) {

    function randNum($digits = 6)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
}

if (!function_exists('str_rand')) {

    function str_rand($length = 12, $up = false)
    {
        if ($up) {
            return Str::random($length);
        } else {
            return strtoupper(Str::random($length));
        }

    }
}

if (!function_exists('sendMail')) {

    function sendMail($to, $subject, $msg, $headers, $attach = null)
    {
        $gs = Generalsetting::first();
        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $msg,
                'attach' => $attach,
            ];
            $mailer = new MoneziumMailer();
            $mailer->sendCustomMail($data);
        } else {
            mail($to, $subject, $msg, $headers);
        }

    }
}

if (!function_exists('diffTime')) {
    function diffTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }
}

if (!function_exists('access')) {
    function access($permission)
    {
        return admin()->can($permission);
    }
}

if (!function_exists('mailSend')) {
    function mailSend($type, array $data, $user)
    {
        $gs = Generalsetting::first();
        $template = EmailTemplate::where('email_type', $type)->first();


        $message = str_replace('{name}', $user->name, $template->email_body);

        foreach ($data as $key => $value) {
            $message = str_replace("{" . $key . "}", $value, $message);
        }

        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $user->email,
                'subject' => ucwords(str_replace('_', ' ', $type)),
                'body' => $message,
            ];
            $mailer = new MoneziumMailer();
            $mailer->sendCustomMail($data);
        } else {

            $headers = "From: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "Reply-To: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            mail($user->email, $template->email_subject, $message, $headers);
        }

        if ($gs->sms_notify) {
            $message = str_replace('{name}', $user->name, $template->sms);
            foreach ($data as $key => $value) {
                $message = str_replace("{" . $key . "}", $value, $message);
            }
            sendSMS($user->phone, $message, $gs->contact_no);
        }

    }
}

if (!function_exists('sendSMS')) {
    function sendSMS($recipient, $message, $from)
    {
        try {
            // Update Nexmo
            nexmo($recipient, $message, $from);
        } catch (\Throwable $th) {

        }

    }

}

if (!function_exists('nexmo')) {
    function nexmo(string $recipient, $message, $from)
    {
        $gs = Generalsetting::first();
        // Update Nexmo
        $config = array('api_key' => $gs->nexmo_key, 'api_secret' => $gs->nexmo_secret);
        $basic = new \Vonage\Client\Credentials\Basic($config['api_key'], $config['api_secret']);
        $client = new \Vonage\Client($basic);
        $client->sms()->send(
            new \Vonage\SMS\Message\SMS($recipient, $from, $message)
        );

    }
}

if (!function_exists('userBalance')) {
    function userBalance($user_id, $rate = null)
    {
        $wallets = Wallet::where('user_id', $user_id)->with('currency')->get();
        if($rate == null) {
            $currency_id = defaultCurr();
            $code = Currency::findOrFail($currency_id)->code;
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $code);
            $rate = json_decode($response->getBody());
        }
        $totalbalance = 0;
        foreach ($wallets as $key => $wallet) {
            if ($wallet && $wallet->currency) {
                $code = $wallet->currency->code;
                $totalbalance = $totalbalance + $wallet->balance / ($rate->data->rates->$code ?? $wallet->currency->rate);
            }
        }
        return $totalbalance;
        //return admin()->can($permission);
    }
}

if (!function_exists('menu')) {
    function menu($route)
    {
        if (is_array($route)) {
            foreach ($route as $value) {
                if (request()->routeIs($value)) {
                    return 'active';
                }
            }
        } elseif (request()->routeIs($route)) {
            return 'active';
        }
    }
}

if (!function_exists('generateQR')) {
    function generateQR($data)
    {
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$data&choe=UTF-8";
    }
}

if (!function_exists('filter')) {
    function filter($key, $value)
    {
        $queries = request()->query();
        if (count($queries) > 0) {
            $delimeter = '&';
        } else {
            $delimeter = '?';
        }

        if (request()->has($key)) {
            $url = request()->getRequestUri();
            $pattern = "\?$key";
            $match = preg_match("/$pattern/", $url);
            if ($match != 0) {
                return preg_replace('~(\?|&)' . $key . '[^&]*~', "\?$key=$value", $url);
            }

            $filteredURL = preg_replace('~(\?|&)' . $key . '[^&]*~', '', $url);
            return $filteredURL . $delimeter . "$key=$value";
        }
        return request()->getRequestUri() . $delimeter . "$key=$value";

    }
}

if (!function_exists('user_wallet_balance')) {
    function user_wallet_balance($auth_id, $currency_id, $wallet_type = null)
    {
        if (!$wallet_type) {
            $wallet_type = 1;
        }
        $balance = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)->where('currency_id', $currency_id)->first();
        return $balance ? $balance->balance : 0;
    }
}

if (!function_exists('user_wallet_decrement')) {
    function user_wallet_decrement($auth_id, $currency_id, $amount, $wallet_type = null)
    {
        if (!$wallet_type) {
            $wallet_type = 1;
        }
        $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)
            ->where('currency_id', $currency_id)->first();

        if ($wallet) {
            Wallet::where('user_id', $auth_id)
                ->where('currency_id', $currency_id)
                ->where('wallet_type', $wallet_type)
                ->decrement('balance', $amount);
        }
        return $wallet;
    }
}

if (!function_exists('user_wallet_increment')) {
    function user_wallet_increment($auth_id, $currency_id, $amount, $wallet_type = null)
    {
        if (!$wallet_type) {
            $wallet_type = 1;
        }
        $gs = Generalsetting::first();
        $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)
            ->where('currency_id', $currency_id)->first();
        $currency = Currency::findOrFail($currency_id);

        if (!$wallet) {
            if ($currency->type == 2) {
                if ($currency->code == 'ETH') {
                    $keyword = str_rand(6);
                    $address = RPC_ETH('personal_newAccount', [$keyword]);
                    if ($address == 'error') {
                        return null;
                    }
                } elseif ($currency->code == 'BTC') {
                    $key = str_rand();
                    $address = RPC_BTC_Create('createwallet', [$key]);
                    if ($address == 'error') {
                        return null;
                    }
                    $keyword = $key;
                }
                elseif ($currency->code == 'TRON') {
                    $addressData = RPC_TRON_Create();
                    if ($addressData == 'error') {
                        return null;
                    }
                    $address = $addressData->address;
                    $keyword = $addressData->privateKey;
                } 
                elseif($currency->code == 'USDT(TRON)') {
                    {
                        $tron_currency = Currency::where('code', 'TRON')->first();
                        $tron_wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)->where('currency_id', $tron_currency->id)->first();
                        if (!$tron_wallet) {
                            
                            $addressData = RPC_TRON_Create();
                            if ($addressData == 'error') {
                                return null;
                            }
                            $address = $addressData->address;
                            $keyword = $addressData->privateKey;

                            $user_wallet = new Wallet();
                            $user_wallet->user_id = $auth_id;
                            $user_wallet->user_type = 1;
                            $user_wallet->currency_id = $tron_currency->id;
                            $user_wallet->balance = 0;
                            $user_wallet->wallet_type = $wallet_type;
                            $user_wallet->wallet_no = $address;
                            $user_wallet->keyword = $keyword;
                            $user_wallet->created_at = date('Y-m-d H:i:s');
                            $user_wallet->updated_at = date('Y-m-d H:i:s');
                            $user_wallet->save();
                        } else {
                            $address = $tron_wallet->wallet_no;
                            $keyword = $tron_wallet->keyword;
                        }
                    }
                }
                else {
                    $eth_currency = Currency::where('code', 'ETH')->first();
                    $eth_wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)->where('currency_id', $eth_currency->id)->first();
                    if (!$eth_wallet) {
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount', [$keyword]);
                        if ($address == 'error') {
                            return null;
                        }
                        $user_wallet = new Wallet();
                        $user_wallet->user_id = $auth_id;
                        $user_wallet->user_type = 1;
                        $user_wallet->currency_id = $eth_currency->id;
                        $user_wallet->balance = 0;
                        $user_wallet->wallet_type = $wallet_type;
                        $user_wallet->wallet_no = $address;
                        $user_wallet->keyword = $keyword;
                        $user_wallet->created_at = date('Y-m-d H:i:s');
                        $user_wallet->updated_at = date('Y-m-d H:i:s');
                        $user_wallet->save();
                    } else {
                        $address = $eth_wallet->wallet_no;
                        $keyword = $eth_wallet->keyword;
                    }
                }
            } else {
                $address = $gs->wallet_no_prefix . date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            $user_wallet = new Wallet();
            $user_wallet->user_id = $auth_id;
            $user_wallet->user_type = 1;
            $user_wallet->currency_id = $currency_id;
            $user_wallet->balance = $amount;
            $user_wallet->wallet_type = $wallet_type;
            $user_wallet->wallet_no = $address;
            $user_wallet->keyword = $keyword;
            $user_wallet->created_at = date('Y-m-d H:i:s');
            $user_wallet->updated_at = date('Y-m-d H:i:s');
            $user_wallet->save();

            if ($wallet_type != 9) {
                $user = User::findOrFail($auth_id);
                $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');

                if ($wallet_type == 2) {
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if (!$chargefee) {
                        $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $auth_id;
                    $trans->user_type = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount = 0;
                    $trans_wallet = get_wallet($auth_id, defaultCurr(), 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge = $chargefee->data->fixed_charge;
                    $trans->type = '-';
                    $trans->remark = 'card-issuance';
                    $trans->details = trans('Card Issuance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                    $trans->save();
                    $def_cur = Currency::findOrFail(defaultCurr());
                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'def_curr' => $def_cur->code,'type'=>$wallet_type_list[$wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                    send_notification($auth_id, 'New '.$wallet_type_list[$wallet_type].' Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_cur->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $auth_id));

                } else {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if (!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $auth_id;
                    $trans->user_type = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount = 0;
                    $trans_wallet = get_wallet($auth_id, defaultCurr(), 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge = $chargefee->data->fixed_charge;
                    $trans->type = '-';
                    $trans->remark = 'account-open';
                    $trans->details = trans('Wallet Create');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                    $trans->save();
                    $def_cur = Currency::findOrFail(defaultCurr());
                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'def_curr' => $def_cur->code, 'type'=>$wallet_type_list[$wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                    send_notification($auth_id, 'New '.$wallet_type_list[$wallet_type].' Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_cur->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $auth_id));
                }
                user_wallet_decrement($auth_id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }
            return $user_wallet;
        } else {
            $wallet->balance += $amount;
            $wallet->update();
            return $wallet;
        }

    }
}

if (!function_exists('merchant_shop_wallet_increment')) {
    function merchant_shop_wallet_increment($auth_id, $currency_id, $amount, $shop_id)
    {
        $gs = Generalsetting::first();
        $user = User::findOrFail($auth_id);
        $wallet = MerchantWallet::where('merchant_id', $auth_id)->where('shop_id', $shop_id)
            ->where('currency_id', $currency_id)->first();
        $currency = Currency::findOrFail($currency_id);
        if (!$wallet) {
            if ($currency->type == 2) {
                if ($currency->code == 'ETH') {
                    $keyword = str_rand(6);
                    $address = RPC_ETH('personal_newAccount', [$keyword]);
                    if ($address == 'error') {
                        return false;
                    }
                } elseif ($currency->code == 'BTC') {
                    $key = str_rand();
                    $address = RPC_BTC_Create('createwallet', [$key]);
                    if ($address == 'error') {
                        return false;
                    }
                    $keyword = $key;
                } 
                elseif ($currency->code == 'TRON') {
                    $addressData = RPC_TRON_Create();
                    if ($addressData == 'error') {
                        return false;
                    }
                    $address = $addressData->address;
                    $keyword = $addressData->privateKey;
                }
                elseif($currency->code == 'USDT(TRON)') {
                    {
                        $tron_currency = Currency::where('code', 'TRON')->first();
                        $tron_wallet = MerchantWallet::where('merchant_id', $user->id)->where('shop_id', $shop_id)->where('currency_id', $tron_currency->id)->first();
                        if (!$tron_wallet) {
                            
                            $addressData = RPC_TRON_Create();
                            if ($addressData == 'error') {
                                return false;
                            }
                            $address = $addressData->address;
                            $keyword = $addressData->privateKey;
                            DB::table('merchant_wallets')->insert([
                                'merchant_id' => $auth_id,
                                'currency_id' => $tron_currency->id,
                                'shop_id' => $shop_id,
                                'wallet_no' => $address,
                                'keyword' => $keyword,
                            ]);
                        } else {
                            $address = $tron_wallet->wallet_no;
                            $keyword = $tron_wallet->keyword;
                        }
                    }
                }
                else {
                    $eth_currency = Currency::where('code', 'ETH')->first();
                    $eth_wallet = MerchantWallet::where('merchant_id', $user->id)->where('shop_id', $shop_id)->where('currency_id', $eth_currency->id)->first();
                    if (!$eth_wallet) {
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount', [$keyword]);
                        if ($address == 'error') {
                            return false;
                        }
                        DB::table('merchant_wallets')->insert([
                            'merchant_id' => $auth_id,
                            'currency_id' => $eth_currency->id,
                            'shop_id' => $shop_id,
                            'wallet_no' => $address,
                            'keyword' => $keyword,
                        ]);
                    } else {
                        $address = $eth_wallet->wallet_no;
                        $keyword = $eth_wallet->keyword;
                    }
                }
            } else {
                $address = $gs->wallet_no_prefix . date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            $shop_wallet = new MerchantWallet();
            $shop_wallet->merchant_id = $auth_id;
            $shop_wallet->currency_id = $currency_id;
            $shop_wallet->balance = $amount;
            $shop_wallet->shop_id = $shop_id;
            $shop_wallet->wallet_no = $address;
            $shop_wallet->keyword = $keyword;
            $shop_wallet->created_at = date('Y-m-d H:i:s');
            $shop_wallet->updated_at = date('Y-m-d H:i:s');
            $shop_wallet->save();
        } else {
            $wallet->balance += $amount;
            $wallet->update();
            return $wallet->balance;
        }

    }
}

if (!function_exists('merchant_shop_wallet_decrement')) {
    function merchant_shop_wallet_decrement($auth_id, $currency_id, $amount, $shop_id)
    {
        $wallet = MerchantWallet::where('merchant_id', $auth_id)->where('shop_id', $shop_id)
            ->where('currency_id', $currency_id)->first();
        if ($wallet) {
            $balance = MerchantWallet::where('merchant_id', $auth_id)
                ->where('shop_id', $shop_id)
                ->where('currency_id', $currency_id)
                ->decrement('balance', $amount);
            return $balance;
        }

    }
}

if (!function_exists('check_user_type')) {
    function check_user_type($type_id)
    {
        $explode = explode(',', auth()->user()->user_type);

        if (in_array($type_id, $explode)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('check_user_type_by_id')) {
    function check_user_type_by_id($type_id, $user_id)
    {
        $user = User::findOrFail($user_id);
        if (!$user) {
            return false;
        }
        $explode = explode(',', $user->user_type);

        if (in_array($type_id, $explode)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('check_custom_transaction_fee')) {
    function check_custom_transaction_fee($amount, $user, $slug)
    {
        $transaction_plan = Charge::where('slug', $slug)->where('user_id', $user->id)->where('plan_id', 0)->get();
        $res = null;
        foreach ($transaction_plan as $value) {
            if ($value->data->from <= $amount && $value->data->till >= $amount) {
                $res = $value;
                return $res;
            }
        }
    }
}

if (!function_exists('check_global_transaction_fee')) {
    function check_global_transaction_fee($amount, $user, $slug)
    {
        $res = null;
        $transaction_plan = Charge::where('slug', $slug)->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->get();
        foreach ($transaction_plan as $value) {
            if ($value->data->from <= $amount && $value->data->till >= $amount) {
                $res = $value;
                return $res;
            }
        }
        $transaction_plan = Charge::where('slug', $slug)->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->get();
        foreach ($transaction_plan as $value) {
            if ($value->data->from <= $amount && $value->data->till >= $amount) {
                $res = $value;
                return $res;
            }
        }
    }
}
if (!function_exists('wallet_monthly_fee')) {
    function wallet_monthly_fee($user_id)
    {
        $now = Carbontime::now();
        $user = User::findOrFail($user_id);
        $wallets = Wallet::where('user_id', $user->id)->where('wallet_type', 1)->get();
        $gs = Generalsetting::first();
        if ($wallets) {
            if ($user->wallet_maintenance && $now->gt($user->wallet_maintenance)) {
                $user->wallet_maintenance = Carbontime::now()->addDays(30);
                $chargefee = Charge::where('slug', 'account-maintenance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if (!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-maintenance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');

                foreach ($wallets as $key => $value) {
                    user_wallet_decrement($user->id, $value->currency_id, $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, $value->currency_id, $chargefee->data->fixed_charge, 9);
                    # code...
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $user->id;
                    $trans->user_type = 1;
                    $trans->currency_id = $value->currency_id;
                    $trans->amount = 0;
                    $trans->charge = $chargefee->data->fixed_charge;
                    $trans->type = '-';
                    $trans_wallet = get_wallet($user->id, $value->currency_id, 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->remark = 'account-maintenance';
                    $trans->details = trans('Wallet Maintenance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                    $trans->save();
                    $currency = Currency::findOrFail($value->currency_id);
                    mailSend('account_maintenance',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>$wallet_type_list[$value->wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                    send_notification($user->id, $wallet_type_list[$value->wallet_type].' Wallet of '.($user->company_name ?? $user->name).' is maintenanced.'."\n. Maintenance Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-transactions', $user->id));

                    $user->update();
                }

            } elseif ($user->wallet_maintenance == null) {
                $user->wallet_maintenance = Carbontime::now()->addDays(30);
                $user->update();

            }
        }
        $cards = Wallet::where('user_id', $user->id)->where('wallet_type', 2)->get();
        if (count($cards) > 0) {
            if ($user->card_maintenance && $now->gt($user->card_maintenance)) {
                $user->card_maintenance = Carbontime::now()->addDays(30);
                $chargefee = Charge::where('slug', 'card-maintenance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if (!$chargefee) {
                    $chargefee = Charge::where('slug', 'card-maintenance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }
                foreach ($cards as $key => $value) {

                    user_wallet_decrement($user->id, $value->currency_id, $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, $value->currency_id, $chargefee->data->fixed_charge, 9);

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $user->id;
                    $trans->user_type = 1;
                    $trans->currency_id = $value->currency_id;
                    $trans->amount = 0;
                    $trans->charge = $chargefee->data->fixed_charge;
                    $trans->type = '-';
                    $trans_wallet = get_wallet($user->id, $value->currency_id, 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->remark = 'card-maintenance';
                    $trans->details = trans('Card Maintenance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                    $trans->save();
                    $currency = Currency::findOrFail($value->currency_id);

                    mailSend('account_maintenance',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Card', 'date_time'=> dateFormat($trans->created_at)], $user);
                    send_notification($user->id, 'Card Wallet of '.($user->company_name ?? $user->name).' is maintenanced. Please check .'."\n. Maintenance Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-transactions', $user->id));

                    $user->update();
                }

            } elseif ($user->card_maintenance == null) {
                $user->card_maintenance = Carbontime::now()->addDays(30);
                $user->update();

            }
        }
    }
}

if (!function_exists('str_dis')) {
    function str_dis($dis_string)
    {
        if (strlen($dis_string) > 20) {
            return substr($dis_string, 0, 20) . '  ...';
        } else {
            return $dis_string;
        }
    }
}

///////////////////////////////////////////////Crypto RPC Function////////////////////////////////////////////////////////////////
if (!function_exists('erc20_token_transfer')) {

    function erc20_token_transfer($contract, $from, $to, $amount, $keyword)
    {
        $geth = new EthereumRPC('127.0.0.1', 8545);
        $erc20 = new ERC20($geth);
        $token = $erc20->token($contract);

        // First argument is payee/recipient of this transfer
        // Second argument is the amount of tokens that will be sent
        $data = $token->encodedTransferData($to, $amount);

        $transaction = $geth->personal()->transaction($from, $contract) // from $payer to $contract address
            ->amount("0") // Amount should be ZERO
            ->data($data); // Our encoded ERC20 token transfer data from previous step


        // Send transaction with ETH account passphrase
        try {
            $txId = $transaction->send($keyword); // Replace "secret" with actual passphrase of SENDER's ethereum account
            return json_encode(['code' => '0', 'message' => $txId]);
        } catch (\Throwable $th) {
            return json_encode(['code' => '1', 'message' => ' ' . $th->getMessage()]);
        }
    }
}

if (!function_exists('RPC_ETH')) {
    function RPC_ETH($method, $args, $link = 'localhost:8545')
    {
        $args = json_encode($args);
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": ' . $args . ',
            "id": 1,
            "jsonrpc": "2.0"
            }';
        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => $body, 'connect_timeout' => 0.5]);
            $res = json_decode($response->getBody());
            return $res->result;
        } catch (\Throwable $th) {
            return 'error';
        }
    }
}
if (!function_exists('RPC_ETH_Send')) {
    function RPC_ETH_Send($method, $args, $keyword, $link = 'localhost:8545')
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": [' . $args . ',"' . $keyword . '"],
            "id": 1,
            "jsonrpc": "2.0"
            }';
        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());
            return $res->result;
        } catch (\Throwable $th) {
            return 'error';
        }
    }
}

if (!function_exists('RPC_BTC_Create')) {
    function RPC_BTC_Create($method, $args, $link = 'http://localhost:18443')
    {
        $args = json_encode($args);
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGV2b3BzOjEyMzEyMw==',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": ' . $args . ',
            "id": 1,
            "jsonrpc": "2.0"
            }';
        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());
            $wallet_name = $res->result->name;
        } catch (\Throwable $th) {
            return 'error';
        }
        try {
            $body = '{
                    "method": "getnewaddress",
                    "params": [],
                    "id": 1,
                    "jsonrpc": "2.0"
                    }';
            $response = $client->request('POST', $link . '/wallet/' . $wallet_name, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());
            $wallet_address = $res->result;
        } catch (\Throwable $th) {
            return 'error';
        }
        return $wallet_address;
    }
}

if (!function_exists('RPC_BTC_Send')) {
    function RPC_BTC_Send($method, $args, $wallet_name, $link = 'http://localhost:18443')
    {
        $args = json_encode($args);
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGV2b3BzOjEyMzEyMw==',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": ' . $args . ',
            "id": 1,
            "jsonrpc": "2.0"
            }';
        // return $body;
        try {
            $response = $client->request('POST', $link . '/wallet/' . $wallet_name, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());

            } catch (RequestException $th) {
            return json_decode($th->getResponse()->getBody());
        }

        $body = '{
                "method": "generatetoaddress",
                "params": [1,"' . json_decode($args, true)[0] . '"],
                "id": 1,
                "jsonrpc": "2.0"
                }';
        try {
            $response = $client->request('POST', $link . '/wallet/' . $wallet_name, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());
        } catch (RequestException $th) {
            return json_decode($th->getResponse()->getBody());
        }
        return json_encode(['code' => '0', 'message' => 'Transaction success!']);
    }
}

if (!function_exists('RPC_BTC_Balance')) {
    function RPC_BTC_Balance($method, $wallet_name, $link = 'http://localhost:18443')
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGV2b3BzOjEyMzEyMw==',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": [],
            "id": 1,
            "jsonrpc": "2.0"
            }';
        try {
            $response = $client->request('POST', $link . '/wallet/' . $wallet_name, ["headers" => $headers, "body" => $body, 'connect_timeout' => 0.5]);
            $res = json_decode($response->getBody());
        } catch (\Throwable $th) {
            return 'error';
        }
        return $res->result;
    }
}
if (!function_exists('RPC_BTC_Check')) {
    function RPC_BTC_Check($method, $wallet_name, $link = 'http://localhost:18443')
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ZGV2b3BzOjEyMzEyMw==',
        ];
        $body = '{
            "method": "' . $method . '",
            "params": [],
            "id": 1,
            "jsonrpc": "2.0"
            }';
        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => $body]);
            $res = json_decode($response->getBody());
        } catch (\Throwable $th) {
            return 'error';
        }
        if ($res->result) {
            $check_flag = "ok";
        } else {
            $check_flag = 'error';
        }

        return $check_flag;
    }
}

if (!function_exists('RPC_TRON_Create')) {
    function RPC_TRON_Create($link = 'https://api.trongrid.io')
    {
        $api = new \Tron\Api(new Client(['base_uri' => $link]));
        try {
            $trxWallet = new \Tron\TRX($api);
            $addressData = $trxWallet->generateAddress();
            return $addressData;
        }
        catch (\Throwable $th) {
            return 'error';
        }
    }
}


if (!function_exists('RPC_TRON_Balance')) {
    function RPC_TRON_Balance($wallet_no, $link = 'https://api.trongrid.io')
    {
        $api = new \Tron\Api(new Client(['base_uri' => $link]));
        try {
            $trxWallet = new \Tron\TRX($api);
            $address = new \Tron\Address($wallet_no);

            $balance = $trxWallet->balance($address);
            return floatval($balance);
        }
        catch (\Throwable $th) {
            return 'error';
        }
    }
}

if (!function_exists('RPC_TRC20_Balance')) {
    function RPC_TRC20_Balance($wallet, $link = 'https://api.trongrid.io')
    {
        $api = new \Tron\Api(new Client(['base_uri' => $link]));
        $config = [
            'contract_address' => $wallet->currency->address,// USDT TRC20
            'decimals' => $wallet->currency->cryptodecimal,
        ];
        try {
            $trc20Wallet = new \Tron\TRC20($api, $config);
            $hexaddress = $trc20Wallet->tron->address2HexString($wallet->wallet_no);
            $address = new \Tron\Address($wallet->wallet_no, '', $hexaddress);
            $balance = $trc20Wallet->balance($address);
            return floatval($balance);
        }
        catch (\Throwable $th) {
            return 'error';
        }
    }
}

if (!function_exists('RPC_TRON_Transfer')) {
    function RPC_TRON_Transfer($fromWallet, $toaddress, $amount, $link = 'https://api.trongrid.io')
    {
        $from = new \Tron\Address($fromWallet->wallet_no, $fromWallet->keyword );
        $to = new \Tron\Address($toaddress);

        $api = new \Tron\Api(new Client(['base_uri' => $link]));
        try {
            $trxWallet = new \Tron\TRX($api);

            $transaction = $trxWallet->transfer($from, $to, $amount);
            return $transaction;
        }
        catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}


if (!function_exists('RPC_TRC20_Transfer')) {
    function RPC_TRC20_Transfer($fromWallet, $toaddress, $amount, $link = 'https://api.trongrid.io')
    {
        $api = new \Tron\Api(new Client(['base_uri' => $link]));
        $config = [
            'contract_address' => $fromWallet->currency->address,// USDT TRC20
            'decimals' => $fromWallet->currency->cryptodecimal,
        ];

        try {
            $trc20Wallet = new \Tron\TRC20($api, $config);
            $fromhexaddress = $trc20Wallet->tron->address2HexString($fromWallet->wallet_no); 
            $from = new \Tron\Address($fromWallet->wallet_no, $fromWallet->keyword, $fromhexaddress );
            $tohexaddress = $trc20Wallet->tron->address2HexString($toaddress);

            $to = new \Tron\Address($toaddress, '', $tohexaddress);

            $transaction = $trc20Wallet->transfer($from, $to, $amount);
            return $transaction;
        }
        catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}

if (!function_exists('Crypto_Balance')) {
    function Crypto_Balance($auth_id, $currency_id)
    {
        $amount = 0;
        if ($auth_id == 0) {
            $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', 9)->where('currency_id', $currency_id)->with('currency')->first();
        } else {
            $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', 8)->where('currency_id', $currency_id)->with('currency')->first();
        }
        if ($wallet) {
            if ($wallet->currency->code == 'BTC') {
                $amount = RPC_BTC_Balance('getbalance', $wallet->keyword);
                if ($amount == 'error') {
                    $amount = 0;
                }

            } else if ($wallet->currency->code == 'ETH') {
                $amount = RPC_ETH('eth_getBalance', [$wallet->wallet_no, "latest"]);
                if ($amount == 'error') {
                    $amount = 0;
                } else {
                    $amount = hexdec($amount) / pow(10, 18);
                }

            } else if ($wallet->currency->code == 'TRON') {
                $amount = RPC_TRON_Balance($wallet->wallet_no);
                if ($amount == 'error') {
                    $amount = 0;
                } 

            }
            else if($wallet->currency->code == 'USDT(TRON)') {
                $amount = RPC_TRC20_Balance($wallet);
                if ($amount == 'error') {
                    $amount = 0;
                }
            } else {
                $geth = new App\Classes\EthereumRpcService();
                $tokenContract = $wallet->currency->address;
                $decimal = $wallet->currency->cryptodecimal;
                $amount = $geth->getTokenBalance($tokenContract, $wallet->wallet_no, $decimal);
                if ($amount == 'error') {
                    $amount = 0;
                }

            }
        } else {
            return 'error';
        }
        return $amount;
    }
}

if (!function_exists('Crypto_Balance_Fiat')) {
    function Crypto_Balance_Fiat($auth_id, $currency_id)
    {
        $amount = Crypto_Balance($auth_id, $currency_id);
        $currency = Currency::findOrFail($currency_id);
        $rate = getRate($currency);
        return $amount/$rate;
    }
}

if (!function_exists('Crypto_Net_Check')) {
    function Crypto_Net_Check($type)
    {
        $amount = 0;
        if ($type == 'BTC') {
            $amount = RPC_BTC_Check('verifychain', []);
            if ($amount == 'error') {
                $amount = 'error';
            }

        } else {
            $amount = RPC_ETH('eth_blockNumber', []);
            if ($amount == 'error') {
                $amount = 'error';
            } else {
                $amount = hexdec($amount) / pow(10, 18);
            }

        }
        return $amount;
    }
}

if (!function_exists('Crypto_Merchant_Balance')) {
    function Crypto_Merchant_Balance($auth_id, $currency_id, $shop_id)
    {
        $wallet = MerchantWallet::where('merchant_id', $auth_id)->where('shop_id', $shop_id)->where('currency_id', $currency_id)->with('currency')->first();
        if ($wallet) {
            if ($wallet->currency->code == 'BTC') {
                $amount = RPC_BTC_Balance('getbalance', $wallet->keyword);
                if ($amount == 'error') {
                    $amount = 0;
                }

            } else if ($wallet->currency->code == 'ETH') {
                $amount = RPC_ETH('eth_getBalance', [$wallet->wallet_no, "latest"]);
                if ($amount == 'error') {
                    $amount = 0;
                } else {
                    $amount = hexdec($amount) / pow(10, 18);
                }

            } else if ($wallet->currency->code == 'TRON') {
                $amount = RPC_TRON_Balance($wallet->wallet_no);
                if ($amount == 'error') {
                    $amount = 0;
                } 
            } 
            else if($wallet->currency->code == 'USDT(TRON)') {
                $amount = RPC_TRC20_Balance($wallet);
                if ($amount == 'error') {
                    $amount = 0;
                }
            }
            else {
                $geth = new App\Classes\EthereumRpcService();
                $tokenContract = $wallet->currency->address;
                $decimal = $wallet->currency->cryptodecimal;
                $amount = $geth->getTokenBalance($tokenContract, $wallet->wallet_no, $decimal);
                if ($amount == 'error') {
                    $amount = 0;
                }

            }
        } else {
            return 'error';
        }
        return $amount;
    }
}

if (!function_exists('Crypto_Transfer')) {
    function Crypto_Transfer($fromWallet, $toaddress, $amount) {
        if($fromWallet->currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$toaddress.'", "value": "0x'.dechex($amount*pow(10,18)).'"}';
            $result = RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
            if ($result == 'error') {
                throw new \Exception($result."ethere");
            }
            return $result;
        }
        else if($fromWallet->currency->code == 'BTC') {
            $res = RPC_BTC_Send('sendtoaddress',[$toaddress, amount($amount, 2)],$fromWallet->keyword);
            if (isset($res->error->message)){
                throw new \Exception($res->error->message."btc");
            }
            return $fromWallet->wallet_no;
        }
        else if ($fromWallet->currency->code == 'TRON') {
            $res = RPC_TRON_Transfer($fromWallet, $toaddress, $amount);
            if(!isset($res->txID)) {
                throw new \Exception($res."tron");
            }
            return $res->txID;

        }
        else if ($fromWallet->currency->code == 'USDT(TRON)') {
            $res = RPC_TRC20_Transfer($fromWallet, $toaddress, $amount);
            if(!isset($res->txID)) {
                throw new \Exception($res."torn.usd");
            }
            $trnx = $res->txID;
            return $trnx;
        }
        else {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $toaddress, $amount,  $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                throw new \Exception(json_decode($result)->message."usdt");
            }
            $trnx = json_decode($result)->message;
            return $trnx;
        }
    }

}

if(!function_exists('Exchange_Transfer')) {
    function Exchange_Transfer($fromWallet, $toWallet, $fromAmount, $toAmount) {
        if($fromWallet->currency->type == 2) {
            $tosystemWallet =  get_wallet(0, $fromWallet->currency_id, 9);
            Crypto_Transfer($fromWallet, $tosystemWallet->wallet_no, $fromAmount);
        }
        if($toWallet->currency->type == 2) {
            $fromsystemWallet = get_wallet(0, $toWallet->currency_id, 9);
            Crypto_Transfer($fromsystemWallet, $toWallet->wallet_no, $finalAmount);
        }
    }
}

if (!function_exists('Get_Wallet_Address')) {
    function Get_Wallet_Address($auth_id, $currency_id)
    {
        $data = Wallet::where('currency_id', $currency_id)->where('wallet_type', 8)->where('user_id', $auth_id)->first();
        if ($data) {
            return $data->wallet_no;
        } else {
            return null;
        }
    }
}

if (!function_exists('get_wallet')) {
    function get_wallet($user_id, $currency_id, $wallet_type = 1)
    {
        $wallet = user_wallet_increment($user_id, $currency_id, 0, $wallet_type);
        return $wallet;
    }
}

if (!function_exists('str2obj')) {
    function str2obj($str)
    {
        $str = preg_replace("/\r|\n/", "", $str);
        $str = str_replace("\\n", "", $str);
        $str = str_replace('\\"', '"', $str);
        return json_decode($str);
    }
}

if (!function_exists('generate_card_number')) {
    function generate_card_number($limit)
    {
        $code = '';
        for ($i = 0; $i < $limit; $i++) {
            $code .= mt_rand(0, 9);
        }
        return $code;
    }
}


if (!function_exists('generate_card')) {
    function generate_card($client_id, $client_secret, $iban, $redirect_url)
    {
        $client = new Client();
        try {
            $options = [
                'multipart' => [
                    [
                        'name' => 'client_id',
                        'contents' => $client_id
                    ],
                    [
                        'name' => 'client_secret',
                        'contents' => $client_secret
                    ],
                    [
                        'name' => 'grant_type',
                        'contents' => 'client_credentials'
                    ]
                ]
            ];
            $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
            $res_body = json_decode($response->getBody());
            $access_token = $res_body->access_token;
        } catch (\Throwable $th) {
            return array('error', json_encode($th->getMessage()));
        }
        try {
            $body = '{"query":"query MyQuery {\\n  accounts {\\n    edges {\\n      node {\\n        BIC\\n        IBAN\\n        memberships {\\n          edges {\\n            node {\\n              email\\n              id\\n            }\\n          }\\n        }\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());
            $accountlist = $res_body->data->accounts->edges;
            if (count($accountlist) > 0) {
                foreach ($accountlist as $key => $value) {
                    if ($value->node->IBAN == $iban) {
                        $membership_id = $value->node->memberships->edges[0]->node->id;
                    }
                }
            }

        } catch (\Throwable $th) {
            return array('error', json_encode($th->getMessage()));
        }
        if (!$membership_id) {
            return array('error', 'The membership id for your swan bank account does not exist');

        }
        try {
            $body = '{"query":"\\nmutation MyMutation {\\n  addCard(\\n    input: {\\n      accountMembershipId: \\"' . $membership_id . '\\"\\n      withdrawal: true\\n      international: true\\n      nonMainCurrencyTransactions: true\\n      eCommerce: true\\n      consentRedirectUrl: \\"' . $redirect_url . '\\"\\n    }\\n  ) {\\n    ... on AddCardSuccessPayload {\\n      __typename\\n      card {\\n        statusInfo {\\n          ... on CardConsentPendingStatusInfo {\\n            __typename\\n            consent {\\n              consentUrl\\n            }\\n          }\\n        }\\n        id\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());
            if (isset($res_body->data, $res_body->data->addCard, $res_body->data->addCard->card)) {
                return array('success', $res_body->data->addCard->card->consentUrl);

            }
            return array('error', "Can't create a Card, becouse this gateway is not on live.");


        } catch (\Throwable $th) {
            return array('error', json_encode($th->getMessage()));
        }
    }
}

if (!function_exists('send_notification')) {
    function send_notification($user_id, $des, $url)
    {
        $notification = new ActionNotification;
        $notification->user_id = $user_id;
        $notification->description = $des;
        $notification->url = $url;
        $notification->save();
    }
}
if (!function_exists('time_elapsed_string')) {

    function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('send_whatsapp')) {
    function send_whatsapp($user_id, $message)
    {
        $whatsapp = UserWhatsapp::where('user_id', $user_id)->first();
        if ($whatsapp && $whatsapp->phonenumber != null) {
            send_message_whatsapp($message, $whatsapp->phonenumber);
            return true;
        }
    }
}

if (!function_exists('send_message_whatsapp')) {
    function send_message_whatsapp($message, $to_number)
    {
        $gs = Generalsetting::first();

        $url = "https://messages-sandbox.nexmo.com/v1/messages";
        $params = [
            "to" => $to_number,
            "from" => $gs->whatsapp_bot_number,
            "text" => $message,
            "channel" => "whatsapp",
            "message_type" => "text"
        ];
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Basic " . base64_encode($gs->nexmo_key . ":" . $gs->nexmo_secret)

        ];
        $client = new Client();
        try {
            $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
            $data = $response->getBody();
            Log::Info($data);
        } catch (\Throwable $th) {
            Log::Info($th->getMessage());
        }
    }
}

if (!function_exists('send_staff_telegram')) {
    function send_staff_telegram($message, $module)
    {
        $telegram_users = UserTelegram::where('chat_id', '!=', NULL)->where('user_id', 0)->where('status', 1)->get();
        $gs = Generalsetting::first();

        foreach ($telegram_users as $key => $telegram) {
            $staff = Admin::findOrFail($telegram->staff_id);
            if ($staff->telegram_section_check($module)) {
                send_message_telegram($message, $telegram->chat_id);
            }
        }
    }
}

if (!function_exists('send_telegram')) {
    function send_telegram($user_id, $message)
    {
        $telegram = UserTelegram::where('user_id', $user_id)->where('status', 1)->first();
        if ($telegram && $telegram->chat_id != null) {
            send_message_telegram($message, $telegram->chat_id);
            return true;
        }
    }
}

if (!function_exists('send_message_telegram')) {
    function send_message_telegram($message, $chat_id)
    {
        $gs = Generalsetting::first();
        $token = $gs->telegram_token;
        $link = 'https://api.telegram.org:443/bot' . $token;

        $params = [
            "chat_id" => $chat_id,
            "text" => $message,
        ];
        $client = new Client();
        try {
            $response = $client->request('GET', $link . '/sendMessage', ["query" => $params]);
            $data = $response->getBody();
            Log::Info($data);
        } catch (\Throwable $th) {
            Log::Info($th->getMessage());
        }
    }
}

if (!function_exists('get_telegram_username')) {
    function get_telegram_username($chat_id)
    {
        $gs = Generalsetting::first();
        $token = $gs->telegram_token;
        $link = 'https://api.telegram.org:443/bot' . $token;

        $client = new Client();
        try {
            $response = $client->request('GET', $link . '/getChat?chat_id='.$chat_id);
            $data = json_decode($response->getBody());
            
            Log::Info($response->getBody());
            return $data->result->username;
        } catch (\Throwable $th) {
            Log::Info($th->getMessage());
        }
    }
}

if (!function_exists('prefix_get_next_key_array')) {

    function prefix_get_next_key_array($arr, $key)
    {
        $keys = array_keys($arr);
        $position = array_search($key, $keys, true);

        if (isset($keys[$position + 1])) {
            $next_key = $keys[$position + 1];
        }

        return $next_key;
    }
}

if (!function_exists('plan_details_by_type')) {

    function plan_details_by_type($type, $plan_id)
    {
        $plandetail = PlanDetail::where('plan_id', $plan_id)->where('type', $type)->first();
        return $plandetail;
    }
}

if (!function_exists('merchant_shop_webhook_send')) {

    function merchant_shop_webhook_send($url, $details)
    {
        $client = new Client();

        try {
            $response = $client->request('POST', $url, ["form_params" => $details]);
            $data = json_decode($response->getBody(), true);
            Log::Info($data);
        } catch (RequestException $th) {
            Log::Info($th->getResponse()->getBody());
        }
    }
}

