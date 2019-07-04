<?php

namespace ApplicationUnitTest;

use Application\Service\FichierThese\FichierTheseService;
use Application\Service\UserContextService;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

trait FichierServiceProphetTrait
{
    /**
     * @var Prophet
     */
    protected $prophet;

    /**
     * @var FichierTheseService|ObjectProphecy
     */
    protected $fichierServiceProphecy;

    /**
     * @return $this
     */
    protected function givenSelectedRoleDoctorantEstVide()
    {
        $this->fichierServiceProphecy->getSelectedRoleDoctorant()->willReturn(null);

        return $this;
    }

    /**
     * @return UserContextService
     */
    protected function revealUserContextService()
    {
        /** @var UserContextService $o */
        $o = $this->fichierServiceProphecy->reveal();

        return $o;
    }

    /**
     * @return $this
     */
    protected function prophesizeUserContextService()
    {
        $this->fichierServiceProphecy = $this->prophet->prophesize(UserContextService::class);

        return $this;
    }
}