<?php

namespace Fichier\Service\Storage;

trait StorageAdapterManagerAwareTrait
{
    protected StorageAdapterManager $storageAdapterManager;

    /**
     * @param \Fichier\Service\Storage\StorageAdapterManager $storageAdapterManager
     */
    public function setStorageAdapterManager(StorageAdapterManager $storageAdapterManager): void
    {
        $this->storageAdapterManager = $storageAdapterManager;
    }

}