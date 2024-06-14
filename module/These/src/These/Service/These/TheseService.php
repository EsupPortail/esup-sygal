<?php

namespace These\Service\These;

use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Doctrine\ORM\ORMException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\Repository\TheseRepository;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\FichierThese\MembreData;
use These\Service\FichierThese\PdcData;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;

class TheseService extends BaseService //implements ListenerAggregateInterface
{
    use SourceServiceAwareTrait;
    use ListenerAggregateTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use UserServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;

    /**
     * @return TheseRepository
     */
    public function getRepository(): TheseRepository
    {
        /** @var TheseRepository $repo */
        $repo = $this->entityManager->getRepository(These::class);

        return $repo;
    }

    public function newThese() : These
    {
        $these = new These();
        $these->setSource($this->sourceService->fetchApplicationSource());
        $these->setSourceCode($this->sourceService->genereateSourceCode());

        return $these;
    }

    public function saveThese(These $these, string $domaine)
    {
        switch ($domaine) {
            case 'generalites':
                $this->saveTheseGeneralites($these);
                break;
            case 'direction':
                $this->saveTheseDirection($these);
                break;
            case 'structures':
                $this->saveTheseStructures($these);
                break;
            case 'encadrement':
                $this->saveTheseEncadrement($these);
                break;
        }
    }

    private function saveTheseGeneralites(These $these)
    {

    }

