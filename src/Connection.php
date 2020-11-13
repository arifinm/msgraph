<?php

namespace pkpudev\graph;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Drive;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\File;
use Microsoft\Graph\Model\Permission;
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
     * Get User with search params
     *
     * @param string $search Search Param
     * @return User[] List
     */
    public function getUser($search)
    {
        $url = "/users?\$filter=startswith(mail,'%s')";
        $user = $this->graph
            ->createRequest("GET", sprintf($url, $search))
            ->setReturnType(User::class)
            ->execute();

        return $user;
    }

    /**
     * Get List Drives from a user by userId
     *
     * @param string $userId User ID
     * @param int $limit Search Limit, Default to 10
     * @return Drive[] List
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
     * Get a File from a user by userId
     *
     * @param string $userId User ID
     * @param string $path Path Folder
     * @return File[] List
     */
    public function getFile($userId, $path)
    {
        $file = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/root:/%s', $userId, $path))
            ->setReturnType(DriveItem::class)
            ->execute();

        return $file;
    }

    /**
     * Get a Folder By Id from a user by User ID and Item ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @return Folder[] List
     */
    public function getFolderById($userId, $itemId)
    {
        $folder = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/items/%s', $userId, $itemId))
            ->setReturnType(DriveItem::class)
            ->execute();

        return $folder;
    }

    /**
     * Get a Folder By Path from a user by User ID and Path
     *
     * @param string $userId User ID
     * @param string $path Path to folder
     * @return Folder[] List
     */
    public function getFolderByPath($userId, $path)
    {
        $folder = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/root:/%s', $userId, $path))
            ->setReturnType(DriveItem::class)
            ->execute();

        return $folder;
    }

    /**
     * Get Root Folder from a user by User ID
     *
     * @param string $userId User ID
     * @return Folder[] List
     */
    public function getRootFolders($userId)
    {
        $folders = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/root/children?filter=folder ne null', $userId))
            ->setReturnType(DriveItem::class)
            ->execute();

        return $folders;
    }

    /**
     * Get List Files from a user by User ID and Path
     *
     * @param string $userId User ID
     * @param string $path Path Folder
     * @return Files[] List
     */
    public function getListFiles($userId, $path)
    {
        $files = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/root:/%s:/children', $userId, $path))
            ->setReturnType(File::class)
            ->execute();

        return $files;
    }

    /**
     * Delete a File from a user by User ID and Item ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     */
    public function deleteFile($userId, $itemId)
    {
        $file = $this->graph
            ->createRequest("DELETE", sprintf('/users/%s/drive/items/%s', $userId, $itemId))
            ->execute();
    }

    /**
     * Download a File from a user by User ID and Item ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @return File[] List
     */
    public function downloadFile($userId, $itemId)
    {
        $file = $this->graph
            ->createRequest("GET", sprintf('/users/%s/drive/items/%s?select=@microsoft.graph.downloadUrl', $userId, $itemId))
            ->setReturnType(DriveUrl::class)
            ->execute();

        return $file;
    }

    /**
     * Get a Permission of File from a user by User ID, Item ID, Permission ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @param string $permId Permission ID
     * @return Permission[] List
     */
    public function getPermission($userId, $itemId, $permId)
    {
        if (!is_null($permId)) {
            $endpoint = sprintf('/users/%s/drive/items/%s/permissions/%s', $userId, $itemId, $permId);
        } else {
            $endpoint = sprintf('/users/%s/drive/items/%s/permissions', $userId, $itemId);
        }

        $permission = $this->graph
            ->createRequest("GET", $endpoint)
            ->setReturnType(Permission::class)
            ->execute();

        return $permission;
    }

    /**
     * Set a Permission of File from a user by User ID, Item ID, Recipients, Options
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @param Array $recipients Recipients
     * @param Array $options Options
     * @return Permission[] List
     */
    public function invite($userId, $itemId, $recipients, $options)
    {
        $recipients = array_map(function ($recipient) {
            return [
                '@odata.type' => 'microsoft.graph.driveRecipient',
                'email' => $recipient,
            ];
        }, $recipients);

        $recipient = [
            'recipients' => $recipients,
        ];

        $body = array_merge($recipient, $options);

        $permission = $this->graph
            ->createRequest("POST", sprintf('/users/%s/drive/items/%s/invite', $userId, $itemId))
            ->attachBody($body)
            ->setReturnType(Permission::class)
            ->execute();

        return $permission;
    }

    /**
     * Create a Link Permission of File from a user by User ID, Item ID, Options
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @param Array $options Options
     * @return Permission[] List
     */
    public function createLink($userId, $itemId, $options)
    {
        $permission = $this->graph
            ->createRequest("POST", sprintf('/users/%s/drive/items/%s/createLink', $userId, $itemId))
            ->attachBody($options)
            ->setReturnType(Permission::class)
            ->execute();

        return $permission;
    }

    /**
     * Update a Permission of File from a user by User ID, Item ID, Permission ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @param string $permId Permission ID
     * @return Permission[] List
     */
    public function updatePermission($userId, $itemId, $permId)
    {
        $item = [
            "roles" => ["read"],
        ];

        $permission = $this->graph
            ->createRequest("PATCH", sprintf('/users/%s/drive/items/%s/permissions/%s', $userId, $itemId, $permId))
            ->attachBody($item)
            ->setReturnType(Permission::class)
            ->execute();

        return $permission;
    }

    /**
     * Delete a Permission of File from a user by User ID, Item ID, Permission ID
     *
     * @param string $userId User ID
     * @param string $itemId Item ID
     * @param string $permId Permission ID
     */
    public function deletePermission($userId, $itemId, $permId)
    {
        $permission = $this->graph
            ->createRequest("DELETE", sprintf('/users/%s/drive/items/%s/permissions/%s', $userId, $itemId, $permId))
            ->execute();

        $status = $permission->getStatus();

        if ($status == 204) {
            echo 'Deleted'.PHP_EOL;
        }
    }
}
