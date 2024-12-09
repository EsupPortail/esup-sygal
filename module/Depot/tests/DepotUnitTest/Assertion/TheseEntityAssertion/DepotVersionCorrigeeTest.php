<?php

namespace DepotUnitTest\Assertion\TheseEntityAssertion;

use These\Assertion\These\TheseEntityAssertion;
use DepotUnitTest\TheseProphetTrait;
use ApplicationUnitTest\UserContextServiceProphetTrait;
use Prophecy\Prophet;

class DepotVersionCorrigeeTest extends \PHPUnit\Framework\TestCase
{
    use TheseProphetTrait;
    use UserContextServiceProphetTrait;

    /**
     * @var TheseEntityAssertion
     */
    private $assertion;

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
        $this->assertion = new TheseEntityAssertion();
        $this->prophesizeThese();
        $this->prophesizeUserContextService();
    }


}
