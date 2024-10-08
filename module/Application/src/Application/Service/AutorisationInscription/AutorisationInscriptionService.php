<?php

namespace Application\Service\AutorisationInscription;

use Application\Entity\Db\AutorisationInscription;
use Application\Entity\Db\Rapport;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\BaseService;
use DateInterval;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class AutorisationInscriptionService extends BaseService
{
    use AnneeUnivServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(AutorisationInscription::class);
    }

    public function createQueryBuilder() : QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('autorisationInscription');
    }

    public function create(AutorisationInscription $autorisationInscription) : AutorisationInscription
    {
        try {
            $this->getEntityManager()->persist($autorisationInscription);
            $this->getEntityManager()->flush($autorisationInscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $autorisationInscription;
    }

    /**
     * @param AutorisationInscription $autorisationInscription
     * @return AutorisationInscription
     */
    public function update(AutorisationInscription $autorisationInscription) : AutorisationInscription
    {
        try {
            $this->getEntityManager()->flush($autorisationInscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $autorisationInscription;
    }

    /**
     * @param AutorisationInscription $autorisationInscription
     * @return AutorisationInscription
     */
    public function historise(AutorisationInscription $autorisationInscription) : AutorisationInscription
    {
        try {
            $autorisationInscription->historiser();
            $this->getEntityManager()->flush($autorisationInscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $autorisationInscription;
    }

    /**
     * @param AutorisationInscription $autorisationInscription
     * @return AutorisationInscription
     */
    public function restore(AutorisationInscription $autorisationInscription) : AutorisationInscription
    {
        try {
            $autorisationInscription->dehistoriser();
            $this->getEntityManager()->flush($autorisationInscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $autorisationInscription;
    }

    /**
     * @param AutorisationInscription $autorisationInscription
     * @return AutorisationInscription
     */
    public function delete(AutorisationInscription $autorisationInscription) : AutorisationInscription
    {
        try {
            $this->getEntityManager()->remove($autorisationInscription);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $autorisationInscription;
    }

    /**
     * @param These $these
     * @return array
     */
    public function findAutorisationsInscriptionParThese(These $these): array
    {
        return $this->getRepository()->findBy(['these' => $these]);
    }

    public function initAutorisationInscriptionFromRapport(Rapport $rapport): AutorisationInscription
    {
        $these = $rapport->getThese();
        $anneeUniv = $rapport->getAnneeUniv();
        $dateDebutAnneeUniv = $this->anneeUnivService->computeDateDebut($anneeUniv);
        $prochaineAnneeUniv = $this->anneeUnivService->fromDate($dateDebutAnneeUniv->add(new DateInterval('P1Y1M')));

        $autorisationInscription = new AutorisationInscription();
        $autorisationInscription->setThese($these);
        $autorisationInscription->setIndividu($these->getDoctorant()->getIndividu());
        $autorisationInscription->setRapport($rapport);
        $autorisationInscription->setAnneeUniv($prochaineAnneeUniv->getPremiereAnnee());

        return $autorisationInscription;
    }
}