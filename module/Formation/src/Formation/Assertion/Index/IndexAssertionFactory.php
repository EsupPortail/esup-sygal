<?php

namespace Formation\Assertion\Index;

use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexAssertionFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndexAssertion
    {
        $messageCollector = $container->get('MessageCollector');

        $assertion = new IndexAssertion();
        $assertion->setServiceMessageCollector($messageCollector);

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);
        return $assertion;
    }
}