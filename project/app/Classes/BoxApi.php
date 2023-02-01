<?php

namespace App\Classes;

use App\Models\Generalsetting;
use GuzzleHttp\Client;
use GuzzleHttp;



class BoxApiException extends \ErrorException

{};

class BoxApi
{
    protected $endpoint = 'https://api.box.com';
    protected $client_id = null;
    protected $client_secret = null;

    public function __construct()
    {
        $gs = Generalsetting::first();
        $this->client_id = (string) $gs->box_id;
        $this->client_secret = (string) $gs->box_secret;
    }

    public function api_check() {
        $gs = Generalsetting::first();
        if($gs->box_id == null && $gs->box_secret == null) {
            return array('warning', 'Please input Box API credentials.(System Setting -> Api Settings)');
        }
        return array('success', 'Api credentials are set up.');
    }

    public function basic_token()
    {
        $client = new Client();
        $options = [
        'form_params' => [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        ]];
        try {
            $response = $client->request('POST', 'https://api.box.com/oauth2/token', $options);
            $res_body = json_decode($response->getBody());
            $option_2 = [
                'form_params' => [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'subject_token' => $res_body->access_token,
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
                    'scope' => 'item_upload item_preview base_explorer',
                    'subject_token_type' => 'urn:ietf:params:oauth:token-type:access_token'
                ]];
            $response = $client->request('POST', 'https://api.box.com/oauth2/token', $options);
            $res_body = json_decode($response->getBody());
            return($res_body);
        } catch (\Throwable $th) {
            // return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
            return ($th->getMessage());
            //throw $th;
        }
        return redirect()->back()->with('message', 'this is success');
    }

    public function upload($document_name, $source_url)
    {
        $client = new Client();
        try {
            $res = $this->basic_token();
            $access_token = 'PkqTCLrBH0xXYyv8VWhTPpFUGccP312Z';
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
        $headers = [
            'Authorization' => 'Bearer '.$access_token
          ];
          $options = [
            'multipart' => [
              [
                'name' => 'attributes',
                'contents' => '{"name":"'.$document_name.'", "parent":{"id":"0"}}'
              ],
              [
                'name' => 'file',
                'contents' => GuzzleHttp\Psr7\Utils::tryFopen($source_url, 'r'),
                'filename' => $source_url,
                'headers'  => [
                  'Content-Type' => '<Content-type header>'
                ]
              ]
          ]];
          try {
            //code...
            $request = new GuzzleHttp\Psr7\Request('POST', 'https://upload.box.com/api/2.0/files/content', $headers);
            $res = $client->sendAsync($request, $options)->wait();
            return json_decode($res->getBody());
          } catch (\Throwable $th) {
            //throw $th;
            return ($th->getMessage());
          }


        return redirect()->back()->with('message', 'this is success');
    }

    public function delete($file_id)
    {
        $client = new Client();
        try {
            $res = $this->basic_token();
            $access_token = 'PkqTCLrBH0xXYyv8VWhTPpFUGccP312Z';
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
        $headers = [
            'Authorization' => 'Bearer '.$access_token,
        ];
        try {
            $request = new GuzzleHttp\Psr7\Request('DELETE', 'https://api.box.com/2.0/files/'.$file_id, $headers);
            $res = $client->sendAsync($request)->wait();
            return json_decode($res->getBody());
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
        return redirect()->back()->with('message', 'this is success');
    }

    public function download($file_id)
    {
        $client = new Client();
        try {
            $res = $this->basic_token();
            $access_token = 'PkqTCLrBH0xXYyv8VWhTPpFUGccP312Z';
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
        $headers = [
            'Authorization' => 'Bearer '.$access_token,
        ];
        try {
            $request = new GuzzleHttp\Psr7\Request('GET', 'https://api.box.com/2.0/files/'.$file_id.'/content', $headers);
            $res = $client->sendAsync($request)->wait();
            return $res->getBody();
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
        return redirect()->back()->with('message', 'this is success');
    }

}
