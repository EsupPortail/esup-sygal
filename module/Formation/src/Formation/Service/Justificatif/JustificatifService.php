<?php

namespace Formation\Service\Justificatif;

use Doctrine\ORM\Exception\ORMException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Justificatif;
use Formation\Entity\Db\JustificatifSeance;
use Formation\Entity\Db\Repository\JustificatifRepository;
use Formation\Entity\Db\Seance;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class JustificatifService
{
    use EntityManagerAwareTrait;
    use PresenceServiceAwareTrait;

    /**
     * @return JustificatifRepository
     */
    public function getRepository(): JustificatifRepository
    {
        /** @var JustificatifRepository $repo */
        $repo = $this->entityManager->getRepository(Justificatif::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Justificatif $justificatif
     * @param Seance[] $seances
     * @return Justificatif
     */
    public function create(Justificatif $justificatif, array $seances): Justificatif
    {
        try {
            foreach ($seances as $seance) {
                $justificatifSeance = new JustificatifSeance();
                $justificatifSeance->setJustificatif($justificatif);
                $justificatifSeance->setSeance($seance);

                $justificatif->addJustificatifSeance($justificatifSeance);
                $this->getEntityManager()->persist($justificatifSeance);
            }
            $this->getEntityManager()->persist($justificatif);
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [Justificatif]", 0, $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function update(Justificatif $justificatif): Justificatif
    {
        try {
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Justificatif]", 0, $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function historise(Justificatif $justificatif): Justificatif
    {
        try {
            $justificatif->historiser();
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Justificatif]", 0, $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function restore(Justificatif $justificatif): Justificatif
    {
        try {
            $justificatif->dehistoriser();
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Justificatif]", 0, $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function delete(Justificatif $justificatif): Justificatif
    {
        try {
            $this->getEntityManager()->remove($justificatif);
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Justificatif]", 0, $e);
        }
        return $justificatif;
    }

    public function getSeancesAbsencesInjustifieesByInscription(Inscription $inscription): array|null
    {
        $session = $inscription->getSession();
        $seances = $session->getSeances()->toArray();
        $seances = array_filter($seances, function (Seance $a) { return $a->estNonHistorise();});

        $seancesInjustifiees = null;
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $presence = $this->presenceService->getRepository()->findPresenceByInscriptionAndSeance($inscription, $seance);
            if ($presence === null || !$presence->isPresent()) {
                $justificatif = $this->getRepository()->findJustificatifByInscriptionAndSeance($inscription, $seance);
                if($justificatif === null) $seancesInjustifiees[] = $seance;
            }
        }

        return $seancesInjustifiees;
    }

    public function isInscriptionHasAbsenceInjustifiee(Inscription $inscription): bool
    {
        $seancesInjustifiees = $this->getSeancesAbsencesInjustifieesByInscription($inscription);
        return is_array($seancesInjustifiees) && count($seancesInjustifiees) > 0;
    }
}