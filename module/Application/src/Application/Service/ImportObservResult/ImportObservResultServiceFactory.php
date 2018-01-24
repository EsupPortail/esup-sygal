<?php

namespace Application\Service\ImportObservResult;

use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObservResultServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var ImportObservResultRepository $repo */
        $repo = $sl->get('doctrine.entitymanager.orm_default')->getRepository(ImportObservResult::class);

        $service = new ImportObservResultService();
        $service->setRepository($repo);

        return $service;
    }
}