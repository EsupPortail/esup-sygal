<?php

namespace Application\Service\Rapport\Avis;

use Application\Entity\Db\RapportAvis;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use UnicaenApp\Exception\RuntimeException;

class RapportAvisService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NatureFichierServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(RapportAvis::class);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findRapportAvisById($id): RapportAvis
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('r')
            ->join('ra.rapport', 'r')
            ->where('ra = :id')->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * @param RapportAvis $rapportAvis
     */
    public function deleteRapportAvis(RapportAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }
    }

    public function saveRapportAvis(RapportAvis $rapportAvis)
    {
        $this->entityManager->beginTransaction();

        $rapport = $rapportAvis->getRapport();
        $rapport->addRapportAvis($rapportAvis);

        try {
            $this->entityManager->persist($rapportAvis);
            $this->entityManager->flush($rapportAvis);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", 0, $e);
        }
    }
}