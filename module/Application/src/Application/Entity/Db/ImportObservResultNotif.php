<?php

namespace Application\Entity\Db;

use Notification\Entity\NotifResultEntity;

class ImportObservResultNotif
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ImportObservResult
     */
    private $importObservResult;

    /**
     * @var NotifResultEntity
     */
    private $notifResult;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ImportObserv $importObservResult
     * @return self
     */
    public function setImportObservResult($importObservResult)
    {
        $this->importObservResult = $importObservResult;

        return $this;
    }

    /**
     * @return ImportObservResult
     */
    public function getImportObservResult()
    {
        return $this->importObservResult;
    }

    /**
     * @return NotifResultEntity
     */
    public function getNotifResult()
    {
        return $this->notifResult;
    }

    /**
     * @param NotifResultEntity $notifResult
     * @return ImportObservResultNotif
     */
    public function setNotifResult(NotifResultEntity $notifResult)
    {
        $this->notifResult = $notifResult;

        return $this;
    }


}
