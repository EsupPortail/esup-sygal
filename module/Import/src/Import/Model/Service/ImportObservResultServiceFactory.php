<?php

namespace Import\Model\Service;

use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Import\Model\Service\ImportObservResultService;
use Interop\Container\ContainerInterface;
use UnicaenDbImport\Config\Config;
use Zend\Console\Request as ConsoleRequest;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\Log\Writer\Stream;

class ImportObservResultServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var Config $config */
        $config = $container->get(Config::class);

        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);

        /** @var VariableService $variableService */
        $variableService = $container->get('VariableService');

        $service = new ImportObservResultService();
        $service->setEntityManager($em);
        $service->setEntityClass($config->getImportObservResultEntityClass());
        $service->setTheseService($theseService);
        $service->setNotifierService($notifierService);
        $service->setVariableService($variableService);

        if ($logger = $this->getLogger($container)) {
            $service->setLogger($logger);
        }

        return $service;
    }

    /**
     * @param ContainerInterface $container
     * @return Logger
     */
    private function getLogger(ContainerInterface $container)
    {
        if ($container->get('request') instanceof ConsoleRequest) {
            $writer = new Stream('php://output');
        } else {
            $writer = new Noop();
        }

        return (new Logger())->addWriter($writer);
    }
}