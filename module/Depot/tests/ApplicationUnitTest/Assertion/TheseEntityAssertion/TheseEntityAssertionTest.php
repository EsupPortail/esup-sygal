<?php

namespace DepotUnitTest\Assertion\TheseEntityAssertion;

use Application\Assertion\Exception\FailedAssertionException;
use These\Assertion\These\TheseEntityAssertion;
use Depot\Provider\Privilege\DepotPrivileges;
use ApplicationUnitTest\TheseProphetTrait;
use ApplicationUnitTest\UserContextServiceProphetTrait;
use Prophecy\Prophet;

class TheseEntityAssertionTest extends \PHPUnit_Framework_TestCase
{
    use TheseProphetTrait;
    use UserContextServiceProphetTrait;

    /**
     * @var TheseEntityAssertion
     */
    private $assertion;

    protected function setUp()
    {
        $this->prophet = new Prophet();
        $this->assertion = new TheseEntityAssertion();
        $this->prophesizeThese();
        $this->prophesizeUserContextService();
    }

    /**
     * THESE_DEPOT_VERSION_INITIALE
     */

    public function test_assertion_interdit_1er_depot_si_correction_attendue()
    {
        $this->givenCorrectionAttendue();

        $this->expectException(FailedAssertionException::class);

        $this->assertion
            ->setThese($this->revealThese())
            ->assert(DepotPrivileges::THESE_DEPOT_VERSION_INITIALE);
    }

    public function test_assertion_autorise_1er_depot_si_aucune_correction_attendue()
    {
        $this->givenCorrectionNonAttendue();
        $this->givenSelectedRoleDoctorantEstVide();

        $res = $this->assertion
            ->setThese($this->revealThese())
            ->setUserContextService($this->revealUserContextService())
            ->assert(DepotPrivileges::THESE_DEPOT_VERSION_INITIALE);

        $this->assertTrue($res);
    }

    /**
     * THESE_DEPOT_VERSION_CORRIGEE
     */



}
