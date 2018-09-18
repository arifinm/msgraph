<?php

namespace pkpudev\graph;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\LimitStream;
use GuzzleHttp\Psr7\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;

/**
 * Uploader for Ms Graph Model
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class Uploader
{
  const UPLOAD_LIMIT = 60000000; //60mb

  /**
   * @var Graph Graph object
   */
  protected $graph;
  /**
   * @var int Chunk Limit for Resumable Upload
   */
  protected $limit;

  /**
   * Class Constructor
   * 
   * @param string $token Access Token
   * @param int $limit Chunk Limit for Resumable Upload
   * @return void
   */
  public function __construct($token, $limit=1024)
  {
    $this->graph = new Graph;
    $this->graph->setAccessToken($token);
    $this->limit = $limit;
  }

  /**
   * Create Session for Resumable Upload
   * 
   * @param string $userId User ID
   * @param string $itemId Item ID
   * @param string $filename Filename
   * @return array|null Response Body containing uploadUrl
   */
  public function createSession($userId, $itemId, $filename)
  {
    $url = '/users/%s/drive/items/%s:/%s:/createUploadSession';
    $item = [
      // '@odata.type' => 'microsoft.graph.driveItemUploadableProperties',
      '@microsoft.graph.conflictBehavior' => 'replace',
      'name' => $filename,
    ];

    $response = $this->graph
      ->createRequest("POST", sprintf($url, $userId, $itemId, $filename))
      ->addHeaders(["Content-Type" => "application/json"])
      ->attachBody(['item' => $item])
      ->execute();

    if ($response instanceof GraphResponse) {
      return $response->getBody();
    }
    return null;
  }

  /**
   * Delete Session for Resumable Upload
   * 
   * @param string $uploadUrl Upload URL
   * @return bool
   */
  public function deleteSession($uploadUrl)
  {
    $response = $this->graph
      ->createRequest("DELETE", $uploadUrl)
      ->execute();
    // HTTP/1.1 204 No Content
    return $response->getStatus() == 204;
  }

  /**
   * Upload a File after create session
   * 
   * @param string $uploadUrl Upload URL
   * @param string $filename Filename
   * @return bool
   */
  public function postFile($uploadUrl, $filename)
  {
    $stream = $this->createStream($filename);
    $fileSize = $stream->getSize();

    $headers = [
      'Content-Length'=>$fileSize,
      'Content-Range'=>sprintf('bytes 0-%s/%s', $fileSize-1, $fileSize),
    ];
    $response = $this->sendRequest($uploadUrl, $headers, $stream);

    // HTTP/1.1 200 OK | 201 Created
    return in_array($response->getStatusCode(), [200, 201]);
  }

  /**
   * Resumable Upload by chunks after create session
   * 
   * @param string $uploadUrl Upload URL
   * @param string $filename Filename
   * @return bool
   */
  public function postFileByChunks($uploadUrl, $filename)
  {
    $fileStream = $this->createStream($filename);
    $fileSize = $fileStream->getSize();
    $byteRanges = new ByteRangeCollection($fileSize, $this->limit);
    
    echo sprintf('--- Begin Sending Request %s ...', basename($filename)).PHP_EOL;
    foreach($byteRanges as $byte) {
      $headers = [
        'Content-Length'=>$byte->limit,
        'Content-Range'=>sprintf('bytes %s-%s/%s', $byte->start, $byte->end, $byte->size),
      ];
      echo sprintf('---- Sending Request %s-%s', $byte->start, $byte->end).PHP_EOL;
      $stream = new LimitStream($fileStream, $byte->limit, $byte->start);
      $response = $this->sendRequest($uploadUrl, $headers, $stream);
      $lastStatus = $response->getStatusCode();
    }
    echo '--- Request Sent!'.PHP_EOL;

    // HTTP/1.1 200 OK | 201 Created
    return in_array($lastStatus, [200, 201]);
  }

  /**
   * Create a stream and add validation
   * 
   * @param string $filename Filename
   * @return bool
   */
  protected function createStream($filename)
  {
    $stream = Psr7\stream_for(fopen($filename, 'r+'));
    if ($stream->getSize() >= self::UPLOAD_LIMIT) {
      throw new \yii\base\NotSupportedException("Tidak boleh lebih dari 60mb!", 400);
    }
    return $stream;
  }

  /**
   * Send a PUT Request for upload a file
   * 
   * @param string $uploadUrl Upload URL
   * @param array $headers Array of Headers
   * @param StreamInterface $stream The Stream
   * @return bool
   */
  protected function sendRequest($uploadUrl, $headers, $stream)
  {
    $request = new Request("PUT", $uploadUrl, $headers, $stream);
    return (new Client)->send($request);
  }
}