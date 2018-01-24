<?php

namespace ApplicationUnitTest\Controller;

use ApplicationUnitTest\Test\Provider\EntityProvider;
use Doctrine\ORM\EntityManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AbstractControllerTest
 *
 * @package ApplicationTest\Controller
 */
abstract class AbstractControllerTestCase extends \UnicaenTest\Controller\AbstractControllerTestCase
{
    public function setUp()
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            // Grabbing the full application configuration:
            include __DIR__ . '/../../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();
    }

    /**
     * @var EntityProvider
     */
    private $ep;

    public function ep()
    {
        if (null === $this->ep) {
            $config = $this->getApplicationServiceLocator()->get('Config');
            $this->ep = new EntityProvider($this->em(), $config['unicaen-test']);
        }

        return $this->ep;
    }

    /**
     * @param string $name
     * @return EntityManager
     */
    protected function em($name = 'orm_default')
    {
        return $this->getApplicationServiceLocator()->get("doctrine.entitymanager.$name");
    }
}