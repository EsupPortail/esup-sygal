<?php

namespace TheseUnitTest\Assertion\TheseEntityAssertion;

use These\Assertion\These\TheseEntityAssertion;
use ApplicationUnitTest\TheseProphetTrait;
use ApplicationUnitTest\UserContextServiceProphetTrait;
use Prophecy\Prophet;

class DepotVersionCorrigeeTest extends \PHPUnit_Framework_TestCase
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


}