    private function saveTheseDirection(These $these)
    {
        /** @var Acteur[] $direction */
        $direction = $these->getActeursByRoleCode([
            Role::CODE_DIRECTEUR_THESE,
            Role::CODE_CODIRECTEUR_THESE,
        ]);

        foreach ($direction as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
                $this->getEntityManager()->flush($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }
    }

    private function saveTheseStructures(These $these)
    {

    }

    private function saveTheseEncadrement(These $these)
    {

    }

    public function create(These $these): These
    {
        try {
            $this->getEntityManager()->persist($these);
            $this->getEntityManager()->flush($these);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $these;
    }

    public function update(These $these): These
    {
        try {
            $this->getEntityManager()->flush($these);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
    }

    /**
     * Met à jour le témoin de correction autorisée forcée.
     *
     * @param These  $these
     * @param string|null $forcage
     */
    public function updateCorrectionAutoriseeForcee(These $these, string $forcage = null)
    {
        if ($forcage !== null) {
            Assertion::inArray($forcage, [
                These::CORRECTION_AUTORISEE_FORCAGE_AUCUNE,
                These::CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE,
                These::CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE,
            ]);
        }

        $these->setCorrectionAutoriseeForcee($forcage);

        // s'il n'y a plus de correction attendue, effacement du sursis éventuel
        if (! $these->getCorrectionAutorisee()) {
            $these->unsetDateButoirDepotVersionCorrigeeAvecSursis();
        }

        try {
            $this->entityManager->flush($these);
        } catch (ORMException $e) {
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
     * @param Event $event
     */
    public function onFichierTheseTeleverse(Event $event)
    {
        /** @var These $these */
        /** @var VersionFichier $version */
        /** @var NatureFichier $nature */
        $these = $event->getTarget();
        $version = $event->getParam('version');

        if ($version->estVersionOriginale() && $version->estVersionCorrigee()) {
            $this->onFichierTheseTeleverseVersionCorrigee($these);
        }
    }

    /**
     * Lors du dépôt d'une version originale corrigée, selon la config de l'appli, il peut être nécessaire de créer
     * automatiquement Diffusion et Attestation.
     *
     * @param These $these
     */
    private function onFichierTheseTeleverseVersionCorrigee(These $these)
    {
        // Création automatique d'une Diffusion pour la version corrigée si l'utilisateur n'a pas le privilège d'en saisir une.
        if (! $this->resaisirAutorisationDiffusionVersionCorrigee) {
            $this->createDiffusionVersionCorrigee($these);
        }
        // Création automatique d'une Attestation pour la version corrigée si l'utilisateur n'a pas le privilège d'en saisir une.
        if (! $this->resaisirAttestationsVersionCorrigee) {
            $this->createAttestationVersionCorrigee($these);
        }
    }

    /**
     * Création automatique d'une Attestation pour la version corrigée, identique à celle du 1er dépôt.
     *
     * @param These $these
     */
    public function createAttestationVersionCorrigee(These $these)
    {
        $versionOrig = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
        $attestationOrig = $these->getAttestationForVersion($versionOrig);
        if ($attestationOrig === null) {
            throw new LogicException("Aucune Attestation trouvée pour le 1er dépôt");
        }

        $versionCorr = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG_CORR);
        $attestationCorr = $these->getAttestationForVersion($versionCorr);
        if ($attestationCorr !== null) {
            return; // Attestation existante
        }

        $attestationCorr = clone $attestationOrig;
        $attestationCorr->setVersionCorrigee(true);
        $attestationCorr->setCreationAuto(true);
        $this->updateAttestation($these, $attestationCorr);
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
     * Supprime définitivement l'éventuelle Attestation concernant une version de fichier.
     *
     * @param These          $these Thèse concernée
     * @param VersionFichier $version
     */
    public function deleteAttestationForVersion(These $these, VersionFichier $version)
    {
        $attestation = $these->getAttestationForVersion($version);
        if ($attestation === null) {
            return;
        }

        $these->removeAttestation($attestation);

        try {
            $this->entityManager->remove($attestation);
            $this->entityManager->flush($attestation);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression", null, $e);
        }
    }

    /**
     * Création automatique d'une Diffusion pour la version corrigée, identique à celle du 1er dépôt.
     *
     * @param These $these
     */
    public function createDiffusionVersionCorrigee(These $these)
    {
        $versionOrig = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
        $diffusionOrig = $these->getDiffusionForVersion($versionOrig);
        if ($diffusionOrig === null) {
            throw new LogicException("Aucune Diffusion trouvée pour le 1er dépôt");
        }

        $versionCorr = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG_CORR);
        $diffusionCorr = $these->getDiffusionForVersion($versionCorr);
        if ($diffusionCorr !== null) {
            return; // Diffusion existante
        }

        $diffusionCorr = clone $diffusionOrig;
        $diffusionCorr->setVersionCorrigee(true);
        $diffusionCorr->setCreationAuto(true);
        $this->updateDiffusion($these, $diffusionCorr, $versionCorr);
    }

    /**
     * @param These          $these
     * @param Diffusion      $diffusion
     * @param VersionFichier $version
     */
    public function updateDiffusion(These $these, Diffusion $diffusion, VersionFichier $version)
    {
        $isUpdate = $diffusion->getId() !== null;

        if ($isUpdate) {
            // on teste si la réponse à l'autorisation de diffusion existante a changé de manière "importante"
            // (auquel cas, il sera nécessaire de tester s'il faut supprimer ou pas les attestations)
            $rule = new AutorisationDiffusionRule();
            $rule->setDiffusion($diffusion)->execute();
            $suppressionAttestationsAVerifier = $rule->computeChangementDeReponseImportant($this->entityManager);
        } else {
            $suppressionAttestationsAVerifier = false;
        }

        if (! $isUpdate) {
            $diffusion->setThese($these);
            $these->addDiffusion($diffusion);
        }

        try {
            if (! $isUpdate) {
                $this->entityManager->persist($diffusion);
            }
            $this->entityManager->flush($diffusion);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }

        //
        // Il peut être nécessaire de supprimer les "attestations" existantes en fonction des réponses
        // à l'autorisation de diffusion.
        //
        if ($suppressionAttestationsAVerifier) {
            $rule = new SuppressionAttestationsRequiseRule($these, $version);
            $suppressionRequise = $rule->execute();

            if ($suppressionRequise) {
                $this->deleteAttestationForVersion($these, $version);
            }
        }
    }

    /**
     * Détermine d'après la réponse à l'autorisation de diffusion de la thèse si le flag de remise de
     * l'exemplaire papier est pertinent ou non.
     *
     * @param These          $these
     * @param VersionFichier $version
     * @return boolean
     */
    public function isRemiseExemplairePapierRequise(These $these, VersionFichier $version)
    {
        $diffusion = $these->getDiffusionForVersion($version);

        if ($diffusion === null) {
            throw new LogicException("Appel de méthode prématuré : autorisation de diffusion introuvable pour la $version");
        }

        return $diffusion->isRemiseExemplairePapierRequise();
    }

    /**
     * Détermine d'après la réponse à l'autorisation de diffusion de la thèse si le flag de remise de
     * l'exemplaire papier est pertinent ou non.
     *
     * @param These $these
     * @return boolean|null
     */
    public function isExemplPapierFourniPertinent(These $these)
    {
        // le RDV BU ne concerne que le dépôt de la version initiale
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        $diffusion = $these->getDiffusionForVersion($version);

        if ($diffusion === null) {
            return false;
        }

        return $diffusion->isRemiseExemplairePapierRequise();
    }

    /**
     * Détermine si les infos qui doivent être saisies pour le RDV BU l'ont été.
     *
     * @param These $these
     * @return bool
     */
    public function isInfosBuSaisies(These $these)
    {
        $rdvBu = $these->getRdvBu();

        if ($rdvBu === null) {
            return false;
        }

        $exemplairePapierFourniPertinent = $this->isExemplPapierFourniPertinent($rdvBu->getThese());

        return
            (!$exemplairePapierFourniPertinent || $exemplairePapierFourniPertinent && $rdvBu->getExemplPapierFourni()) &&
            $rdvBu->getConventionMelSignee() && $rdvBu->getMotsClesRameau() &&
            $rdvBu->isVersionArchivableFournie();
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
        if ($this->isInfosBuSaisies($these)) {
            $this->validationService->validateRdvBu($these, $this->userContextService->getIdentityIndividu());
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
    public function fetchInformationsPageDeCouverture(These $these): PdcData
    {
        $pdcData = new PdcData();
        $propositions = $these->getPropositions()->toArray();
        /** @var Proposition $proposition */
        $proposition = end($propositions);

        if ($these->getDateSoutenance() !== null) {
            $mois = (int)$these->getDateSoutenance()->format('m');
            $annee = (int)$these->getDateSoutenance()->format('Y');

            if ($mois > 9) $anneeUniversitaire = $annee . "/" . ($annee + 1);
            else            $anneeUniversitaire = ($annee - 1) . "/" . $annee;
            $pdcData->setAnneeUniversitaire($anneeUniversitaire);
        }

        /** informations générales */
        $titre = trim($these->getTitre());
        $titre = str_replace("\n", ' ', $titre);
        $pdcData->setTitre($titre);
        $pdcData->setSpecialite($these->getLibelleDiscipline());
        if ($these->getEtablissement()) {
            $pdcData->setEtablissement($these->getEtablissement()->getStructure()->getLibelle());
        }
        if ($these->getDoctorant()) {
            $pdcData->setDoctorant(mb_strtoupper($these->getDoctorant()->getIndividu()->getNomComplet(false, true, false, true)));
        }
        if ($these->getDateSoutenance()) $pdcData->setDate($these->getDateSoutenance()->format("d/m/Y"));

        /** cotutelle */
        $pdcData->setCotutuelle(false);
        if ($these->getLibelleEtabCotutelle() !== null && $these->getLibelleEtabCotutelle() !== "") {
            $pdcData->setCotutuelle(true);
            $pdcData->setCotutuelleLibelle($these->getLibelleEtabCotutelle());
            if ($these->getLibellePaysCotutelle()) $pdcData->setCotutuellePays($these->getLibellePaysCotutelle());
        }

        /** Huis Clos */
        if ($proposition and $proposition->isHuitClos()) {
            $pdcData->setHuisClos(true);
        } else {
            $pdcData->setHuisClos(false);
        }

        /** confidentialité */
        $pdcData->setDateFinConfidentialite($these->getDateFinConfidentialite());
        /** Jury de thèses */
        $acteurs = $these->getActeursNonHistorises()->toArray();

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
        $coencadrants = array_filter($acteurs, function (Acteur $a) {
            return $a->estCoEncadrant();
        });
        $pdcData->setCoencadrants($coencadrants);
        $president = array_filter($acteurs, function (Acteur $a) {
            return $a->estPresidentJury();
        });
        $coencadrants = array_filter($acteurs, function (Acteur $a) {
            return $a->estCoEncadrant();
        });

        $rapporteurs = array_diff($rapporteurs, $president);
        $membres = array_diff($acteurs, $rapporteurs, $directeurs, $codirecteurs, $president);
        $pdcData->setMembres($membres);

        $jury = array_filter($acteurs, function (Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_MEMBRE_JURY;
        });
        $pdcData->setJury($jury);

        /** associée */
        $pdcData->setAssocie(false);
        /** @var Acteur $directeur */
        foreach (array_merge($directeurs, $codirecteurs) as $directeur) {
            if ($directeur->getEtablissement()) {
                if ($directeur->getEtablissement()->estAssocie()) {
                    $pdcData->setAssocie(true);
                    try {
                        $pdcData->setLogoAssocie($this->fichierStorageService->getFileForLogoStructure($directeur->getEtablissement()->getStructure()));
                    } catch (StorageAdapterException $e) {
                        $pdcData->setLogoAssocie(null);
                    }
                    $pdcData->setLibelleAssocie($directeur->getEtablissement()->getStructure()->getLibelle());
                }
            }
        }

        $acteursEnCouverture = array_merge($rapporteurs, $directeurs, $codirecteurs, $president, $membres);
        usort($acteursEnCouverture, Acteur::getComparisonFunction());
        $acteursEnCouverture = array_unique($acteursEnCouverture);

        /** @var Acteur $acteur */
        foreach ($acteursEnCouverture as $acteur) {
            $individu = $acteur->getIndividu();

            $acteursLies = array_filter($these->getActeurs()->toArray(), function (Acteur $a) use ($individu) {
                return $a->getIndividu() === $individu;
            });

            $acteurData = new MembreData();
            $acteurData->setDenomination(mb_strtoupper($acteur->getIndividu()->getNomComplet(true, false, false, true)));


            $estMembre = !empty(array_filter($jury, function (Acteur $a) use ($acteur) {
                return $a->getIndividu() === $acteur->getIndividu();
            }));

            /** GESTION DES RÔLES SPÉCIAUX ****************************************************************************/
            if (!$acteur->estPresidentJury()) {
                $acteurData->setRole($acteur->getRole()->getLibelle());

                //patch rapporteur non membre ...
                if ($acteur->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY && !$estMembre) {
                    $acteurData->setRole("Rapporteur non membre du jury");
                }
            } else {
                $acteurData->setRole(Role::LIBELLE_PRESIDENT);
            }

            /** GESTION DES QUALITES **********************************************************************************/
            if ($acteur->getQualite() && trim($acteur->getQualite()) !== '') {
                $acteurData->setQualite($acteur->getQualite());
            } else {
                foreach ($acteursLies as $acteurLie) {
                    $membre = $this->getMembreService()->getMembreByActeur($acteurLie);
                    if ($membre) {
                        $acteurData->setQualite($membre->getQualite()->getLibelle());
                        break;
                    }
                }
            }

            /** GESTION DES ETABLISSEMENTS ****************************************************************************/
            if ($etab = ($acteur->getEtablissementForce() ?: $acteur->getEtablissement())) {
                $acteurData->setEtablissement((string)$etab);
            } else {
                foreach ($acteursLies as $acteurLie) {
                    $membre = $this->getMembreService()->getMembreByActeur($acteurLie);
                    if ($membre) {
                        $acteurData->setEtablissement($membre->getEtablissement());
                        break;
                    }
                }
            }

            if ($estMembre) $pdcData->addActeurEnCouverture($acteurData);
        }

        /** Directeurs de thèses */
        $listing = [];
        foreach ($directeurs as $directeur) {
            $current = mb_strtoupper($directeur->getIndividu()->getNomComplet(false, false, false, true));
            $structure = $directeur->getUniteRecherche() ?: $these->getUniteRecherche();
            if ($structure !== null) $structure = $structure->getStructure()->getLibelle();
            $listing[] = ['individu' => $current, 'structure' => $structure];
        }
        foreach ($codirecteurs as $directeur) {
            $current = mb_strtoupper($directeur->getIndividu()->getNomComplet(false, false, false, true));
            $structure = $directeur->getUniteRecherche() ?: $directeur->getEtablissementForce() ?: $directeur->getEtablissement();
            if ($structure !== null) $structure = $structure->getStructure()->getLibelle();
            $listing[] = ['individu' => $current, 'structure' => $structure];
        }
        $pdcData->setListingDirection($listing);
        if ($these->getUniteRecherche()) $pdcData->setUniteRecherche($these->getUniteRecherche()->getStructure()->getLibelle());
        if ($these->getEcoleDoctorale()) $pdcData->setEcoleDoctorale($these->getEcoleDoctorale()->getStructure()->getLibelle());

        // chemins vers les logos
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $pdcData->setLogoCOMUE($this->fichierStorageService->getFileForLogoStructure($comue->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoCOMUE(null);
            }
        }
        try {
            $pdcData->setLogoEtablissement($this->fichierStorageService->getFileForLogoStructure($these->getEtablissement()->getStructure()));
        } catch (StorageAdapterException $e) {
            $pdcData->setLogoEtablissement(null);
        }
        if ($these->getEcoleDoctorale() !== null) {
            try {
                $pdcData->setLogoEcoleDoctorale($this->fichierStorageService->getFileForLogoStructure($these->getEcoleDoctorale()->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoEcoleDoctorale(null);
            }
        }
        if ($these->getUniteRecherche() !== null) {
            try {
                $pdcData->setLogoUniteRecherche($this->fichierStorageService->getFileForLogoStructure($these->getUniteRecherche()->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoUniteRecherche(null);
            }
        }

        return $pdcData;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return These
     */
    public function getRequestedThese(AbstractActionController $controller, string $param = 'these')
    {
        $id = $controller->params()->fromRoute($param);

        /** @var These $these */
        $these = $this->getRepository()->find($id);
        return $these;
    }

    /** PREDICATS *****************************************************************************************************/

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isDoctorant(These $these, Individu $individu): bool
    {
        return ($these->getDoctorant()->getIndividu() === $individu);
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isActeur(These $these, Individu $individu, ?array $roles = null): bool
    {
        return ($these->getDoctorant()->getIndividu() === $individu);
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isDirecteur(These $these, Individu $individu): bool
    {
        $directeurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, 'D');
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu() === $individu) return true;
        }
        return false;
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isCoDirecteur(These $these, Individu $individu): bool
    {
        $directeurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, 'K');
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu() === $individu) return true;
        }
        return false;
    }


    public function assertAppartenanceThese(These $these, UserContextService $userContextService): void
    {
        $role = $userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $doctorant = $userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        } elseif ($roleEcoleDoctorale = $userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        } elseif ($roleUniteRech = $userContextService->getSelectedRoleUniteRecherche()) {
            $this->assertTrue(
                $these->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'UR " . $roleUniteRech->getStructure()->getCode()
            );
        } elseif ($userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        } elseif ($userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas codirigée par " . $individuUtilisateur
            );
        }
    }
}