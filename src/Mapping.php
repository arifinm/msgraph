<?php

namespace pkpudev\graph;

use Microsoft\Graph\Model\FileAttachment;

/**
 * Mapping Class From Ms Graph Model
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class Mapping
{
  /**
   * Convert FileAttachment content to a string
   * 
   * @param FileAttachment $attachment File Attachment
   * @return string The Content
   */
  public static function AttachmentToContent(FileAttachment $attachment)
  {
    $base64content = $attachment->getContentBytes()->getContents();
    return base64_decode($base64content);
  }
}