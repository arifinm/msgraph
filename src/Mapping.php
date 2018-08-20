<?php

namespace pkpudev\graph;

use Microsoft\Graph\Model\FileAttachment;

class Mapping
{
  public static function AttachmentToContent(FileAttachment $attachment)
  {
    $base64content = $attachment->getContentBytes()->getContents();
    return base64_decode($base64content);
  }
}