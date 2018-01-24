<?php

namespace Application\Service\ValiditeFichier;

use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\Repository\ValiditeFichierRepository;
use Application\Service\BaseService;

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
        return $this->entityManager->getRepository(ValiditeFichier::class);
    }

    /**
     * @param Fichier $fichier
     * @param array   $validiteResult
     * @return ValiditeFichier
     */
    public function createValiditeFichier(Fichier $fichier, array $validiteResult)
    {
        $entity = new ValiditeFichier();
        $entity
            ->setFichier($fichier)
            ->setMessage($validiteResult['message'])
            ->setLog($validiteResult['resultat']);

        $fichier->addValidite($entity);

        if ($validiteResult['estArchivable'] !== null) {
            $entity->setEstValide($validiteResult['estArchivable']);
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);

        return $entity;
    }

    /**
     * @param Fichier $fichier
     */
    public function clearValiditesFichier(Fichier $fichier)
    {
        $validites = $fichier->getValidites();
        if ($validites->count() === 0) {
            return;
        }

        foreach ($validites as $validite) {
            $this->entityManager->remove($validite);
        }

        $this->entityManager->flush();
    }
}