<?php

namespace DepotUnitTest;

use These\Entity\Db\These;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

trait TheseProphetTrait
{
    /**
     * @var Prophet
     */
    protected $prophet;

    /**
     * @var These|ObjectProphecy
     */
    protected $theseProphecy;

    /**
     * @return $this
     */
    protected function givenCorrectionAttendue()
    {
        // Une correction est attendue lorsque $these->getCorrectionAutorisee() retourne une valeur non vide
        $this->theseProphecy->getCorrectionAutorisee()->willReturn('non vide');
        return $this;
    }

    /**
     * @return $this
     */
    protected function givenCorrectionNonAttendue()
    {
        // Aucune correction n'est attendue lorsque $these->getCorrectionAutorisee() retourne une valeur vide
        $this->theseProphecy->getCorrectionAutorisee()->willReturn(null);
        return $this;
    }

    /**
     * @return These
     */
    protected function revealThese()
    {
        /** @var These $these */
        $these = $this->theseProphecy->reveal();

        return $these;
    }

    /**
     * @return $this
     */
    protected function prophesizeThese()
    {
        $this->theseProphecy = $this->prophet->prophesize(These::class);

        return $this;
    }
}