<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

class MKMAuthority
{

    public $appToken;

    public $appSecret;

    public $accessToken;

    public $accessSecret;

    public function __construct(string $appToken, string $appSecret, string $accessToken, string $accessSecret)
    {
        $this->appToken = $appToken;
        $this->appSecret = $appSecret;
        $this->accessToken = $accessToken;
        $this->accessSecret = $accessSecret;
    }
}

