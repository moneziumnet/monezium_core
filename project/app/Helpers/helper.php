<?php

use App\Classes\GeniusMailer;
use App\Models\Admin;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use App\Models\MerchantWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon as Carbontime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('getModule')) {
    function getModule($value)
    {
        $admin = tenancy()->central(function ($tenant) {
            if ($tenant) {
                return Admin::where('tenant_id', $tenant->id)->first();
            } else {
                return auth()->guard('admin')->user();
            }

        });

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
            return $currency->symbol . $price;
        } else {
            return $price . $currency->symbol;
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
        } catch (\Exception$e) {
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

    function amount($amount, $type = 1, $length = 0)
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

if (!function_exists('email')) {

    function email($data)
    {
        $gs = Generalsetting::first();
        if ($gs->is_smtp == 1) {
            $maildata = [
                'to' => $data['email'],
                'subject' => $data['subject'],
                'body' => $data['message'],
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($maildata);
        } else {
            $headers = "From: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "Reply-To: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";

            mail($data['email'], $data['subject'], $data['message'], $headers);
        }
    }
}
if (!function_exists('sendMail')) {

    function sendMail($to, $subject, $msg, $headers)
    {
        $gs = Generalsetting::first();
        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $msg,
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {
            mail($to,$subject,$msg,$headers);
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
    function mailSend($key, array $data, $user)
    {
        $gs = Generalsetting::first();
        $template = EmailTemplate::where('email_type', $key)->first();

        $message = str_replace('{name}', $user->name, $template->email_body);

        foreach ($data as $key => $value) {
            $message = str_replace("{" . $key . "}", $value, $message);
        }

        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $user->email,
                'subject' => $template->email_subject,
                'body' => $message,
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {

            $headers = "From: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "Reply-To: $gs->sitename <$gs->from_email> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            @mail($user->email, $template->email_subject, $message, $headers);
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
        } catch (\Throwable$th) {

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
    function userBalance($user_id)
    {
        $wallets = Wallet::where('user_id', $user_id)->with('currency')->get();
        $currency_id = defaultCurr();
        $code = Currency::findOrFail($currency_id)->code;
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $code);
        $rate = json_decode($response->getBody());
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
                } else {
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
                    $trans->amount = $chargefee->data->fixed_charge;
                    $trans_wallet = get_wallet($auth_id, defaultCurr(), 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge = 0;
                    $trans->type = '-';
                    $trans->remark = 'card_issuance';
                    $trans->details = trans('Card Issuance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();
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
                    $trans->amount = $chargefee->data->fixed_charge;
                    $trans_wallet = get_wallet($auth_id, defaultCurr(), 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge = 0;
                    $trans->type = '-';
                    $trans->remark = 'wallet_create';
                    $trans->details = trans('Wallet Create');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();
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
                } else {
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
                foreach ($wallets as $key => $value) {
                    user_wallet_decrement($user->id, $value->currency_id, $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, $value->currency_id, $chargefee->data->fixed_charge, 9);
                    # code...
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $user->id;
                    $trans->user_type = 1;
                    $trans->currency_id = $value->currency_id;
                    $trans->amount = $chargefee->data->fixed_charge;
                    $trans->charge = 0;
                    $trans->type = '-';
                    $trans_wallet = get_wallet($user->id, $value->currency_id, 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->remark = 'wallet_monthly_fee';
                    $trans->details = trans('Wallet Maintenance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();

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
                foreach ($wallets as $key => $value) {

                    user_wallet_decrement($user->id, $value->currency_id, $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, $value->currency_id, $chargefee->data->fixed_charge, 9);

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id = $user->id;
                    $trans->user_type = 1;
                    $trans->currency_id = $value->currency_id;
                    $trans->amount = $chargefee->data->fixed_charge;
                    $trans->charge = 0;
                    $trans->type = '-';
                    $trans_wallet = get_wallet($user->id, $value->currency_id, 1);
                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->remark = 'card_monthly_fee';
                    $trans->details = trans('Card Maintenance');
                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();

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
            return substr($dis_string, 0, 15) . '  ...';
        } else {
            return $dis_string;
        }
    }
}

///////////////////////////////////////////////Crypto RPC Function////////////////////////////////////////////////////////////////

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
        } catch (\Throwable$th) {
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
        } catch (\Throwable$th) {
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
        } catch (\Throwable$th) {
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
        } catch (\Throwable$th) {
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
        } catch (\Throwable$th) {
            return 'error';
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
        } catch (\Throwable$th) {
            return 'error';
        }
        return 'success';
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
        } catch (\Throwable$th) {
            return 'error';
        }
        return $res->result;
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

            } else {
                $geth = new App\Classes\EthereumRpcService();
                $tokenContract = $wallet->currency->address;
                $amount = $geth->getTokenBalance($tokenContract, $wallet->wallet_no);
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

            } else {
                $geth = new App\Classes\EthereumRpcService();
                $tokenContract = $wallet->currency->address;
                $amount = $geth->getTokenBalance($tokenContract, $wallet->wallet_no);
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
    function generate_card($client_id, $client_secret, $iban, $redirect_url) {
        $client = New Client();
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
            ]];
          $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
          $res_body = json_decode($response->getBody());
          $access_token = $res_body->access_token;
        } catch (\Throwable $th) {
            return array('error', json_encode($th->getMessage()));
        }
        try {
            $body = '{"query":"query MyQuery {\\n  accounts {\\n    edges {\\n      node {\\n        BIC\\n        IBAN\\n        memberships {\\n          edges {\\n            node {\\n              email\\n              id\\n            }\\n          }\\n        }\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
                'Content-Type' => 'application/json'
            ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());
            $accountlist = $res_body->data->accounts->edges;
            if(count($accountlist) > 0) {
                foreach ($accountlist as $key => $value) {
                    if($value->node->IBAN == $iban) {
                        $membership_id = $value->node->memberships->edges[0]->node->id;
                    }
                }
            }

        } catch (\Throwable $th) {
            return array('error', json_encode($th->getMessage()));
        }
        if(!$membership_id){
            return array('error', 'The membership id for your swan bank account does not exist');

        }
        try {
            $body = '{"query":"\\nmutation MyMutation {\\n  addCard(\\n    input: {\\n      accountMembershipId: \\"'.$membership_id.'\\"\\n      withdrawal: true\\n      international: true\\n      nonMainCurrencyTransactions: true\\n      eCommerce: true\\n      consentRedirectUrl: \\"'.$redirect_url.'\\"\\n    }\\n  ) {\\n    ... on AddCardSuccessPayload {\\n      __typename\\n      card {\\n        statusInfo {\\n          ... on CardConsentPendingStatusInfo {\\n            __typename\\n            consent {\\n              consentUrl\\n            }\\n          }\\n        }\\n        id\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
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
