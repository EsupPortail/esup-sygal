<?php

namespace Import\Model\Service;

use Notification\Service\NotifierService;
use These\Service\Notification\TheseNotificationFactory;
use These\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use UnicaenDbImport\Config\Config;
use Unicaen\Console\Request as ConsoleRequest;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Noop;
use Laminas\Log\Writer\Stream;

class ImportObservResultServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ImportObservResultService
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

        /** @var \These\Service\Notification\TheseNotificationFactory $theseNotificationFactory */
        $theseNotificationFactory = $container->get(TheseNotificationFactory::class);
        $service->setTheseNotificationFactory($theseNotificationFactory);

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