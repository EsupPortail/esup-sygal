<?php

namespace Formation\Service\Inscription;

use Application\Entity\AnneeUniv;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\InscriptionRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InscriptionService {
    use EntityManagerAwareTrait;
    use AnneeUnivServiceAwareTrait;

    /**
     * @return InscriptionRepository
     */
    public function getRepository() : InscriptionRepository
    {
        /** @var InscriptionRepository $repo */
        $repo = $this->entityManager->getRepository(Inscription::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function create(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->persist($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function update(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function historise(Inscription $seance) : Inscription
    {
        try {
            $seance->historiser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function restore(Inscription $seance) : Inscription
    {
        try {
            $seance->dehistoriser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function delete(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->remove($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /** Querying ******************************************************************************************************/

    /** @return Inscription[] */
    public function getInscriptionByDoctorantAndAnnee(Doctorant $doctorant, ?string $type = null, ?int $annee = null): array
    {
        $qb = $this->getRepository()->createQueryBuilder('inscription')
            ->join('inscription.doctorant', 'doctorant')->addSelect('doctorant')
            ->join('inscription.session', 'session')->addSelect('session')
            ->join('session.formation', 'formation')->addSelect('formation')
            ->join('session.seances', 'seance')->addSelect('seance')
            ->andWhere('inscription.doctorant = :doctorant')->setParameter('doctorant', $doctorant);

        if ($type !== null) {
            $qb->andWhere('formation.type = :type')->setParameter('type', $type);
        }

        if ($annee !== null) {
            $annee = AnneeUniv::fromPremiereAnnee($annee);
            $debut = $this->anneeUnivService->computeDateDebut($annee);
            $fin = $this->anneeUnivService->computeDateFin($annee);;
            $qb
                ->andWhere('seance.debut >= :debut')->setParameter('debut', $debut)
                ->andWhere('seance.fin <= :fin')->setParameter('fin', $fin);
        }
        return $qb->getQuery()->getResult();
    }
}