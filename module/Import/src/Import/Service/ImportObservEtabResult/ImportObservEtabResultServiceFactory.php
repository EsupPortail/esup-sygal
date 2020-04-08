<?php

namespace Import\Service\ImportObservEtabResult;

use Application\Entity\Db\ImportObservEtabResult;
use Application\Entity\Db\Repository\ImportObservEtabResultRepository;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Application\SourceCodeStringHelper;
use Zend\Console\Request as ConsoleRequest;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObservEtabResultServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);

        /** @var ImportObservEtabResultRepository $repo */
        $repo = $sl->get('doctrine.entitymanager.orm_default')->getRepository(ImportObservEtabResult::class);
        $repo->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var TheseService $theseService */
        $theseService = $sl->get('TheseService');

        /** @var NotifierService $notifierService */
        $notifierService = $sl->get(NotifierService::class);

        /** @var VariableService $variableService */
        $variableService = $sl->get('VariableService');

        $service = new ImportObservEtabResultService();
        $service->setRepository($repo);
        $service->setTheseService($theseService);
        $service->setNotifierService($notifierService);
        $service->setVariableService($variableService);

        if ($logger = $this->getLogger($sl)) {
            $service->setLogger($logger);
        }

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @return Logger
     */
    private function getLogger(ServiceLocatorInterface $sl)
    {
        if ($sl->get('request') instanceof ConsoleRequest) {
            $writer = new Stream('php://output');
        } else {
            $writer = new Noop();
        }

        return (new Logger())->addWriter($writer);
    }
}