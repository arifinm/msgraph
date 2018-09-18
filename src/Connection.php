<?php

namespace pkpudev\graph;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Attachment;
use Microsoft\Graph\Model\Drive;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\File;
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

  public function getUsers($search, $limit = 10)
  {
    $url = "/users?\$top=%s&\$filter=startswith(displayName,'%s')";
    $users = $this->graph
      ->createRequest("GET", sprintf($url, $limit, $search))
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

  public function getDrives($userId, $limit=10)
  {
    $drives = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drives?$top=%s', $userId, $limit))
      ->setReturnType(Drive::class)
      ->execute();
    return $drives;
  }

  public function getFolders($userId, $path, $limit=10)
  {
    $folders = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drive/root:/%s:/children?$top=%s', $userId, $path, $limit))
      ->setReturnType(DriveItem::class)
      ->execute();
    return $folders;
  }

  public function getFiles($userId, $path, $limit=10)
  {
    $files = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drive/root:/%s:/children?$top=%s', $userId, $path, $limit))
      ->setReturnType(File::class)
      ->execute();
    return $files;
  }
}