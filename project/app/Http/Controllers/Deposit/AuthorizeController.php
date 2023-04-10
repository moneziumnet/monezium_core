<?php

namespace App\Http\Controllers\Deposit;

use App\Http\Controllers\Controller;

use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\PlanDetail;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Classes\GoogleAuthenticator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeController extends Controller
{
    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentcheck('Payment Gateway Incoming')) {
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
            else{
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
        }
        $settings = Generalsetting::find(1);

        $authorizeinfo    = PaymentGateway::whereKeyword('authorize.net')->first();
        $authorizesettings= $authorizeinfo->convertAutoData();

        $item_name = $settings->title." Deposit";
        $item_number = Str::random(4).time();
        $item_amount = $request->amount;
        $currency_code = Currency::where('id',$request->currency_id)->first()->code;

        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = Deposit::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = Deposit::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }

        if($dailydeposit > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }

        $validator = Validator::make($request->all(),[
            'cardNumber' => 'required',
            'cardCVC' => 'required',
            'month' => 'required',
            'year' => 'required',
        ]);

        if ($validator->passes()) {
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName($authorizesettings['login_id']);
            $merchantAuthentication->setTransactionKey($authorizesettings['txn_key']);

            $refId = 'ref' . time();

            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber(str_replace(' ','',$request->cardNumber));
            $year = $request->year;
            $month = $request->month;
            $creditCard->setExpirationDate($year.'-'.$month);
            $creditCard->setCardCode($request->cardCVC);

            $paymentOne = new AnetAPI\PaymentType();
            $paymentOne->setCreditCard($creditCard);

            $orderr = new AnetAPI\OrderType();
            $orderr->setInvoiceNumber($item_number);
            $orderr->setDescription($item_name);

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount($item_amount);
            $transactionRequestType->setOrder($orderr);
            $transactionRequestType->setPayment($paymentOne);

            $requestt = new AnetAPI\CreateTransactionRequest();
            $requestt->setMerchantAuthentication($merchantAuthentication);
            $requestt->setRefId($refId);
            $requestt->setTransactionRequest($transactionRequestType);


            $controller = new AnetController\CreateTransactionController($requestt);
            if($authorizesettings['sandbox_check'] == 1){
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
            }
            else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            }


            if ($response != null) {
                if ($response->getMessages()->getResultCode() == "Ok") {
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getMessages() != null) {
                        $deposit = new Deposit();
                        $deposit['deposit_number'] = Str::random(12);
                        $deposit['user_id'] = auth()->user()->id;
                        $deposit['currency_id'] = $request->currency_id;
                        $deposit['amount'] = $request->amount;
                        $deposit['method'] = $request->method;
                        $deposit['status'] = "complete";

                        $deposit->save();


                        $gs =  Generalsetting::findOrFail(1);
                        $user = auth()->user();
                        user_wallet_increment($user->id, $request->currency_id, $request->amount);
                        $trans_wallet = get_wallet($user->id, $request->currency_id, 1);

                        $trans = new Transaction();
                        $trans->trnx = $deposit->deposit_number;
                        $trans->user_id     = $user->id;
                        $trans->user_type   = 1;
                        $trans->currency_id = $deposit->currency_id;
                        $trans->amount      = $deposit->amount;
                        $trans->charge      = 0;
                        $trans->type        = '+';

                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                        $trans->remark      = 'deposit';
                        $trans->details     = trans('Deposit complete');

                        $trans->data        = '{"sender":"Authorize.Net", "receiver":"'.($user->company_name ?? $user->name).'", "description": "Authorize.Net / '.$deposit->deposit_number.'"}';
                        $trans->save();



                        mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency_code, 'trnx' => $deposit->deposit_number ,'date_time'=>$trans->created_at ,'type' => 'Authorize.Net' ], $user);
                        send_notification($user->id, 'Authorize.Net Deposit for '.($user->company_name ?? $user->name).'is approved '."\n Amount is ".$currency->symbol.$deposit->amount."\n Transaction ID : ".$deposit->deposit_number, route('admin.deposits.index'));

                        return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$request->amount.' ('.$currency_code.') successfully!');

                    } else {
                        return redirect()->route('user.deposit.create')->with('unsuccess', 'Payment Failed.');
                    }
                } else {
                    return redirect()->route('user.deposit.create')->with('unsuccess', 'Payment Failed.');
                }
            } else {
                return redirect()->route('user.deposit.create')->with('unsuccess', 'Payment Failed.');
            }

        }
        return redirect()->route('user.deposit.create')->with('unsuccess', 'Invalid Payment Details.');
    }
}
