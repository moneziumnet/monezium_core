<?php

namespace App\Classes;

/**
 * Main Binance class
 *
 * Eg. Usage:
 * $api = new BinanceAPI();
 */

class BinanceAPIException extends \ErrorException {};

class BinanceAPI {

    protected $base = 'https://api.binance.com/api/'; // /< REST endpoint for the currency exchange
    protected $wapi = 'https://api.binance.com/wapi/'; // /< REST endpoint for the withdrawals
    protected $sapi = 'https://api.binance.com/sapi/'; // /< REST endpoint for the supporting network API

    protected $api_key; // /< API key that you created in the binance website member area
    protected $api_secret; // /< API secret that was given to you when you created the api key

    protected $curlOpts = []; // /< User defined curl coptions
    protected $info = [
        "timeOffset" => 0,
    ]; // /< Additional connection options
    protected $proxyConf = null; // /< Used for story the proxy configuration
    protected $caOverride = false; // /< set this if you donnot wish to use CA bundle auto download feature
    protected $transfered = 0; // /< This stores the amount of bytes transfered
    protected $requestCount = 0; // /< This stores the amount of API requests
    protected $httpDebug = false; // /< If you enable this, curl will output debugging information
    protected $subscriptions = []; // /< View all websocket subscriptions
    protected $btc_value = 0.00; // /< value of available assets
    protected $btc_total = 0.00;

    // /< value of available onOrder assets

    protected $exchangeInfo = NULL;
    protected $lastRequest = [];

    /**
     * Constructor for the class,
     * send as many argument as you want.
     *
     * No arguments - use file setup
     * 1 argument - file to load config from
     * 2 arguments - api key and api secret
     *
     * @return null
     */
    public function __construct()
    {
        $param = func_get_args();
        switch (count($param)) {
            case 0:
                // $this->setupApiConfigFromFile();
                // $this->setupProxyConfigFromFile();
                // $this->setupCurlOptsFromFile();
                break;
            case 1:
                // $this->setupApiConfigFromFile($param[0]);
                // $this->setupProxyConfigFromFile($param[0]);
                // $this->setupCurlOptsFromFile($param[0]);
                break;
            case 2:
                $this->api_key = $param[0];
                $this->api_secret = $param[1];
                break;
            default:
                echo 'Please see valid constructors here: https://github.com/jaggedsoft/php-binance-api/blob/master/examples/constructor.php';
        }
    }

    /**
     * magic get for protected and protected members
     *
     * @param $file string the name of the property to return
     * @return null
     */
    public function __get(string $member)
    {
        if (property_exists($this, $member)) {
            return $this->$member;
        }
        return null;
    }

    /**
     * magic set for protected and protected members
     *
     * @param $member string the name of the member property
     * @param $value the value of the member property
     */
    public function __set(string $member, $value)
    {
        $this->$member = $value;
    }



    /**
     * buy attempts to create a currency order
     * each currency supports a number of order types, such as
     * -LIMIT
     * -MARKET
     * -STOP_LOSS
     * -STOP_LOSS_LIMIT
     * -TAKE_PROFIT
     * -TAKE_PROFIT_LIMIT
     * -LIMIT_MAKER
     *
     * You should check the @see exchangeInfo for each currency to determine
     * what types of orders can be placed against specific pairs
     *
     * $quantity = 1;
     * $price = 0.0005;
     * $order = $api->buy("BNBBTC", $quantity, $price);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function buy(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * buyTest attempts to create a TEST currency order
     *
     * @see buy()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function buyTest(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, $price, $type, $flags, true);
    }

    /**
     * sell attempts to create a currency order
     * each currency supports a number of order types, such as
     * -LIMIT
     * -MARKET
     * -STOP_LOSS
     * -STOP_LOSS_LIMIT
     * -TAKE_PROFIT
     * -TAKE_PROFIT_LIMIT
     * -LIMIT_MAKER
     *
     * You should check the @see exchangeInfo for each currency to determine
     * what types of orders can be placed against specific pairs
     *
     * $quantity = 1;
     * $price = 0.0005;
     * $order = $api->sell("BNBBTC", $quantity, $price);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function sell(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * sellTest attempts to create a TEST currency order
     *
     * @see sell()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type array config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function sellTest(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags, true);
    }

    /**
     * marketBuy attempts to create a currency order at given market price
     *
     * $quantity = 1;
     * $order = $api->marketBuy("BNBBTC", $quantity);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketBuy(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags);
    }

    /**
     * marketBuyTest attempts to create a TEST currency order at given market price
     *
     * @see marketBuy()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketBuyTest(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags, true);
    }


    /**
     * numberOfDecimals() returns the signifcant digits level based on the minimum order amount.
     *
     * $dec = numberOfDecimals(0.00001); // Returns 5
     *
     * @param $val float the minimum order amount for the pair
     * @return integer (signifcant digits) based on the minimum order amount
     */
    public function numberOfDecimals($val = 0.00000001){
        $val = sprintf("%.14f", $val);
        $parts = explode('.', $val);
        $parts[1] = rtrim($parts[1], "0");
        return strlen($parts[1]);
    }

