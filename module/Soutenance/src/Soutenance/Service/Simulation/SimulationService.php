<?php

namespace Soutenance\Service\Simulation;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class SimulationService {
    use EntityManagerAwareTrait;
    use ActeurServiceAwareTrait;
    use IndividuServiceAwareTrait;

    CONST SIMULATION_SOURCE = 6;

    /**
     * @param These $these
     * @return Acteur[]
     */
    public function getActeursSimules($these = null) {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('source')->join ('acteur.source', 'source')
            ->addSelect('individu')->join ('acteur.individu', 'individu')
            ->addSelect('these')->join ('acteur.these', 'these')
            ->andWhere('source.id = :ID')
            ->setParameter('ID', SimulationService::SIMULATION_SOURCE)
            ->orderBy('acteur.these, individu.nomUsuel, individu.prenom1', 'ASC')
        ;

        if ($these !== null) {
            $qb = $qb->andWhere('these = :these')
                ->setParameter('these', $these)
            ;
        }

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Acteur
     */
    public function getRequestedActeurSimule($controller, $paramName = 'acteur')
    {
        $id = $controller->params()->fromRoute($paramName);
        /** @var Acteur $acteur */
        $acteur = $this->getActeurService()->getRepository()->find($id);
        return $acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function delete($acteur) {
        try {
            $this->getEntityManager()->remove($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base", $e);
        }
        return $acteur;
    }

    public function update(Acteur $acteur)
    {
        try {
            $this->getEntityManager()->flush($acteur->getIndividu());
            $this->getEntityManager()->flush($acteur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base", $e);
        }
        return $acteur;
    }

    public function create(Acteur $acteur)
    {
        try {
            $this->getEntityManager()->persist($acteur->getIndividu());
            $this->getEntityManager()->flush($acteur->getIndividu());
            $this->getEntityManager()->persist($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base", $e);
        }
        return $acteur;
    }

}