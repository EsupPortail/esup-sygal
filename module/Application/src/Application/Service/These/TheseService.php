<?php

namespace Application\Service\These;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Repository\TheseRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Notification\ValidationRdvBuNotification;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\FichierThese\MembreData;
use Application\Service\FichierThese\PdcData;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Assert\Assertion;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenAuth\Entity\Db\UserInterface;

class TheseService extends BaseService
{
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FileServiceAwareTrait;

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
     * Met à jour le témoin de correction autorisée forcée.
     *
     * @param These  $these
     * @param string|null $forcage
     */
    public function updateCorrectionAutoriseeForcee(These $these, $forcage = null)
    {
        if ($forcage !== null) {
            Assertion::inArray($forcage, [
                These::CORRECTION_AUTORISEE_FORCAGE_AUCUNE,
                These::CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE,
                These::CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE,
            ]);
        }

        $these->setCorrectionAutoriseeForcee($forcage);

        try {
            $this->entityManager->flush($these);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
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

    /**
     * Cette fonction a pour vocation de récupérer les informations utile pour la génération de la page de couverture.
     * Si une clef est vide cela indique un problème associé à la thèse
     *
     * @param These $these
     * @return PdcData
     */
    public function fetchInformationsPageDeCouverture(These $these)
    {
        $pdcData = new PdcData();

        /** informations générales */
        $pdcData->setTitre($these->getTitre());
        $pdcData->setSpecialite($these->getLibelleDiscipline());
        if ($these->getEtablissement()) $pdcData->setEtablissement($these->getEtablissement()->getLibelle());
        if ($these->getDoctorant()) {
            $pdcData->setDoctorant($these->getDoctorant()->getIndividu()->getNomComplet(false, false, false, true, true, true));
        }
        if ($these->getDateSoutenance()) $pdcData->setDate($these->getDateSoutenance()->format("d/m/Y"));

        /** cotutelle */
        $pdcData->setCotutuelle(false);
        if ($these->getLibelleEtabCotutelle() !== null && $these->getLibelleEtabCotutelle() !== "") {
            $pdcData->setCotutuelle(true);
            $pdcData->setCotutuelleLibelle($these->getLibelleEtabCotutelle());
            if ($these->getLibellePaysCotutelle()) $pdcData->setCotutuellePays($these->getLibellePaysCotutelle());
        }



        /** Jury de thèses */
        $acteurs = $these->getActeurs()->toArray();

        $jury = array_filter($acteurs, function (Acteur $a) {
           return $a->estMembreDuJury();
        });

        $rapporteurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estRapporteur();
        });
        $pdcData->setRapporteurs($rapporteurs);
        $directeurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estDirecteur();
        });
        $pdcData->setDirecteurs($directeurs);
        $codirecteurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estCodirecteur();
        });
        $pdcData->setCodirecteurs($codirecteurs);
        $president = array_filter($acteurs, function (Acteur $a) {
            return $a->estPresidentJury();
        });

        $membres = array_diff($acteurs, $rapporteurs, $directeurs, $codirecteurs, $president);
        $pdcData->setMembres($membres);

        /** associée */
        $pdcData->setAssocie(false);
        /** @var Acteur $directeur */
        foreach ($directeurs as $directeur) {
            if (!$directeur->getEtablissement()) {
                throw new RuntimeException("Anomalie: le directeur de thèse '{$directeur}' n'a pas d'établissement.");
            }
            if ($directeur->getEtablissement()->estAssocie()) {
                $pdcData->setAssocie(true);
                $pdcData->setLogoAssocie($this->fileService->computeLogoFilePathForStructure($directeur->getEtablissement()));
                $pdcData->setLibelleAssocie($directeur->getEtablissement()->getLibelle());
            }
        }

        $acteursEnCouverture = array_merge($rapporteurs, $directeurs, $codirecteurs, $president, $membres);
        usort($acteursEnCouverture, Acteur::getComparisonFunction());
        $acteursEnCouverture = array_unique($acteursEnCouverture);

        /** @var Acteur $acteur */
        foreach ($acteursEnCouverture as $acteur) {
            $acteurData = new MembreData();
            $acteurData->setDenomination($acteur->getIndividu()->getNomComplet(true, false, false, true, true));
            $acteurData->setQualite($acteur->getQualite());

            if (!$acteur->estPresidentJury()) {
                $acteurData->setRole($acteur->getRole()->getLibelle());

                //patch rapporteur non membre ...
                $estMembre = !empty(array_filter($jury, function (Acteur $a) use ($acteur) {return $a->getIndividu() === $acteur->getIndividu();}));
                if ($acteur->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY && !$estMembre) {
                    $acteurData->setRole("Rapporteur non membre du jury");
                }
            } else {
                $acteurData->setRole(Role::LIBELLE_PRESIDENT);
            }
            if ($acteur->getEtablissement()) $acteurData->setEtablissement($acteur->getEtablissement()->getStructure()->getLibelle());
            $pdcData->addActeurEnCouverture($acteurData);
        }

        /** Directeurs de thèses */
        $nomination = [];
        foreach ($directeurs as $directeur) {
            $nomination[] = $directeur->getIndividu()->getNomComplet(false, false, false, true, true);
        }
        foreach ($codirecteurs as $directeur) {
            $nomination[] = $directeur->getIndividu()->getNomComplet(false, false, false, true, true);
        }
        $pdcData->setListing(implode(" et ", $nomination) . ", ");
        if ($these->getUniteRecherche()) $pdcData->setUniteRecherche($these->getUniteRecherche()->getStructure()->getLibelle());

        // chemins vers les logos
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $pdcData->setLogoCOMUE($this->fileService->computeLogoFilePathForStructure($comue));
        }
        $pdcData->setLogoEtablissement($this->fileService->computeLogoFilePathForStructure($these->getEtablissement()));
        if ($these->getEcoleDoctorale() !== null) {
            $pdcData->setLogoEcoleDoctorale($this->fileService->computeLogoFilePathForStructure($these->getEcoleDoctorale()));
        }
        if ($these->getUniteRecherche() !== null) {
            $pdcData->setLogoUniteRecherche($this->fileService->computeLogoFilePathForStructure($these->getUniteRecherche()));
        }

        return $pdcData;
    }

    /**
     * Transfère toutes les données saisies sur une thèse *historisée* vers une autre thèse.
     *
     * @param These $fromThese Thèse source historisée
     * @param These $toThese   Thèse destination
     */
    public function transferTheseData(These $fromThese, These $toThese)
    {
        if (! $fromThese->estHistorise()) {
            throw new TheseServiceException("La thèse source doit être une thèse historisée.");
        }

        $fromId = $fromThese->getId();
        $toId = $toThese->getId();

        $sql = <<<EOS
begin        
    update FICHIER          set THESE_ID = $toId where THESE_ID = $fromId;
    update ATTESTATION      set THESE_ID = $toId where THESE_ID = $fromId;
    update DIFFUSION        set THESE_ID = $toId where THESE_ID = $fromId;
    update METADONNEE_THESE set THESE_ID = $toId where THESE_ID = $fromId;
    update RDV_BU           set THESE_ID = $toId where THESE_ID = $fromId;
    update VALIDATION       set THESE_ID = $toId where THESE_ID = $fromId;
end;
EOS;
        try {
            $this->entityManager->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rencontrée lors des updates en bdd.", null, $e);
        }
    }
}