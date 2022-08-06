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
use DateTime;

class UserClearJunctionController extends Controller
{
    private $url = 'https://sandbox.clearjunction.com/v7/'; // Sandbox Host Url
    private $client_uuid = '8f297d61-5391-4a1e-83e8-147223520050'; // client_uuid
    private $wallet_uuid = '8f297da3-8838-437f-b4e1-ce9f7714c61b'; // Sandbwallet_uuid
    private $API_Key = '8f299ac0-1543-41f6-b094-70a5fd837bed';
    private $apiPassword = 'eydy8qv9ui0o';
    //private $apiPassword = hash("sha512", 'eydy8qv9ui0o');


	//echo $hashed = hash("sha512", $password);
    public function getToken($request) {
        $secret = hash('sha512', $this->apiPassword);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        // $body = json_encode($request);
        $signature = hash('sha512', mb_strtoupper($this->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }

    public function CheckBankWallet(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('GET',  $this->url.'bank/wallets/'.$this->wallet_uuid.'?returnPaymentMethods=true', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function CreateInvidualWallet(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'bank/wallets/'.$this->wallet_uuid.'/individuals', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function WalletTransfer(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/wallets/transfer', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetWalletStatement(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/wallets/statement', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AllocateIbanIndividual(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/iban/individual', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function AllocateIbanCreate(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/allocate/v2/create/iban', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function AllocateBecsCreate(Request $request) {
        $client = new  Client();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/allocate/v2/create/becs', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetIbanStatusByClientOrder(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/clientOrder/'.$request->get('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
    
  public function GetIbanStatusByOrderRef(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/orderReference/'.$request->get('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
 
  public function GetIbanByIban(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/info/iban/'.$request->get('iban_no'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function IbanListByCid(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/list/iban/'.$request->get('customer_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function BecsByOrderRef(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/becs/orderReference/'.$request->get('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function BecsByClientOrderid(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/becs/clientOrder/'.$request->get('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  /*Payout Clear Junction API*/
  public function ToUsBankSwift(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/swift?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function ToUsBankFedwire(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/fedwire?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
 
  public function ToUsBankSignet(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/signet?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function InternalPayment(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/internalPayment', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function EuBankInstant(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/sepaInst?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function UkBankFaster(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/fps?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function UkBankChaps(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/chaps?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function AuBankBecs(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/becs?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function UaBankPayout(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/ua?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function MdBankPayout(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/md?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function CreditCardPayout(Request $request) {
      $client = new  Client();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/creditCardNonPc?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function PayoutStatusOrderRef(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/payout/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function PayoutStatusClientOrder(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/payout/clientOrder/'.$request->input('client_order'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function CreateInvoice(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/invoice/creditCard', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function InvoiceStatusByRef(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/invoice/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function InvoiceStatusByOrder(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/invoice/clientOrder/'.$request->input('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function TransactionApprove(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/transactionAction/approve', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function TransactionCancel(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/transactionAction/cancel', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function CreateToken(Request $request) {
      $client = new  Client();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'pci/createToken', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function CheckRequiByIBAN(Request $request) {
      $client = new  Client();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/checkRequisite/bankTransfer/eu/iban'.$request->input('iban_no'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function TransactionReport(Request $request) {
      $client = new  Client();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/reports/transactionReport', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function TransferStatusOrderRef(Request $request) {
      $client = new  Client();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/status/walletTransfer/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  
  public function TransferStatusClientOrder(Request $request) {
      $client = new  Client();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/status/walletTransfer/clientOrder//'.$request->input('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $this->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

}
