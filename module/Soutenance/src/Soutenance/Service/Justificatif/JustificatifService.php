<?php

namespace Soutenance\Service\Justificatif;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Soutenance\Entity\Justificatif;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class JustificatifService {
    use EntityManagerAwareTrait;

    /**
     * @param string $champ
     * @param string $order
     * @return Justificatif[]
     */
    public function getJustificatifs($champ = 'id', $order = 'ASC')
    {
        $qb = $this->getEntityManager()->getRepository(Justificatif::class)->createQueryBuilder('justificatif')
            ->orderBy('justificatif.'. $champ, $order)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getJustificatif($id)
    {
        $qb = $this->getEntityManager()->getRepository(Justificatif::class)->createQueryBuilder('justificatif')
            ->andWhere('justificatif.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Justificatif partagent le même identifiant [".$id."].", $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Justificatif
     */
    public function getRequestedJustificatif($controller, $paramName = 'justificatif')
    {
        $id = $controller->params()->fromRoute($paramName);
        $justificatif = $this->getJustificatif($id);
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function create($justificatif)
    {
        try {
            $this->getEntityManager()->persist($justificatif);
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function update($justificatif)
    {
        try {
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function delete($justificatif)
    {
        try {
            $this->getEntityManager()->remove($justificatif->getFichier());
            $this->getEntityManager()->remove($justificatif);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

}