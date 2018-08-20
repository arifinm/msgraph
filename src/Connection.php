<?php

namespace pkpudev\graph;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Attachment;
use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\User;

class Connection
{
  protected $graph;

  public function __construct($token)
  {
    $this->graph = new Graph;
    $this->graph->setAccessToken($token);
  }

  public function getUsers($limit = 10)
  {
    $users = $this->graph
      ->createRequest("GET", '/users?$top='.$limit)
      ->setReturnType(User::class)
      ->execute();
    return $users;
  }

  public function getMessages($userId, $limit = 10)
  {
    $messages = $this->graph
      ->createRequest("GET", sprintf('/users/%s/mailFolders/inbox/messages?$top=%s', $userId, $limit))
      ->setReturnType(Message::class)
      ->execute();
    return $messages;
  }

  public function getAttachments($userId, $msgId)
  {
    $attachments = $this->graph
      ->createRequest("GET", sprintf('/users/%s/messages/%s/attachments', $userId, $msgId))
      ->setReturnType(Attachment::class)
      ->execute();
    return $attachments;
  }

  public function getFileAttachment($userId, $msgId, $attachmentId)
  {
    $fileAttachment = $this->graph
      ->createRequest("GET", sprintf('/users/%s/messages/%s/attachments/%s', $userId, $msgId, $attachmentId))
      ->setReturnType(FileAttachment::class)
      ->execute();
    return $fileAttachment;
  }

  public function deleteMessage($userId, $msgId)
  {
    $message = $this->graph
      ->createRequest("POST", sprintf('/users/%s/messages/%s/move', $userId, $msgId))
      ->attachBody(['destinationId'=>'deletedItems'])
      ->setReturnType(Message::class)
      ->execute();
    return $message;
  }
}