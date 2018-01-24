<?php

namespace Application\Entity\Db;

use Notification\Entity\Notif;

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
     * @var Notif
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
     * @return Notif
     */
    public function getNotif()
    {
        return $this->notif;
    }

    /**
     * @param Notif $notif
     * @return ImportObservNotif
     */
    public function setNotif(Notif $notif)
    {
        $this->notif = $notif;

        return $this;
    }


}
