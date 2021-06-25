<?php

namespace Formation\Entity;

use Doctrine\Common\Collections\Collection;

class Session {

    /** @var int */
    private $id;

    /** @var Action */
    private $action;

    /** @var Collection (Seance) */
    private $seances;

}