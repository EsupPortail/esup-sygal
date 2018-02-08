<?php

namespace ApplicationUnitTest\Assertion;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\These\TheseEntityAssertion;
use Application\Entity\Db\These;
use Application\Provider\Privilege\ThesePrivileges;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class TheseEntityAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TheseEntityAssertion
     */
    private $assertion;

    protected function setUp()
    {
        $this->assertion = new TheseEntityAssertion();
    }

    public function getCorrectionAutoriseeValeurs()
    {
        return [
            [These::CORRECTION_MINEURE],
            [These::CORRECTION_MAJEURE],
        ];
    }

    /**
     * @dataProvider getCorrectionAutoriseeValeurs
     * @expectedException FailedAssertionException
     * @param string $correctionAutorisee
     */
    public function test_1er_depot_interdit_si_correction_mineure_autorisee($correctionAutorisee)
    {
        $privilege = ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;

        $theseProphecy = $this->theseProphecy();
        $theseProphecy->getCorrectionAutorisee()->willReturn($correctionAutorisee);
        /** @var These $these */
        $these = $theseProphecy->reveal();

        $this->assertion
            ->setThese($these)
            ->assert($privilege);
    }

    /**
     * @return These|ObjectProphecy
     */
    private function theseProphecy()
    {
        $prophet = new Prophet();

        /** @var These|ObjectProphecy $prophecy */
        $prophecy = $prophet->prophesize(These::class);

        return $prophecy;
    }
}
