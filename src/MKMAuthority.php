<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

class MKMAuthority {
    
    public string $appToken;
    
    public string $appSecret;
    
    public string $accessToken;
    
    public string $accessSecret;
    
    public function __construct(string $appToken, string $appSecret, string $accessToken, string $accessSecret) {
        $this->appToken = $appToken;
        $this->appSecret = $appSecret;
        $this->accessToken = $accessToken;
        $this->accessSecret = $accessSecret;
    }
}