    /**
     * marketSell attempts to create a currency order at given market price
     *
     * $quantity = 1;
     * $order = $api->marketSell("BNBBTC", $quantity);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketSell(string $symbol, $quantity, array $flags = [])
    {
        $c = $this->numberOfDecimals($this->exchangeInfo()['symbols'][$symbol]['filters'][2]['minQty']);
        $quantity = $this->floorDecimal($quantity, $c);

        return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags);
    }

    protected function floorDecimal($n, $decimals=2)
    {
        return floor($n * pow(10, $decimals)) / pow(10, $decimals);
    }


    /**
     * marketSellTest attempts to create a TEST currency order at given market price
     *
     * @see marketSellTest()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketSellTest(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags, true);
    }

    /**
     * cancel attempts to cancel a currency order
     *
     * $orderid = "123456789";
     * $order = $api->cancel("BNBBTC", $orderid);
     *
     * @param $symbol string the currency symbol
     * @param $orderid string the orderid to cancel
     * @param $flags array of optional options like ["side"=>"sell"]
     * @return array with error message or the order details
     * @throws BinanceAPIException
     */
    public function cancel(string $symbol, $orderid, $flags = [])
    {
        $params = [
            "symbol" => $symbol,
            "orderId" => $orderid,
        ];
        return $this->httpRequest("v3/order", "DELETE", array_merge($params, $flags), true);
    }

    /**
     * orderStatus attempts to get orders status
     *
     * $orderid = "123456789";
     * $order = $api->orderStatus("BNBBTC", $orderid);
     *
     * @param $symbol string the currency symbol
     * @param $orderid string the orderid to cancel
     * @return array with error message or the order details
     * @throws BinanceAPIException
     */
    public function orderStatus(string $symbol, $orderid)
    {
        return $this->httpRequest("v3/order", "GET", [
            "symbol" => $symbol,
            "orderId" => $orderid,
        ], true);
    }

    /**
     * openOrders attempts to get open orders for all currencies or a specific currency
     *
     * $allOpenOrders = $api->openOrders();
     * $allBNBOrders = $api->openOrders( "BNBBTC" );
     *
     * @param $symbol string the currency symbol
     * @return array with error message or the order details
     * @throws BinanceAPIException
     */
    public function openOrders(string $symbol = null)
    {
        $params = [];
        if (is_null($symbol) != true) {
            $params = [
                "symbol" => $symbol,
            ];
        }
        return $this->httpRequest("v3/openOrders", "GET", $params, true);
    }

    /**
     * orders attempts to get the orders for all or a specific currency
     *
     * $allBNBOrders = $api->orders( "BNBBTC" );
     *
     * @param $symbol string the currency symbol
     * @param $limit int the amount of orders returned
     * @param $fromOrderId string return the orders from this order onwards
     * @param $params array optional startTime, endTime parameters
     * @return array with error message or array of orderDetails array
     * @throws BinanceAPIException
     */
    public function orders(string $symbol, int $limit = 500, int $fromOrderId = -1, array $params = []) {
	$params["symbol"] = $symbol;
	$params["limit"] = $limit;
        if ( $fromOrderId ) $params["orderId"] = $fromOrderId;
        return $this->httpRequest("v3/allOrders", "GET", $params, true);
    }

