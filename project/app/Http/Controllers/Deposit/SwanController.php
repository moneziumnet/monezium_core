<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\DepositBank;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\Admin;
use App\Models\SubInsBank;
use App\Models\BankPoolAccount;
use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class SwanController extends Controller
{
    public function store(Request $request){
        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        $currency = Currency::whereId($request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        if ($currency->code != 'EUR'){
            return redirect()->back()->with(array('warning' => 'Sorry, Currently this Bank API only supports for EUR.'));
        }
        try {
              $options = [
                'multipart' => [
                  [
                    'name' => 'client_id',
                    'contents' => $bankgateway->information->client_id
                  ],
                  [
                    'name' => 'client_secret',
                    'contents' => $bankgateway->information->client_secret
                  ],
                  [
                    'name' => 'grant_type',
                    'contents' => 'client_credentials'
                  ]
              ]];
            $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
            $res_body = json_decode($response->getBody());
            $access_token = $res_body->access_token;
            Session::put('Swan_token', $access_token);
            Session::put('subbank', $bankgateway->subbank_id);
            Session::put('currency', $currency->id);
            Session::put('user_id', $user->id);
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => $th->getMessage()));
        }

        try {
            $redirect_url = url()->previous() == url('/user/dashboard') ? url('/user/dashboard') : url('/admin/dashboard');
            if(!isset($user->company_name)) {
                $country = Country::findOrFail($user->country);
                $body = '{"query":"mutation MyMutation($input: OnboardIndividualAccountHolderInput) {\\n  onboardIndividualAccountHolder(\\n    input: $input\\n  ) \\n  {\\n    ... on OnboardIndividualAccountHolderSuccessPayload {\\n      __typename\\n      onboarding {\\n        id\\n        onboardingUrl\\n        redirectUrl\\n      }\\n    }\\n    ... on ForbiddenRejection {\\n      __typename\\n      message\\n    }\\n  }\\n}\\n","variables":{"input":{"email":"'.$user->email.'","redirectUrl": "'.$redirect_url.'"}}}';
            }
            else {
                $country = Country::findOrFail($user->company_country);
                $body = '{"query":"mutation MyMutation($input: OnboardCompanyAccountHolderInput) {\\n  onboardCompanyAccountHolder(\\n    input: $input\\n  ) {\\n    ... on OnboardCompanyAccountHolderSuccessPayload {\\n      __typename\\n      onboarding {\\n        onboardingUrl\\n      }\\n    }\\n    ... on BadRequestRejection {\\n      __typename\\n      message\\n    }\\n    ... on ForbiddenRejection {\\n      __typename\\n      message\\n    }\\n    ... on ValidationRejection {\\n      __typename\\n      message\\n    }\\n  }\\n}","variables":{"input":{"email":"'.$user->email.'","redirectUrl": "'.$redirect_url.'"}}}';
            }
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
                'Content-Type' => 'application/json'
              ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());
            return redirect()->away($res_body->data->onboardIndividualAccountHolder->onboarding->onboardingUrl);

        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => $th->getMessage()));
        }
    }
}
