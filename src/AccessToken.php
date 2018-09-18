<?php

namespace pkpudev\graph;

use GuzzleHttp\Client;

/**
 * Getting access token from Ms Graph Api
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class AccessToken
{
  /**
   * @var string Tenant ID
   */
  protected $tenantId;
  /**
   * @var string Client ID
   */
  protected $clientId;
  /**
   * @var string Secret
   */
  protected $clientSecret;

  /**
   * Class Constructor
   * 
   * @param string $tenantId Tenant ID
   * @param string $clientId Client ID
   * @param string $clientSecret Secret
   * @return void
   */
  public function __construct($tenantId, $clientId, $clientSecret)
  {
    $this->tenantId = $tenantId;
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
  }

  /**
   * Generate the access token
   * 
   * @return string Access Token (JWT)
   */
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