<?php

namespace Fichier\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginatorAdapter;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\StorageAdapterManagerAwareTrait;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;
use Laminas\Paginator\Paginator;
use Webmozart\Assert\Assert;

class ConsoleController extends AbstractConsoleController
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use StorageAdapterManagerAwareTrait;

    /**
     * BROUILLON !
     */
    public function migrerFichiersAction()
    {
        $from = $this->params()->fromRoute('from');
        $to = $this->params()->fromRoute('to');

        Assert::true($this->storageAdapterManager->has($from), "Storage '$from' introuvable");
        Assert::true($this->storageAdapterManager->has($to), "Storage '$to' introuvable");
        $fromStorage = $this->storageAdapterManager->get($from);
        $toStorage = $this->storageAdapterManager->get($to);

        $paginator = $this->createFichiersPaginator();

        $this->console->writeLine("# Migration des fichiers du storage '$from' vers '$to'...");
        $this->fichierStorageService->migrerFichiers($paginator, $fromStorage, $toStorage);
    }

    private function createFichiersPaginator(int $itemCountPerPage = 50): Paginator
    {
        $qb = $this->fichierService->getRepository()->createQueryBuilder('f')
            ->where('histoDestruction is null')
            ->orderBy('id');

        $paginator = new Paginator(new DoctrinePaginator(new DoctrinePaginatorAdapter($qb)));
        $paginator->setItemCountPerPage($itemCountPerPage);

        return $paginator;
    }
}