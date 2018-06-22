<?php

namespace ApplicationUnitTest;

use Application\Service\UserContextService;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

trait UserContextServiceProphetTrait
{
    /**
     * @var Prophet
     */
    protected $prophet;

    /**
     * @var UserContextService|ObjectProphecy
     */
    protected $userContextServiceProphecy;

    /**
     * @return $this
     */
    protected function givenSelectedRoleDoctorantEstVide()
    {
        $this->userContextServiceProphecy->getSelectedRoleDoctorant()->willReturn(null);

        return $this;
    }

    /**
     * @return UserContextService
     */
    protected function revealUserContextService()
    {
        /** @var UserContextService $o */
        $o = $this->userContextServiceProphecy->reveal();

        return $o;
    }

    /**
     * @return $this
     */
    protected function prophesizeUserContextService()
    {
        $this->userContextServiceProphecy = $this->prophet->prophesize(UserContextService::class);

        return $this;
    }
}