<?php

namespace Application\Service\ImportObservResult;

use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObservResultServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var ImportObservResultRepository $repo */
        $repo = $sl->get('doctrine.entitymanager.orm_default')->getRepository(ImportObservResult::class);

        /** @var TheseService $theseService */
        $theseService = $sl->get('TheseService');

        /** @var NotifierService $notifierService */
        $notifierService = $sl->get(NotifierService::class);

        /** @var VariableService $variableService */
        $variableService = $sl->get('VariableService');

        $service = new ImportObservResultService();
        $service->setRepository($repo);
        $service->setTheseService($theseService);
        $service->setNotifierService($notifierService);
        $service->setVariableService($variableService);

        return $service;
    }
}