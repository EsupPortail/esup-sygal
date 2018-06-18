<?php

namespace Application\Entity\Db;

use Notification\Entity\NotifEntity;

class ImportObservNotif
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ImportObserv
     */
    private $importObserv;

    /**
     * @var NotifEntity
     */
    private $notif;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ImportObserv $importObserv
     * @return self
     */
    public function setImportObserv($importObserv)
    {
        $this->importObserv = $importObserv;

        return $this;
    }

    /**
     * @return ImportObserv
     */
    public function getImportObserv()
    {
        return $this->importObserv;
    }

    /**
     * @return NotifEntity
     */
    public function getNotif()
    {
        return $this->notif;
    }

    /**
     * @param NotifEntity $notif
     * @return ImportObservNotif
     */
    public function setNotif(NotifEntity $notif)
    {
        $this->notif = $notif;

        return $this;
    }


}
