<?php

namespace ApplicationUnitTest\Controller;

class IndexControllerTest extends AbstractControllerTestCase
{
    public function test_a_propos_action_can_be_accessed()
    {
        $this->dispatch('/apropos');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('UnicaenApp');
        $this->assertControllerName('UnicaenApp\Controller\Application');
        $this->assertControllerClass('ApplicationController');
        $this->assertMatchedRouteName('apropos');
    }
}