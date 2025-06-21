<?php

namespace ClarionApp\RssTorrents;

use Illuminate\Support\Facades\Http;
use ClarionApp\Backend\Models\User;

class HttpApiCall
{
    private string $url;
    private array $headers = [];

    public function __construct(string $url)
    {
        $this->url = env('APP_URL').$url;

        $user = User::first();
        $accessToken = $user->createToken('CommandCall')->accessToken;

        $this->headers = [
            "Authorization"=>"Bearer ".$accessToken,
            "Accept"=>"application/json"
        ];
    }

    public function post($body)
    {
        $response = Http::withHeaders($this->headers)
                    ->post($this->url, $body);

        return $response;
    }
}