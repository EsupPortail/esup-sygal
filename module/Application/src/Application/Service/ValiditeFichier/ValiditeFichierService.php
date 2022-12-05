<?php

namespace Application\Service\ValiditeFichier;

use Application\Entity\Db\Repository\ValiditeFichierRepository;
use Application\Entity\Db\ValiditeFichier;
use Application\Service\BaseService;
use These\Entity\Db\FichierThese;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 26/04/16
 * Time: 09:07
 */
class ValiditeFichierService extends BaseService
{
    /**
     * @return ValiditeFichierRepository
     */
    public function getRepository()
    {
        /** @var ValiditeFichierRepository $repo */
        $repo = $this->entityManager->getRepository(ValiditeFichier::class);
        return $repo;
    }

    /**
     * @param FichierThese $fichierThese
     * @param array        $validiteResult
     * @return ValiditeFichier
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createValiditeFichier(FichierThese $fichierThese, array $validiteResult)
    {
        $entity = new ValiditeFichier();
        $entity
            ->setFichier($fichierThese->getFichier())
            ->setMessage($validiteResult['message'])
            ->setLog($validiteResult['resultat'])
            ->setHistoCreateur($fichierThese->getThese()->getHistoCreateur()); // indispensable en mode CLI

        $fichierThese->getFichier()->addValidite($entity);

        if ($validiteResult['estArchivable'] !== null) {
            $entity->setEstValide($validiteResult['estArchivable']);
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);

        return $entity;
    }

    /**
     * @param FichierThese $fichierThese
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function clearValiditesFichier(FichierThese $fichierThese)
    {
        $validites = $fichierThese->getFichier()->getValidites();
        if ($validites->count() === 0) {
            return;
        }

        foreach ($validites as $validite) {
            $this->entityManager->remove($validite);
        }

        $this->entityManager->flush();
    }
}