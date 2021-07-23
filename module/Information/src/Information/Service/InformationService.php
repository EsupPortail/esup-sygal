<?php

namespace Information\Service;

use Information\Entity\Db\Information;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InformationService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @param bool $visibleOnly
     * @return Information[]
     */
    public function getInformations(bool $visibleOnly = false)
    {
        $qb = $this->getEntityManager()->getRepository(Information::class)->createQueryBuilder('information')
            ->orderBy('information.priorite', 'DESC')
        ;

        if ($visibleOnly)
            $qb = $qb->andWhere('information.visible = true');

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param integer $id
     * @return Information
     */
    public function getInformation($id)
    {
        $qb = $this->getEntityManager()->getRepository(Information::class)->createQueryBuilder('information')
            ->andWhere('information.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Information partagent le même identifiant [".$id."]", $e);
        }
        return $result;
    }

    /**
     * @param Information $information
     * @return Information
     */
    public function create($information)
    {
        try {
            $date = new \DateTime();
        } catch (\Exception $e) {
            throw new RuntimeException("Problème de récupération de la date lors de la création en base d'une Information", $e);
        }
        $user = $this->userContextService->getIdentityDb();
        $information->setHistoCreation($date);
        $information->setHistoCreateur($user);
        $information->setHistoModification($date);
        $information->setHistoModificateur($user);
        if ($information->getPriorite() === null) $information->setPriorite(Information::DEFAULT_PRIORITE);
        if ($information->isVisible() === null) $information->setVisible(true);
        $this->getEntityManager()->persist($information);
        try {
            $this->getEntityManager()->flush($information);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de la création en base d'une Information", $e);
        }
        return $information;
    }

    /**
     * @param Information $information
     * @return Information
     */
    public function update($information)
    {
        try {
            $date = new \DateTime();
        } catch (\Exception $e) {
            throw new RuntimeException("Problème de récupération de la date lors de la mise à jour en base d'une Information", $e);
        }
        $user = $this->userContextService->getIdentityDb();
        $information->setHistoModification($date);
        $information->setHistoModificateur($user);
        try {
            $this->getEntityManager()->flush($information);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de la mise à jour en base d'une Information", $e);
        }
        return $information;
    }

    /**
     * @param Information $information
     */
    public function delete($information)
    {
        $this->getEntityManager()->remove($information);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de la destruction en base d'une Information", $e);
        }
    }
}