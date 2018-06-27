<?php

namespace Application\Service\These;

use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Repository\TheseRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Notification\ValidationRdvBuNotification;
use Application\Service\BaseService;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenAuth\Entity\Db\UserInterface;

class TheseService extends BaseService
{
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return TheseRepository
     */
    public function getRepository()
    {
        /** @var TheseRepository $repo */
        $repo = $this->entityManager->getRepository(These::class);

        return $repo;
    }


    /**
     * @param These           $these
     * @param MetadonneeThese $metadonnee
     */
    public function updateMetadonnees(These $these, MetadonneeThese $metadonnee)
    {
        if (! $metadonnee->getId()) {
            $metadonnee->setThese($these);
            $these->addMetadonnee($metadonnee);

            $this->entityManager->persist($metadonnee);
        }

        try {
            $this->entityManager->flush($metadonnee);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These       $these
     * @param Attestation $attestation
     */
    public function updateAttestation(These $these, Attestation $attestation)
    {
        if (! $attestation->getId()) {
            $attestation->setThese($these);
            $these->addAttestation($attestation);

            $this->entityManager->persist($attestation);
        }

        try {
            $this->entityManager->flush($attestation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Supprime l'Attestation (éventuelle) d'une These.
     *
     * @param These         $these Thèse concernée
     * @param UserInterface $destructeur Auteur de l'historisation, le cas échéant
     */
    public function deleteAttestation(These $these, UserInterface $destructeur = null)
    {
        $attestation = $these->getAttestation();
        if ($attestation === null) {
            return;
        }

        if ($destructeur) {
            $attestation->historiser($destructeur);
        } else {
            $these->removeAttestation($attestation);
            $this->entityManager->remove($attestation);
        }

        try {
            $this->entityManager->flush($attestation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These       $these
     * @param Diffusion $mel
     */
    public function updateDiffusion(These $these, Diffusion $mel)
    {
        if (! $mel->getId()) {
            $mel->setThese($these);
            $these->addDiffusion($mel);

            $this->entityManager->persist($mel);
        }

        try {
            $this->entityManager->flush($mel);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Supprime la Diffusion (éventuelle) d'une These.
     *
     * @param These         $these Thèse concernée
     * @param UserInterface $destructeur Auteur de l'historisation, le cas échéant
     */
    public function deleteDiffusion(These $these, UserInterface $destructeur = null)
    {
        $diffusion = $these->getDiffusion();
        if ($diffusion === null) {
            return;
        }

        if ($destructeur) {
            $diffusion->historiser($destructeur);
        } else {
            $these->removeDiffusion($diffusion);
            $this->entityManager->remove($diffusion);
        }

        try {
            $this->entityManager->flush($diffusion);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Recherche le fichier de la version d'archivage de la thèse (corrigée, le cas échéant)
     * et modifie son témoin de conformité.
     *
     * @param These  $these
     * @param string $conforme "1" (conforme), "0" (non conforme) ou null (i.e. pas de réponse)
     */
    public function updateConformiteTheseRetraitee(These $these, $conforme = null)
    {
//        $fichiersVA  = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI,      false);
//        $fichiersVAC = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI_CORR, false);
        $fichiersVA  = $this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI);
        $fichiersVAC = $this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI_CORR);


        /** @var Fichier $fichier */
        if (! empty($fichiersVAC)) {
            $fichier = current($fichiersVAC) ?: null;
        } else {
            $fichier = current($fichiersVA) ?: null;
        }

        // il n'existe pas forcément de fichier en version d'archivage (si la version originale est testée archivable)
        if ($fichier === null) {
            return;
        }

        $fichier->setEstConforme($conforme);

        try {
            $this->entityManager->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These $these
     * @param RdvBu $rdvBu
     */
    public function updateRdvBu(These $these, RdvBu $rdvBu)
    {
        if (! $rdvBu->getId()) {
            $rdvBu->setThese($these);
            $these->addRdvBu($rdvBu);

            $this->entityManager->persist($rdvBu);
        }

        try {
            $this->entityManager->flush($rdvBu);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }

        // si tout est renseigné, on valide automatiquement
        if ($rdvBu->isInfosBuSaisies()) {
            $this->validationService->validateRdvBu($these);
            $successMessage = "Validation enregistrée avec succès.";

            // notification BDD et BU + doctorant (à la 1ere validation seulement)
            $notifierDoctorant = ! $this->validationService->existsValidationRdvBuHistorisee($these);
            $notification = new ValidationRdvBuNotification();
            $notification->setThese($these);
            $notification->setNotifierDoctorant($notifierDoctorant);
            $this->notifierService->triggerValidationRdvBu($notification);
//            $notificationLog = $this->notifierService->getMessage('<br>', 'info');

            $this->addMessage($successMessage, MessageAwareInterface::SUCCESS);
//            $this->addMessage($notificationLog, MessageAwareInterface::INFO);
        }
    }

    public function getTheseEnCoursPostSoutenance()
    {
        $qb = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->addSelect("doctorant")
            ->addSelect("individu")
            ->leftJoin("these.doctorant", "doctorant")
            ->leftJoin("doctorant.individu", "individu")
            ->addSelect("etablissement")
            ->addSelect("ecoleDoctorale")
            ->addSelect("uniteRecherche")
            ->addSelect("structure_etab")
            ->addSelect("structure_ed")
            ->addSelect("structure_ur")
            ->leftJoin("these.etablissement", "etablissement")
            ->leftJoin("etablissement.structure", "structure_etab")
            ->leftJoin("these.ecoleDoctorale", "ecoleDoctorale")
            ->leftJoin("ecoleDoctorale.structure", "structure_ed")
            ->leftJoin("these.uniteRecherche", "uniteRecherche")
            ->leftJoin("uniteRecherche.structure", "structure_ur")
            ->andWhere("these.etatThese = :encours")
            ->andWhere("these.dateSoutenance < :today")
            ->setParameter("encours", These::ETAT_EN_COURS)
            ->setParameter("today", new \DateTime())
            ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getEffectifs($etablissement = null) {

        $date = new \DateTime();
        $annee= $date->format("Y");

        $qb_enCours = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->andWhere("these.etatThese = :encours")
            ->setParameter("encours", These::ETAT_EN_COURS)
            ;
        if ($etablissement) $qb_enCours = $qb_enCours->andWhere("these.etablissement = :etablissement")->setParameter("etablissement", $etablissement);
        $result_enCours = $qb_enCours->getQuery()->getResult();

        $qb_inscriptions = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->andWhere("these.datePremiereInscription >= :debut")
            ->andWhere("these.datePremiereInscription <= :fin")
            ->setParameter("debut", \DateTime::createFromFormat('d/m/Y', "01/01/".$annee))
            ->setParameter("fin", \DateTime::createFromFormat('d/m/Y', "31/12/".$annee))
        ;
        if ($etablissement) $qb_inscriptions = $qb_inscriptions->andWhere("these.etablissement = :etablissement")->setParameter("etablissement", $etablissement);
        $result_inscriptions = $qb_inscriptions->getQuery()->getResult();

        $qb_soutenues = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->andWhere("these.dateSoutenance >= :debut")
            ->andWhere("these.dateSoutenance <= :fin")
            ->setParameter("debut", \DateTime::createFromFormat('d/m/Y', '01/01/'.$annee))
            ->setParameter("fin", \DateTime::createFromFormat('d/m/Y', "31/12/".$annee))
        ;
        if ($etablissement) $qb_soutenues = $qb_soutenues->andWhere("these.etablissement = :etablissement")->setParameter("etablissement", $etablissement);
        $result_soutenues = $qb_soutenues->getQuery()->getResult();

        return [count($result_enCours), count($result_inscriptions), count($result_soutenues)];
    }

    public function getThesesAnciennes($age)
    {
        $date = new \DateTime();
        $mois = $date->format("m") - $age;
        $annee = $date->format("Y") - $age;
        $dateLimite = \DateTime::createFromFormat('d/m/Y', "01/".$mois."/".$annee);

        $qb = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder("these")
            ->addSelect("doctorant")
            ->addSelect("individu")
            ->leftJoin("these.doctorant", "doctorant")
            ->leftJoin("doctorant.individu", "individu")
            ->addSelect("etablissement")
            ->addSelect("ecoleDoctorale")
            ->addSelect("uniteRecherche")
            ->addSelect("structure_etab")
            ->addSelect("structure_ed")
            ->addSelect("structure_ur")
            ->leftJoin("these.etablissement", "etablissement")
            ->leftJoin("etablissement.structure", "structure_etab")
            ->leftJoin("these.ecoleDoctorale", "ecoleDoctorale")
            ->leftJoin("ecoleDoctorale.structure", "structure_ed")
            ->leftJoin("these.uniteRecherche", "uniteRecherche")
            ->leftJoin("uniteRecherche.structure", "structure_ur")
            ->andWhere("these.etatThese = :encours")
            ->andWhere("these.datePremiereInscription <= :date")
            ->setParameter("encours", These::ETAT_EN_COURS)
            ->setParameter("date", $dateLimite)
            ->orderBy("these.datePremiereInscription")
        ;
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}