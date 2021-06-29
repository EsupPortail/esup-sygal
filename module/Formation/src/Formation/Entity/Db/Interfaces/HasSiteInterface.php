<?php

namespace Formation\Entity\Db\Interfaces;

use Application\Entity\Db\Etablissement;

interface HasSiteInterface {

    public function getSite() : ?Etablissement;
    public function setSite(?Etablissement $etablissement) : HasSiteInterface;

}