    /**
     * history Get the complete account trade history for all or a specific currency
     *
     * $allHistory = $api->history();
     * $BNBHistory = $api->history("BNBBTC");
     * $limitBNBHistory = $api->history("BNBBTC",5);
     * $limitBNBHistoryFromId = $api->history("BNBBTC",5,3);
     *
     * @param $symbol string the currency symbol
     * @param $limit int the amount of orders returned
     * @param $fromTradeId int (optional) return the orders from this order onwards. negative for all
     * @return array with error message or array of orderDetails array
     * @throws BinanceAPIException
     */
    public function history(string $symbol, int $limit = 500, int $fromTradeId = -1)
    {
        $parameters = [
            "symbol" => $symbol,
            "limit" => $limit,
        ];
        if ($fromTradeId > 0) {
            $parameters["fromId"] = $fromTradeId;
        }

        return $this->httpRequest("v3/myTrades", "GET", $parameters, true);
    }

    /**
     * useServerTime adds the 'useServerTime'=>true to the API request to avoid time errors
     *
     * $api->useServerTime();
     *
     * @return null
     * @throws BinanceAPIException
     */
    public function useServerTime()
    {
        $request = $this->httpRequest("v1/time");
        if (isset($request['serverTime'])) {
            $this->info['timeOffset'] = $request['serverTime'] - (microtime(true) * 1000);
        }
    }

    /**
     * time Gets the server time
     *
     * $time = $api->time();
     *
     * @return array with error message or array with server time key
     * @throws BinanceAPIException
     */
    public function time()
    {
        return $this->httpRequest("v1/time");
    }

    /**
     * exchangeInfo Gets the complete exchange info, including limits, currency options etc.
     *
     * $info = $api->exchangeInfo();
     *
     * @return array with error message or exchange info array
     * @throws BinanceAPIException
     */
    public function exchangeInfo()
    {
        if(!$this->exchangeInfo){

            $arr = $this->httpRequest("v1/exchangeInfo");

            $this->exchangeInfo = $arr;
            $this->exchangeInfo['symbols'] = null;

            foreach($arr['symbols'] as $key => $value){
                $this->exchangeInfo['symbols'][$value['symbol']] = $value;
            }

        }

        return $this->exchangeInfo;
    }

    public function assetDetail()
    {
        $params["wapi"] = true;
        return $this->httpRequest("v3/assetDetail.html", 'GET', $params, true);
    }


    /**
     * Fetch current(daily) trade fee of symbol, values in percentage.
     * for more info visit binance official api document
     *
     * $symbol = "BNBBTC"; or any other symbol or even a set of symbols in an array
     * @param string $symbol
     * @return mixed
     */
    public function tradeFee(string $symbol)
    {
	$params = [
            "symbol" => $symbol,
            "wapi" => true,
        ];

        return $this->httpRequest("v3/tradeFee.html", 'GET', $params, true);
    }

    /**
     * withdraw requests a asset be withdrawn from binance to another wallet
     *
     * $asset = "BTC";
     * $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
     * $amount = 0.2;
     * $response = $api->withdraw($asset, $address, $amount);
     *
     * $address = "44tLjmXrQNrWJ5NBsEj2R77ZBEgDa3fEe9GLpSf2FRmhexPvfYDUAB7EXX1Hdb3aMQ9FLqdJ56yaAhiXoRsceGJCRS3Jxkn";
     * $addressTag = "0e5e38a01058dbf64e53a4333a5acf98e0d5feb8e523d32e3186c664a9c762c1";
     * $amount = 0.1;
     * $response = $api->withdraw($asset, $address, $amount, $addressTag);
     *
     * @param $asset string the currency such as BTC
     * @param $address string the addressed to whihc the asset should be deposited
     * @param $amount double the amount of the asset to transfer
     * @param $addressTag string adtional transactionid required by some assets
     * @return array with error message or array transaction
     * @throws BinanceAPIException
     */
    public function withdraw(string $asset, string $address, $amount, $addressTag = null, $addressName = "API Withdraw", bool $transactionFeeFlag = false,$network = null)
    {
        $options = [
            "coin" => $asset,
            "address" => $address,
            "amount" => $amount,
            "sapi" => true,
        ];
        // if (is_null($addressName) === false && empty($addressName) === false) {
        //     $options['name'] = $addressName;
        // }
        // if (is_null($addressTag) === false && empty($addressTag) === false) {
        //     $options['addressTag'] = $addressTag;
        // }
        // if (is_null($network) === false && empty($network) === false) {
        //     $options['network'] = $network;
        // }
        return $this->httpRequest("v1/capital/withdraw/apply", "POST", $options, true);
    }

