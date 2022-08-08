<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\GlobalPassToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use DateTime;

class UserGlobalPassController extends Controller
{
    private $url = 'https://screenings-api.globalpass.ch';
    private $client_id = '';
    private $client_secret = '';
    private $auth_token = '';
    private $screen_token = '';

    public function __construct()
    {
        $this->middleware('guest');
    }


    public function GetAuth() {
        $client = new  Client();
        $response = $client->request('POST',  'https://identity-test.globalpass.ch/connect/token', [
            'body' => '{
                "grant_type":"clientCredentials"
            }',
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
              'Content-Type' => 'application/x-www-form-urlencoded',
            ],
          ]);
          $res_body = json_decode($response->getBody());
          $this->auth_token = $res_body->access_token;
    }

    public function GetScreenToken() {
        $client = new  Client();
        $this->GetAuth();
        $response = $client->request('POST',  $this->url.'/api/v2/screenings', [
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Bearer '.$this->auth_token,
            ],
          ]);
          $res_body = json_decode($response->getBody());
          $this->screen_token = $res_body->token;
          $user = auth()->user();
          $globalpass = new GlobalPassToken();
          $globalpass->user_id = $user->id;
          $globalpass->screentoken = $res_body->token;
          $globalpass->update();
    }

    public function callback(Request $request) {
        $x_secret = $request->header('X-Secret');
        if ($x_secret == $this->client_secret) {
            $user = GlobalPassToken::where('screentoken',  $request->data->ScreeningToken)->firstOrFail();
            $subject = "Update GlobalPass data";
            $gs = Generalsetting::findOrFail(1);
            $type = str_replace('.', ' ', $request->type);
            $msg = 'Your KYC status is updated';
            if($gs->is_smtp == 1)
            {
                $data = [
                  'to' => $user->user->email,
                  'subject' => $subject.'('.ucwords($type).')',
                  'body' => $msg,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            }
            else
            {
              $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
              mail($user->user->email,$subject.'('.ucwords($type).')',$msg,$headers);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => 'Okay']);
        }
        else {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your secret key is not matched']);
        }
    }

    public function GetScreenIDStatus($screentoken,$user_id) {
        $client = new  Client();
        $response = $client->request('Get',  $this->url.'/api/v2/screenings/'.$screentoken.'/status', [
            'headers' => [
                'Accept'=> '*/*',
                'Authorization' => 'Bearer '.$this->auth_token,
            ],
          ]);
          $res_body = json_decode($response->getBody());
          $user = User::findOrFail($user_id);
          switch ($res_body->status) {
            case 'Accepted':
                $user->kyc_status = 1;
                $user->update();
            case 'Rejected':
                $user->kyc_status = 2;
                $user->kyc_reject_reason = $res_body->rejectReason.'\n'.$res_body->comments;
                $user->update();
            case 'Processing':
                $user->kyc_status = 0;
                $user->update();
          }
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetScreenAddressStatus($screentoken, $user_id) {
        $client = new  Client();
        $response = $client->request('Get',  $this->url.'/api/v2/screenings/'.$screentoken.'/address/status', [
            'headers' => [
                'Accept'=> '*/*',
                'Authorization' => 'Bearer '.$this->auth_token,
            ],
          ]);
          $res_body = json_decode($response->getBody());
          $user = User::findOrFail($user_id);
          switch ($res_body->status) {
            case 'Accepted':
                $user->kyc_status = 1;
                $user->update();
            case 'Rejected':
                $user->kyc_status = 2;
                $user->kyc_reject_reason = $res_body->rejectReason.'\n'.$res_body->comments;
                $user->update();
            case 'Processing':
                $user->kyc_status = 0;
                $user->update();
          }
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetScreenID($screentoken) {
        $client = new  Client();
        $response = $client->request('Get',  $this->url.'/api/v2/screenings/'.$screentoken, [
            'headers' => [
                'Accept'=> '*/*',
                'Authorization' => 'Bearer '.$this->auth_token,
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetScreenAddress($screentoken) {
        $client = new  Client();
        $response = $client->request('Get',  $this->url.'/api/v2/screenings/'.$screentoken, [
            'headers' => [
                'Accept'=> '*/*',
                'Authorization' => 'Bearer '.$this->auth_token,
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateForensicsAnalysis(Request $request, $screentoken) {
        $client = new  Client();
        $files = $request->file('attachment');
        foreach($files as $k => $file)
        {
            $client = $client->attach('file['.$k.']', $file);
        }
        $response = $client->request('POST',  $this->url.'/api/v1/screenings/'.$screentoken.'/forensics', [
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
              'Content-Type' => 'multipart/form-data',
            ],
          ]);
          $res_body = json_decode($response->getBody());
          $this->screen_token = $res_body->token;
          $user = auth()->user();
          $globalpass = new GlobalPassToken();
          $globalpass->user_id = $user->id;
          $globalpass->screentoken = $res_body->token;
          $globalpass->update();
          return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetForensicsStatus($screentoken, $id) {
        $client = new Client();
        $response = $client->request('GET',   $this->url.'/api/v1/screenings/'.$screentoken.'/forensics/'.$id, [
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
              'Content-Type' => 'multipart/form-data',
            ],
          ]);
          return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateAMLBusinessScreen(Request $request) {
        $client = new Client();
        $response = $response = $client->request('POST',   $this->url.'/api/v2/business', [
            'body' => '{
                  "name": "'.$request->name.'",
                  "taxRegistrationNumber": "'.$request->taxnumber.'",
                  "countryCode": "'.$request->countrycode.'"
            }',
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
              'Content-Type' => 'multipart/form-data',
            ],
          ]);
          return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetAMLBusinessScreen($screentoken) {
        $client = new Client();
        $response = $response = $client->request('GET',   $this->url.'/api/v2/business/'.$screentoken, [
            'headers' => [
              'Accept'=> '*/*',
              'Authorization' => 'Basic '.base64_encode($this->client_id.':'.$this->client_secret),
              'Content-Type' => 'multipart/form-data',
            ],
          ]);
          return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }


}
