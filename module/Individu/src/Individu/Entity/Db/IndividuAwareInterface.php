<?php

namespace Individu\Entity\Db;

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