    /**
     * depositAddress get the deposit address for an asset
     *
     * $depositAddress = $api->depositAddress("VEN");
     *
     * @param $asset string the currency such as BTC
     * @return array with error message or array deposit address information
     * @throws BinanceAPIException
     */
    public function depositAddress(string $asset)
    {
        $params = [
            "sapi" => true,
            "coin" => $asset,
        ];
        return $this->httpRequest("v1/capital/deposit/address", "GET", $params, true);
    }

    /**
     * depositAddress get the deposit history for an asset
     *
     * $depositHistory = $api->depositHistory();
     *
     * $depositHistory = $api->depositHistory( "BTC" );
     *
     * @param $asset string empty or the currency such as BTC
     * @param $params array optional startTime, endTime, status parameters
     * @return array with error message or array deposit history information
     * @throws BinanceAPIException
     */
    public function depositHistory(string $asset = null, array $params = [])
    {
        $params["wapi"] = true;
        if (is_null($asset) === false) {
            $params['asset'] = $asset;
        }
        return $this->httpRequest("v3/depositHistory.html", "GET", $params, true);
    }

    /**
     * withdrawHistory get the withdrawal history for an asset
     *
     * $withdrawHistory = $api->withdrawHistory();
     *
     * $withdrawHistory = $api->withdrawHistory( "BTC" );
     *
     * @param $asset string empty or the currency such as BTC
     * @param $params array optional startTime, endTime, status parameters
     * @return array with error message or array deposit history information
     * @throws BinanceAPIException
     */
    public function withdrawHistory(string $asset = null, array $params = [])
    {
        $params["wapi"] = true;
        if (is_null($asset) === false) {
            $params['asset'] = $asset;
        }
        return $this->httpRequest("v3/withdrawHistory.html", "GET", $params, true);
    }

    /**
     * withdrawFee get the withdrawal fee for an asset
     *
     * $withdrawFee = $api->withdrawFee( "BTC" );
     *
     * @param $asset string currency such as BTC
     * @return array with error message or array containing withdrawFee
     * @throws BinanceAPIException
     */
    public function withdrawFee(string $asset)
    {
        $params = [
            "wapi" => true,
        ];

        $response = $this->httpRequest("v3/assetDetail.html", "GET", $params, true);

        if (isset($response['success'], $response['assetDetail'], $response['assetDetail'][$asset]) && $response['success']) {
            return $response['assetDetail'][$asset];
        }
    }

    /**
     * price get the latest price of a symbol
     *
     * $price = $api->price( "ETHBTC" );
     *
     * @return array with error message or array with symbol price
     * @throws BinanceAPIException
     */
    public function price(string $symbol)
    {
        $ticker = $this->httpRequest("v3/ticker/price", "GET", ["symbol" => $symbol]);

        return $ticker['price'];
    }

    /**
     * account get all information about the api account
     *
     * $account = $api->account();
     *
     * @return array with error message or array of all the account information
     * @throws BinanceAPIException
     */
    public function account()
    {
        return $this->httpRequest("v3/account", "GET", [], true);
    }

