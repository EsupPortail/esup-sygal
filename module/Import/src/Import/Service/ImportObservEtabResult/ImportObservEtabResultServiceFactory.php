<?php

namespace Import\Service\ImportObservEtabResult;

use Application\Entity\Db\ImportObservEtabResult;
use Application\Entity\Db\Repository\ImportObservEtabResultRepository;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\Log\Writer\Stream;

class ImportObservEtabResultServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);

        /** @var ImportObservEtabResultRepository $repo */
        $repo = $container->get('doctrine.entitymanager.orm_default')->getRepository(ImportObservEtabResult::class);
        $repo->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);

        /** @var VariableService $variableService */
        $variableService = $container->get('VariableService');

        $service = new ImportObservEtabResultService();
        $service->setRepository($repo);
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