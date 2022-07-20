<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\OtherBank;
use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class UserOpenPaydController extends Controller
{
    private $url = 'https://sandbox.openpayd.com/api';
    private $auth = 'V0hkZXY6JSp0UDVrMjcrWQ==';
    private $accounter_id = '99a4651a-f65a-434e-85ad-6bf9e445146a';
    private $token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2NvdW50SG9sZGVySWQiOiI5OWE0NjUxYS1mNjVhLTQzNGUtODVhZC02YmY5ZTQ0NTE0NmEiLCJjbGllbnRJZCI6ImE5NjI4YzllLTBiZWUtNGM2NC1hYWM5LTAyNGE1N2JkOTA5OSIsInJlZmVycmFsSWQiOiJiMjEzZDEwMi0wMDg4LTQ2NzMtODMyNS04ZjBjNWExYmZkNjciLCJhY2NvdW50SG9sZGVycyI6eyI5OWE0NjUxYS1mNjVhLTQzNGUtODVhZC02YmY5ZTQ0NTE0NmEiOnsib3duZXIiOiJBcGlVc2VyU3VtbWFyeShmaXJzdE5hbWU9d2gsIGxhc3ROYW1lPWRldikiLCJreWNEZXRhaWxJZCI6ImJkYjVlZjQ1LTk1M2UtNDdlYi04NTQzLTkzODRmZjY2MGU2NCIsImt5Y1N0YXR1cyI6IlVOVkVSSUZJRUQiLCJidXNpbmVzc0RldGFpbCI6IkFwaUJ1c2luZXNzRGV0YWlsU3VtbWFyeShjb21wYW55TmFtZT1EZXZlbG9wIFRlY2ggU29sdXRpb24pIiwiYWNjb3VudEhvbGRlckRpc3BsYXlOYW1lIjoiRGV2ZWxvcCBUZWNoIFNvbHV0aW9uIiwicmVmZXJyYWxJZCI6ImIyMTNkMTAyLTAwODgtNDY3My04MzI1LThmMGM1YTFiZmQ2NyIsImFjY291bnRIb2xkZXJTdGF0dXMiOiJBQ1RJVkUiLCJpZCI6Ijk5YTQ2NTFhLWY2NWEtNDM0ZS04NWFkLTZiZjllNDQ1MTQ2YSIsImFjY291bnRIb2xkZXJUeXBlIjoiQlVTSU5FU1MifX0sImFjY291bnRIb2xkZXJTdGF0dXMiOiJBQ1RJVkUiLCJhdXRob3JpdGllcyI6WyJHRVRfQkFOS19BQ0NPVU5UIiwiVVBEQVRFX0FDQ09VTlQiLCJERUxFVEVfQVBJX0tFWSIsIkNSRUFURV9TVEFORElOR19PUkRFUiIsIkNSRUFURV9BUElfS0VZIiwiREVMRVRFX0JBTktfQkVORUZJQ0lBUlkiLCJHRVRfQkVORUZJQ0lBUlkiLCJERUxFVEVfQ0FSRCIsIkdFVF9BUElfS0VZIiwiREVMRVRFX0RFTEVHQVRFIiwiREVMRVRFX0JBTktfQ0FSRCIsIkdFVF9UT0tFTl9NRU1CRVIiLCJDUkVBVEVfQkFOS19CRU5FRklDSUFSWSIsIkdFVF9DVVNUT01JU0FUSU9OUyIsIlVQREFURV9BUElfS0VZIiwiR0VUX0NBUkRfTElNSVQiLCJVUERBVEVfTUUiLCJHRVRfUEFZSU5fSU5fQ0xJRU5UX1JFVklFVyIsIkdFVF9ERUxJVkVSWV9NRVRIT0QiLCJHRVRfQ0FSRF9GSUxURVIiLCJERUxFVEVfQUNDT1VOVCIsIkNMSUVOVF9BUFBST1ZFX1RSQU5TQUNUSU9OIiwiREVMRVRFX1RPS0VOX0FDQ09VTlRTIiwiVVBEQVRFX1BBWUlOX0lOX0NMSUVOVF9SRVZJRVciLCJHRVRfU1VCX0FDQ09VTlRfSE9MREVSIiwiQ1JFQVRFX0lOVklUQVRJT04iLCJHRVRfRElSRUNUX0RFQklUX01BTkRBVEUiLCJDUkVBVEVfSU5ESVZJRFVBTCIsIkdFVF9LWUNfREVUQUlMIiwiR0VUX1RPUFVQX0lORk8iLCJVUERBVEVfQ0FSRF9MSU1JVCIsIkNSRUFURV9CRU5FRklDSUFSWSIsIkdFVF9IT01FU0NSRUVOX1NUQVRJU1RJQyIsIkdFVF9DQVJEIiwiQ1JFQVRFX0JBTktfQ0FSRCIsIlVQREFURV9JUF9SRVNUUklDVElPTiIsIkdFVF9JUF9SRVNUUklDVElPTiIsIkNSRUFURV9CRU5FRklDSUFSWV9XSVRIRFJBVyIsIlVQREFURV9XRUJIT09LIiwiVVBEQVRFX0NBUkRfRklMVEVSIiwiREVMRVRFX0FDUVVJUklOR19QUk9GSUxFIiwiU0lNVUxBVE9SX09QRVJBVElPTlMiLCJVUERBVEVfU1RBTkRJTkdfT1JERVIiLCJHRVRfQkVORUZJQ0lBUllfQkFOSyIsIkdFVF9NRSIsIkNSRUFURV9LWUNfRE9DVU1FTlQiLCJDUkVBVEVfQ0FSRF9MSU1JVCIsIkdFVF9CQUxBTkNFIiwiQ1JFQVRFX0xJTktFRF9DTElFTlQiLCJHRVRfVFJBTlNBQ1RJT05fUFJFRkVSRU5DRSIsIkdFVF9JTlZJVEFUSU9OIiwiQ1JFQVRFX1RSQU5TQUNUSU9OIiwiR0VUX0JBTktfUEFZSU4iLCJDUkVBVEVfQkFOS19BQ0NPVU5UIiwiVVBEQVRFX0NBU0VfQ09NTUVOVCIsIkFERF9UT1BVUF9JTkZPIiwiUkVDQUxMX1dFQkhPT0siLCJVUERBVEVfQ1VTVE9NSVNBVElPTlMiLCJHRVRfU0FMRVNGT1JDRV9DQVNFIiwiVkFMSURBVEVfSUJBTiIsIlBSRV9WRVJJRklDQVRJT05fVVNFUiIsIkNSRUFURV9TQUxFU0ZPUkNFX0NBU0UiLCJDUkVBVEVfQUNDT1VOVCIsIlVQREFURV9UUkFOU0FDVElPTl9QUkVGRVJFTkNFIiwiQ1JFQVRFX1NXRUVQIiwiR0VUX1JFRkVSUkFMIiwiU0lNVUxBVEVfV0VCSE9PSyIsIkNSRUFURV9JUF9SRVNUUklDVElPTiIsIkNSRUFURV9CQU5LX1BBWU9VVCIsIkdFVF9QSU4iLCJHRVRfUE9QVVBfU0FMRVNGT1JDRV9DQVNFX01FU1NBR0UiLCJHRVRfREVMRUdBVEUiLCJHRVRfU1dFRVAiLCJERUxFVEVfQkVORUZJQ0lBUlkiLCJVUERBVEVfQkFOS19DQVJEIiwiQ1JFQVRFX0NVU1RPTUlTQVRJT05TIiwiQ1JFQVRFX0FDQ09VTlRfVE9LRU4iLCJSRUZVTkRfRElSRUNUX0RFQklUIiwiR0VUX0JBTktfQkVORUZJQ0lBUlkiLCJVUERBVEVfUElOIiwiREVMRVRFX0NBUkRfTElNSVQiLCJHRVRfQVNTSUdORURfQ0FSRF9QUk9GSUxFIiwiR0VUX0JBTktfQkVORUZJQ0lBUllfUkVRVUlSRURfREVUQUlMUyIsIkRFTEVURV9JUF9SRVNUUklDVElPTiIsIkdFVF9XRUJIT09LIiwiVVBEQVRFX0lOVklUQVRJT04iLCJHRVRfVFJBTlNBQ1RJT04iLCJERUxFVEVfV0VCSE9PSyIsIkRFTEVURV9DQVJEX0ZJTFRFUiIsIkdFVF9DVVJSRU5UX0FDQ09VTlRfSE9MREVSIiwiR0VUX0xJTktFRF9DTElFTlQiLCJHRVRfQkFOS19DQVJEIiwiQ09SUE9SQVRFX0FDVElWSVRZX1RZUEUiLCJHRVRfVE9LRU5fQUNDT1VOVFMiLCJHRVRfS1lDX0RPQ1VNRU5UIiwiQ1JFQVRFX1dFQkhPT0siLCJHRVRfQUNDT1VOVF9OT1RFUyIsIkNSRUFURV9CVVNJTkVTUyIsIlVQREFURV9TV0VFUCIsIkNSRUFURV9DQVNFX0NPTU1FTlQiLCJDUkVBVEVfQ0FSRCIsIlVQREFURV9TQUxFU0ZPUkNFX0NBU0UiLCJHRVRfQUNDT1VOVCIsIlBPT0xFRF9BQ1RJVklUWV9UWVBFIiwiVVBEQVRFX01FX1BST0ZJTEUiLCJDUkVBVEVfQkVORUZJQ0lBUllfV0lUSERSQVcxIiwiR0VUX0NWViIsIlVQREFURV9LWUNfRE9DVU1FTlQiLCJERUxFVEVfU1dFRVAiLCJHRVRfQlVTSU5FU1NfREVUQUlMUyIsIkNMSUVOVF9SRUpFQ1RfVFJBTlNBQ1RJT04iLCJERUxFVEVfU1RBTkRJTkdfT1JERVIiLCJHRVRfU1RBTkRJTkdfT1JERVIiLCJDUkVBVEVfUEFZTUVOVF9UT0tFTiIsIlVQREFURV9CRU5FRklDSUFSWSIsIlVQREFURV9CQU5LX0JFTkVGSUNJQVJZIiwiTElOS0VEX0NMSUVOVF9BQ1RJVklUWV9UWVBFIiwiVVBEQVRFX0RFTEVHQVRFIiwiR0VUX1JFRkVSUkVSIiwiR0VUX0ZVTkRJTkdfUFJFRkVSRU5DRSIsIlVQREFURV9MSU5LRURfQ0xJRU5UIiwiUkVWSUVXX0RJUkVDVF9ERUJJVCJdLCJjbGllbnRfaWQiOiJXSGRldiIsImNsaWVudFR5cGUiOiJBUElfS0VZIiwic2NvcGUiOlsidGVzdCJdLCJjbGllbnRUZW5hbnRJZCI6ImIzMDBiOWVlLTQxYzItNGNlMy04NmI2LTQ2NzhlYmZiNmZlZSIsImV4cCI6MTY1ODMzMzg3MiwianRpIjoiMTA0NDE3Y2MtNmVmOS00Y2MyLWI5ZmMtNWYzNzk1NDM2OWNiIiwiYWNjb3VudEhvbGRlclR5cGUiOiJCVVNJTkVTUyJ9.bmfjBY11sVV6GwJ1iv2DGfwxbM5fkKRBEuSIl1SHhU4bqU5t7gPp0wm8QmjdNj1L2XP2nk3rmXhXDOBOB3jJ01YwcvX2BjJ_byYmv2mqxEFkkZs9F1NwkXZdNV7Gf51EiQ5VbZ6fx1f66mR2EO2PunAJuH9udgI4ImhTDcXFcikoWUrsrdgBWZyQ9x4qO2o_7CAPvBPrk0FCkMtIvKTFT42PN6R--H51O7f6bijb7K00V3qBxFchfGnmJN3aSZeYWAPm5pm3Ziq2qFNs0stkbMfom3oDZbNY1-V2B4pfgWHsSOqxqq2nUYGLjRyf_fhxdVe2NycGVJ13vAqentsmTA';

    public function GetAccessToken() {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/oauth/token?grant_type=client_credentials', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'Basic '.$this->auth,
              'Content-Type' => 'application/x-www-form-urlencoded',
            ],
          ]);
          $res_body = json_decode($response->getBody());
        $this->token = $res_body->access_token;
        $this->accounter_id = $res_body->accountHolderId;
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateAccount(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/accounts', [
            'body' => '{"currency":"'.$request->currency.'","friendlyName":"'.$request->friendlyName.'"}',
            'headers' => [
              'Accept' => 'application/json',
              'Authorization' => 'Bearer '.$this->token,
              'Content-Type' => 'application/json',
              'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
               return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);

    }

    public function GetAccountList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/accounts', [
            'headers' => [
              'Accept' => 'application/json',
              'Authorization' => 'Bearer '.$this->token,
              'Content-Type' => 'application/json',
              'x-account-holder-id' => $this->accounter_id,
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);

    }

    public function GetAccount($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/accounts/'.$id, [
            'headers' => [
              'Accept' => 'application/json',
              'Authorization' => 'Bearer '.$this->token,
              'Content-Type' => 'application/json',
              'x-account-holder-id' => $this->accounter_id,
            ],
          ]);

               return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);

    }

    public function GetBankList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/bank-accounts',[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function  CreateBeneficiary(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/beneficiaries',[
            'body' => '{"beneficiaryType":"'.$request->beneficiarytype.'","friendlyName":"'.$request->friendlyname.'","firstName":"'.$request->fname.'","lastName":"'.$request->lname.'","companyName":"'.$request->companyname.'"}',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaries() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries',[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiary($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id,[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateBankBeneficiary(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/beneficiaries/'.$request->beneid.'/bank-beneficiaries',[
            'body' => '{"paymentTypes":["SEPA_INSTANT", "SEPA"],"bankAccountCurrency":"'.$request->currencycode.'","beneficiaryType":"'.$request->beneficiarytype.'","beneficiaryCountry":"'.$request->countrycode.'","bankAccountCountry":"'.$request->bankcountrycode.'","friendlyName": "'.$request->friendlyname.'","bankAccountHolderName": "'.$request->bankholdername.'", "accountNumber":"'.$request->accountnumber.'","iban":"'.$request->iban.'", "bic":"'.$request->bic.'"}',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBankBeneficiaryList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/bank-beneficiaries',[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBankBeneficiary($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/bank-beneficiaries/'.$id,[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateBankPayout(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/bank-payouts',[
            'body' => '{"amount":{"currency":"'.$request->currencycode.'","value":'.$request->currencyvalue.'},"accountId":"'.$request->accountid.'","beneficiaryId":"'.$request->beneficiarybankid.'","paymentType":"'.$request->paymenttype.'","reference":"'.$request->reference.'"}',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateInternalTransfer(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions',[
            'body' => '{"type":"TRANSFER","source":{"type":"ACCOUNT","identifier":"'.$request->accountfromid.'"},"destination":{"type":"ACCOUNT","identifier":"'.$request->accounttoid.'"},"amount":{"currency":"'.$request->currencycode.'","value":'.$request->amount.'}}',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetTransaction($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/transactions/'.$id,[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetTransactionList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/transactions',[
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
                'x-account-holder-id' => $this->accounter_id,
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

}
