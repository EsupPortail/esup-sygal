<?php

namespace ApplicationUnitTest\Assertion;

use Application\Assertion\These\TheseEntityAssertion;
use Application\Entity\Db\These;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Service\UserContextService;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class TheseEntityAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TheseEntityAssertion
     */
    private $assertion;

    /**
     * @var Prophet
     */
    private $prophet;

    /**
     * @var These
     */
    private $these;

    /**
     * @var These|ObjectProphecy
     */
    private $theseProphecy;

    /**
     * @var UserContextService|ObjectProphecy
     */
    private $userContextServiceProphecy;

    /**
     *
     */
    protected function setUp()
    {
        $this->prophet = new Prophet();
        $this->assertion = new TheseEntityAssertion();
        $this->theseProphecy = $this->theseProphecy();
        $this->userContextServiceProphecy = $this->userContextServiceProphecy();
    }

    /**
     * @expectedException \Application\Assertion\Exception\FailedAssertionException
     */
    public function test_interdit_1er_depot_si_correction_attendue()
    {
        // Une correction est attendue lorsque $these->getCorrectionAutorisee() retourne une valeur non vide
        $this->theseProphecy->getCorrectionAutorisee()->willReturn('non vide');
        $these = $this->theseProphecy->reveal();

        $this->assertion
            ->setThese($these)
            ->assert(ThesePrivileges::THESE_DEPOT_VERSION_INITIALE);
    }

    public function test_autorise_1er_depot_si_aucune_correction_attendue()
    {
        // Aucune correction n'est attendue lorsque $these->getCorrectionAutorisee() retourne une valeur vide
        $this->theseProphecy->getCorrectionAutorisee()->willReturn(null);
        /** @var These $these */
        $these = $this->theseProphecy->reveal();

        $this->userContextServiceProphecy->getSelectedRoleDoctorant()->willReturn('non vide');
        /** @var UserContextService $userContextService */
        $userContextService = $this->userContextServiceProphecy->reveal();

        $res = $this->assertion
            ->setThese($these)
            ->setUserContextService($userContextService)
            ->assert(ThesePrivileges::THESE_DEPOT_VERSION_INITIALE);

        $this->assertTrue($res);
    }

    /**
     * @return These|ObjectProphecy
     */
    private function theseProphecy()
    {
        /** @var These|ObjectProphecy $prophecy */
        $prophecy = $this->prophet->prophesize(These::class);

        return $prophecy;
    }

    /**
     * @return UserContextService|ObjectProphecy
     */
    private function userContextServiceProphecy()
    {
        /** @var These|ObjectProphecy $prophecy */
        $prophecy = $this->prophet->prophesize(UserContextService::class);

        return $prophecy;
    }
}
