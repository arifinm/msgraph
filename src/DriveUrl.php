<?php

namespace pkpudev\graph;

use Microsoft\Graph\Model\DriveItem;

class DriveUrl extends DriveItem
{
    public function getDownloadUrl()
    {
        if (array_key_exists("@microsoft.graph.downloadUrl", $this->_propDict)) {
            return $this->_propDict["@microsoft.graph.downloadUrl"];
        } else {
            return null;
        }
    }
}
