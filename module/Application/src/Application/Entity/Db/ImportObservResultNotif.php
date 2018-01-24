<?php

namespace Application\Entity\Db;

use Notification\Entity\NotifResult;

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
     * @var NotifResult
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
     * @return NotifResult
     */
    public function getNotifResult()
    {
        return $this->notifResult;
    }

    /**
     * @param NotifResult $notifResult
     * @return ImportObservResultNotif
     */
    public function setNotifResult(NotifResult $notifResult)
    {
        $this->notifResult = $notifResult;

        return $this;
    }


}
