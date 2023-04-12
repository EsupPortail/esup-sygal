<?php

namespace Fichier\Service\ValiditeFichier;

use Application\Entity\Db\Repository\ValiditeFichierRepository;
use Application\Entity\Db\ValiditeFichier;
use Application\Service\BaseService;
use Depot\Entity\Db\FichierThese;
use Doctrine\ORM\ORMException;

class ValiditeFichierService extends BaseService
{
    /**
     * @return ValiditeFichierRepository
     */
    public function getRepository(): ValiditeFichierRepository
    {
        /** @var ValiditeFichierRepository $repo */
        $repo = $this->entityManager->getRepository(ValiditeFichier::class);
        return $repo;
    }

    /**
     * @param FichierThese $fichierThese
     * @param array        $validiteResult
     * @return ValiditeFichier
     */
    public function createValiditeFichier(FichierThese $fichierThese, array $validiteResult): ValiditeFichier
    {
        $entity = new ValiditeFichier();
        $entity
            ->setFichier($fichierThese->getFichier())
            ->setMessage($validiteResult['message'])
            ->setLog($validiteResult['resultat'])
            ->setHistoCreateur($fichierThese->getThese()->getHistoCreateur()); // indispensable en mode CLI

        $fichierThese->getFichier()->addValidite($entity);

        if ($validiteResult['estValide'] !== null) {
            $entity->setEstValide($validiteResult['estValide']);
        }

        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        } catch (ORMException $e) {
            throw new \RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }

        return $entity;
    }

    /**
     * @param FichierThese $fichierThese
     */
    public function clearValiditesFichier(FichierThese $fichierThese)
    {
        $validites = $fichierThese->getFichier()->getValidites();
        if ($validites->count() === 0) {
            return;
        }

        try {
            foreach ($validites as $validite) {
                $this->entityManager->remove($validite);
            }
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new \RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }
}