    /**
     * httpRequest curl wrapper for all http api requests.
     * You can't call this function directly, use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $url string the endpoint to query, typically includes query string
     * @param $method string this should be typically GET, POST or DELETE
     * @param $params array addtional options for the request
     * @param $signed bool true or false sign the request with api secret
     * @return array containing the response
     * @throws BinanceAPIException
     */
    protected function httpRequest(string $url, string $method = "GET", array $params = [], bool $signed = false)
    {
        if (function_exists('curl_init') === false) {
            throw new BinanceAPIException("Sorry cURL is not installed!");
        }

        // if ($this->caOverride === false) {
        //     if (file_exists(getcwd() . '/ca.pem') === false) {
        //         $this->downloadCurlCaBundle();
        //     }
        // }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
        $query = http_build_query($params, '', '&');

        // signed with params
        if ($signed === true) {
            if (empty($this->api_key)) {
                throw new BinanceAPIException("signedRequest error: API Key not set!");
            }

            if (empty($this->api_secret)) {
                throw new BinanceAPIException("signedRequest error: API Secret not set!");
            }

            $base = $this->base;
            $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
            $params['timestamp'] = number_format($ts, 0, '.', '');
            if (isset($params['wapi'])) {
                unset($params['wapi']);
                $base = $this->wapi;
            }

            if (isset($params['sapi'])) {
                unset($params['sapi']);
                $base = $this->sapi;
            }

            $query = http_build_query($params, '', '&');
            $signature = hash_hmac('sha256', $query, $this->api_secret);
            if ($method === "POST") {
                $endpoint = $base . $url;
				$params['signature'] = $signature; // signature needs to be inside BODY
				$query = http_build_query($params, '', '&'); // rebuilding query
            } else {
                $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        // params so buildquery string and append to url
        else if (count($params) > 0) {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
        }
        // no params so just the base url
        else {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
        // Post and postfields
        if ($method === "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        // Delete Method
        if ($method === "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        // PUT Method
        if ($method === "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        // proxy settings
        // if (is_array($this->proxyConf)) {
        //     curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
        //     if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
        //         curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
        //     }
        // }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // set user defined curl opts last for overriding
        foreach ($this->curlOpts as $key => $value) {
            curl_setopt($curl, constant($key), $value);
        }

        // if ($this->caOverride === false) {
        //     if (file_exists(getcwd() . '/ca.pem') === false) {
        //         $this->downloadCurlCaBundle();
        //     }
        // }

        $output = curl_exec($curl);
        // Check if any error occurred
        if (curl_errno($curl) > 0) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            echo 'Curl error: ' . curl_error($curl) . "\n";
            return [];
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $output = substr($output, $header_size);

        curl_close($curl);

        $json = json_decode($output, true);

        $this->lastRequest = [
            'url' => $url,
            'method' => $method,
            'params' => $params,
            'header' => $header,
            'json' => $json
        ];

        // if (isset($header['x-mbx-used-weight'])) {
        //     $this->setXMbxUsedWeight($header['x-mbx-used-weight']);
        // }

        // if (isset($header['x-mbx-used-weight-1m'])) {
        //     $this->setXMbxUsedWeight1m($header['x-mbx-used-weight-1m']);
        // }

        if(isset($json['msg'])){
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            // echo "signedRequest error: {$output}" . PHP_EOL;
        }
        $this->transfered += strlen($output);
        $this->requestCount++;
        return $json;
    }

    /**
     * order formats the orders before sending them to the curl wrapper function
     * You can call this function directly or use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $side string typically "BUY" or "SELL"
     * @param $symbol string to buy or sell
     * @param $quantity string in the order
     * @param $price string for the order
     * @param $type string is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
     * @param $flags array additional transaction options
     * @param $test bool whether to test or not, test only validates the query
     * @return array containing the response
     * @throws BinanceAPIException
     */
    public function order(string $side, string $symbol, $quantity, string $type = "LIMIT", array $flags = [], bool $test = false)
    {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 60000,
        ];

        // someone has preformated there 8 decimal point double already
        // dont do anything, leave them do whatever they want
        // if (gettype($price) !== "string") {
        //     // for every other type, lets format it appropriately
        //     $price = number_format($price, 8, '.', '');
        // }

        if (is_numeric($quantity) === false) {
            // WPCS: XSS OK.
            echo "warning: quantity expected numeric got " . gettype($quantity) . PHP_EOL;
        }

        // if (is_string($price) === false) {
        //     // WPCS: XSS OK.
        //     echo "warning: price expected string got " . gettype($price) . PHP_EOL;
        // }

        if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
            $opt["timeInForce"] = "GTC";
        }

        if (isset($flags['stopPrice'])) {
            $opt['stopPrice'] = $flags['stopPrice'];
        }

        if (isset($flags['icebergQty'])) {
            $opt['icebergQty'] = $flags['icebergQty'];
        }

        if (isset($flags['newOrderRespType'])) {
            $opt['newOrderRespType'] = $flags['newOrderRespType'];
        }

        $qstring = ($test === false) ? "v3/order" : "v3/order/test";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    public function prices()
    {
        return $this->priceData($this->httpRequest("v3/ticker/price"));
    }
    protected function priceData(array $array)
    {
        $prices = [];
        foreach ($array as $obj) {
            $prices[$obj['symbol']] = $obj['price'];
        }
        return $prices;
    }

    public function balances($priceData = false)
    {
        if (is_array($priceData) === false) {
            $priceData = false;
        }

        $account = $this->httpRequest("v3/account", "GET", [], true);

        if (is_array($account) === false) {
            // echo "Error: unable to fetch your account details" . PHP_EOL;
        }

        if (isset($account['balances']) === false) {
            // echo "Error: your balances were empty or unset" . PHP_EOL;
        }

        return $this->balanceData($account, $priceData);
    }

    protected function balanceData(array $array, $priceData)
    {
        $balances = [];

        if (is_array($priceData)) {
            $btc_value = $btc_total = 0.00;
        }

        if (empty($array) || empty($array['balances'])) {
            // WPCS: XSS OK.
            // echo "balanceData error: Please make sure your system time is synchronized: call \$api->useServerTime() before this function" . PHP_EOL;
            // echo "ERROR: Invalid request. Please double check your API keys and permissions." . PHP_EOL;
            return [];
        }

        foreach ($array['balances'] as $obj) {
            $asset = $obj['asset'];
            $balances[$asset] = [
                "available" => $obj['free'],
                "onOrder" => $obj['locked'],
                "btcValue" => 0.00000000,
                "btcTotal" => 0.00000000,
            ];

            if (is_array($priceData) === false) {
                continue;
            }

            if ($obj['free'] + $obj['locked'] < 0.00000001) {
                continue;
            }

            if ($asset === 'BTC') {
                $balances[$asset]['btcValue'] = $obj['free'];
                $balances[$asset]['btcTotal'] = $obj['free'] + $obj['locked'];
                $btc_value += $obj['free'];
                $btc_total += $obj['free'] + $obj['locked'];
                continue;
            } elseif ( $asset === 'USDT' || $asset === 'USDC' || $asset === 'PAX' || $asset === 'BUSD' ) {
                $btcValue = $obj['free'] / $priceData['BTCUSDT'];
                $btcTotal = ($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'];
                $balances[$asset]['btcValue'] = $btcValue;
                $balances[$asset]['btcTotal'] = $btcTotal;
                $btc_value += $btcValue;
                $btc_total += $btcTotal;
                continue;
            }

            $symbol = $asset . 'BTC';

            if ($symbol === 'BTCUSDT') {
                $btcValue = number_format($obj['free'] / $priceData['BTCUSDT'], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'], 8, '.', '');
            } elseif (isset($priceData[$symbol]) === false) {
                $btcValue = $btcTotal = 0;
            } else {
                $btcValue = number_format($obj['free'] * $priceData[$symbol], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) * $priceData[$symbol], 8, '.', '');
            }

            $balances[$asset]['btcValue'] = $btcValue;
            $balances[$asset]['btcTotal'] = $btcTotal;
            $btc_value += $btcValue;
            $btc_total += $btcTotal;
        }
        if (is_array($priceData)) {
            uasort($balances, function ($opA, $opB) {
                return $opA['btcValue'] < $opB['btcValue'];
            });
            $this->btc_value = $btc_value;
            $this->btc_total = $btc_total;
        }
        return $balances;
    }
}
?>