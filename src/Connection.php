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

/**
 * Connection from Ms Graph Api
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class Connection
{
  /**
   * @var Graph Graph object
   */
  protected $graph;

  /**
   * Class Constructor
   * 
   * @param string $token Access Token
   * @return void
   */
  public function __construct($token)
  {
    $this->graph = new Graph;
    $this->graph->setAccessToken($token);
  }

  /**
   * Get Users with search params
   * 
   * @param string $search Search Param
   * @param int $limit Search Limit, Default to 10
   * @return User[] List of Users
   */
  public function getUsers($search, $limit = 10)
  {
    $url = "/users?\$top=%s&\$filter=startswith(displayName,'%s')";
    $users = $this->graph
      ->createRequest("GET", sprintf($url, $limit, $search))
      ->setReturnType(User::class)
      ->execute();
    return $users;
  }

  /**
   * Get Messages/Mails from a user by userId
   * 
   * @param string $userId User ID
   * @param int $limit Search Limit, Default to 10
   * @return Message[] List of Messages
   */
  public function getMessages($userId, $limit = 10)
  {
    $messages = $this->graph
      ->createRequest("GET", sprintf('/users/%s/mailFolders/inbox/messages?$top=%s', $userId, $limit))
      ->setReturnType(Message::class)
      ->execute();
    return $messages;
  }

  /**
   * Get Attachments from a message by msgId
   * 
   * @param string $userId User ID
   * @param string $msgId Message ID
   * @return Attachment[] List of Attachments
   */
  public function getAttachments($userId, $msgId)
  {
    $attachments = $this->graph
      ->createRequest("GET", sprintf('/users/%s/messages/%s/attachments', $userId, $msgId))
      ->setReturnType(Attachment::class)
      ->execute();
    return $attachments;
  }

  /**
   * Get FileAttachment from a message by attachmentId
   * 
   * @param string $userId User ID
   * @param string $msgId Message ID
   * @param string $attachmentId Attachment ID
   * @return FileAttachment The Attachment File
   */
  public function getFileAttachment($userId, $msgId, $attachmentId)
  {
    $fileAttachment = $this->graph
      ->createRequest("GET", sprintf('/users/%s/messages/%s/attachments/%s', $userId, $msgId, $attachmentId))
      ->setReturnType(FileAttachment::class)
      ->execute();
    return $fileAttachment;
  }

  /**
   * Remove a Message to Deleted Items folder
   * 
   * @param string $userId User ID
   * @param string $msgId Message ID
   * @return Message The Deleted Message
   */
  public function deleteMessage($userId, $msgId)
  {
    $message = $this->graph
      ->createRequest("POST", sprintf('/users/%s/messages/%s/move', $userId, $msgId))
      ->attachBody(['destinationId'=>'deletedItems'])
      ->setReturnType(Message::class)
      ->execute();
    return $message;
  }

  /**
   * Get Drives from a user by userId
   * 
   * @param string $userId User ID
   * @param int $limit Search Limit, Default to 10
   * @return Drive[] List of Drives
   */
  public function getDrives($userId, $limit=10)
  {
    $drives = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drives?$top=%s', $userId, $limit))
      ->setReturnType(Drive::class)
      ->execute();
    return $drives;
  }

  /**
   * Get Folders from a user by path
   * 
   * @param string $userId User ID
   * @param string $path Folder Location
   * @param int $limit Search Limit, Default to 10
   * @return DriveItem[] List of DriveItems
   */
  public function getFolders($userId, $path, $limit=10)
  {
    $folders = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drive/root:/%s:/children?$top=%s', $userId, $path, $limit))
      ->setReturnType(DriveItem::class)
      ->execute();
    return $folders;
  }

  /**
   * Get Files from a user by path
   * 
   * @param string $userId User ID
   * @param string $path Folder Location
   * @param int $limit Search Limit, Default to 10
   * @return File[] List of Files
   */
  public function getFiles($userId, $path, $limit=10)
  {
    $files = $this->graph
      ->createRequest("GET", sprintf('/users/%s/drive/root:/%s:/children?$top=%s', $userId, $path, $limit))
      ->setReturnType(File::class)
      ->execute();
    return $files;
  }
}