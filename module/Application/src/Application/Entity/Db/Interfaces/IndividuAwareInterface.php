<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\Individu;

interface IndividuAwareInterface
{
    /**
     * @return Individu
     */
    public function getIndividu();

    /**
     * @param Individu|null $individu
     */
    public function setIndividu(Individu $individu = null);
}