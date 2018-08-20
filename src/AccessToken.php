<?php

namespace pkpudev\graph;

use GuzzleHttp\Client;

class AccessToken
{
  protected $tenantId;
  protected $clientId;
  protected $clientSecret;

  public function __construct($tenantId, $clientId, $clientSecret)
  {
    $this->tenantId = $tenantId;
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
  }

  public function generate()
  {
    // Parameters
    $url = Endpoint::BASE . '/' . $this->tenantId . Endpoint::TOKEN;
    $form_params = [
      'tenant' => $this->tenantId,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'scope' => Endpoint::SCOPE,
      'grant_type' => Endpoint::GRANT,
    ];
    // Token object
    $token = json_decode((new Client)
      ->post($url, compact('form_params'))
      ->getBody()
      ->getContents()
    );
    return $token->access_token;
  }
}