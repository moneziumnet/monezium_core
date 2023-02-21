<?php

namespace App\Classes;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EthereumException extends \ErrorException

{};

class EthereumRpcService
{
    public function createAccount(string $password): string
    {
        return $this->call('personal_newAccount', [$password]);
    }

    public function getEtherBalance(string $address): float
    {
        return hexdec($this->call('eth_getBalance', [$address, 'latest'])) / (10 ** 18);
    }

    public function getTokenBalance(string $contract, string $address, int $decimal)
    {
        $signature = $this->getFunctionSignature('balanceOf(address)');
        $balance = $this->call('eth_call', [[
            'to' => $contract,
            'data' => $signature . str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT),
        ], 'latest']);
        if ($balance == 'error')
        return $balance;

        return hexdec($balance) / (10 ** $decimal);
    }

    public function sendEther(string $from, string $to, float $value)
    {
        return $this->call('eth_sendTransaction', [[
            'from' => $from,
            'to' => $to,
            'value' => '0x' . $this->bcdechex($this->toWei($value)),
        ]]);
    }

    public function transferToken(string $tokenContract, string $from, string $to, float $value)
    {
        $signature = $this->getFunctionSignature('transfer(address,uint256)');
        $to = str_pad(substr($to, 2), 64, '0', STR_PAD_LEFT);
        $value = str_pad($this->bcdechex($this->toWei($value)), 64, '0', STR_PAD_LEFT);
        // $value = "0x".dechex((int)($value*pow(10,18)));
        // dd($value);
        // $eth_balance = $this->getEtherBalance($from);
        // $gas_fee = $this->estimateGas([
        //     'from' => $from,
        //     'to' => $tokenContract,
        //     'data' => $signature . $to . $value,
        //     'value' => '0x0',
        // ]);
        // if (hexdec($gas_fee) / (10 ** 18) > $eth_balance) {
        //     $res = [
        //         'error' => [
        //             'message' => 'eth balance insulance funds.',
        //         ]
        //     ];
        //     return $res;
        // }
        return $this->call_transfer('eth_sendTransaction', [[
            'from' => $from,
            'to' => $tokenContract,
            'data' => $signature . $to . $value,
            'value' => '0x0',
        ]]);
    }

    public function approveTransfer(string $tokenContract, string $from, string $spender, float $value): array
    {
        $signature = $this->getFunctionSignature('approve(address,uint256)');
        $spender = str_pad(substr($spender, 2), 64, '0', STR_PAD_LEFT);
        $value = str_pad($this->bcdechex($this->toWei($value)), 64, '0', STR_PAD_LEFT);
        return $this->call('eth_sendTransaction', [[
            'from' => $from,
            'to' => $tokenContract,
            'data' => $signature . $spender . $value,
            'value' => '0x0',
        ]]);
    }
    public function call_transfer($method, array $parameters = [], $link = 'localhost:8545')
    {
        $body = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $parameters,
            'id' => $id = time(),
        ];
        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => json_encode($body)]);
            $res = json_decode($response->getBody());

        } catch (\Throwable $th) {
                return 'error';
        }
        if (isset($res->error)) {
            return $res;
            // throw new EthereumException(sprintf('Ethereum client error: %s', $res->error->message));
        }
        if ($method == 'eth_sendTransaction') {
            do {
                sleep(1);
                $txReceipt = $this->getTransactionReceipt($res->result);
            } while (!$txReceipt);
            return $txReceipt;
        }
        return $res;
    }

    public function call($method, array $parameters = [], $link = 'localhost:8545')
    {
        $body = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $parameters,
            'id' => $id = time(),
        ];
        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $client->request('POST', $link, ["headers" => $headers, "body" => json_encode($body), 'connect_timeout' => 0.5]);
            $res = json_decode($response->getBody());
            return $res->result;
        } catch (\Throwable $th) {
            return 'error';
        }
    }
    public function unlockAccount(string $address, string $password, int $duration = 30): void
    {
        $this->call('personal_unlockAccount', [$address, $password, $duration]);
    }

    public function getTransactionReceipt(string $txHash)
    {
        return $this->call('eth_getTransactionReceipt', [$txHash]);
    }

    private function getCoinbase(): string
    {
        return $this->call('eth_coinbase');
    }

    private function getFunctionSignature(string $function): string
    {
        $signature = $this->getSha3($function);
        return substr($signature, 0, 10);
    }

    private function getSha3(string $string): string
    {
        return $this->call('web3_sha3', ['0x' . $this->strhex($string)]);
    }

    private function estimateGas(array $payload): string
    {
        return $this->call('eth_estimateGas', [$payload]);
    }

    // Here be Dragons...

    private function strhex(string $string): string
    {
        $hexstr = unpack('H*', $string);
        return array_shift($hexstr);
    }

    private function hexstr(string $string): string
    {
        return pack('H*', $string);
    }

    private function toWei(float $value, int $decimals = 18): string
    {
        $brokenNumber = explode('.', $value);
        return number_format($brokenNumber[0]) . '' . str_pad($brokenNumber[1] ?? '0', $decimals, '0');
    }

    private function bcdechex(string $dec): string
    {
        $hex = '';
        do {
            $last = bcmod($dec, 16);
            $hex = dechex($last) . $hex;
            $dec = bcdiv(bcsub($dec, $last), 16);
        } while ($dec > 0);
        return $hex;
    }

}
