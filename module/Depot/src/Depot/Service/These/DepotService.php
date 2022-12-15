<?php

namespace Depot\Service\These;

use Application\Entity\Db\Utilisateur;
use Application\Service\AuthorizeServiceAwareTrait;
use Application\Service\BaseService;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Assert\Assertion;
use DateTime;
use Depot\Controller\FichierTheseController;
use Depot\Entity\Db\Attestation;
use Depot\Entity\Db\Diffusion;
use Depot\Entity\Db\MetadonneeThese;
use Depot\Entity\Db\RdvBu;
use Depot\Notification\ValidationRdvBuNotification;
use Depot\Rule\AutorisationDiffusionRule;
use Depot\Rule\SuppressionAttestationsRequiseRule;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Exception\NotificationImpossibleException;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Repository\TheseRepository;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;

class DepotService extends BaseService implements ListenerAggregateInterface
{
    use TheseServiceAwareTrait;
    use ListenerAggregateTrait;
    use DepotValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use UserServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use AuthorizeServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;

    /**
     * Resaisir l'autorisation de diffusion ? Sinon celle saisie au 1er dépôt est reprise/dupliquée.
     * @var bool
     */
    private bool $resaisirAutorisationDiffusionVersionCorrigee = false;

    /**
     * Resaisir les attestations ? Sinon celles saisies au 1er dépôt sont reprises/dupliquées.
     * @var bool
     */
    private bool $resaisirAttestationsVersionCorrigee = false;

    public function setResaisirAutorisationDiffusionVersionCorrigee(bool $resaisirAutorisationDiffusionVersionCorrigee): self
    {
        $this->resaisirAutorisationDiffusionVersionCorrigee = $resaisirAutorisationDiffusionVersionCorrigee;
        return $this;
    }

    public function setResaisirAttestationsVersionCorrigee(bool $resaisirAttestationsVersionCorrigee): self
    {
        $this->resaisirAttestationsVersionCorrigee = $resaisirAttestationsVersionCorrigee;
        return $this;
    }

    public function getResaisirAutorisationDiffusionVersionCorrigee(): bool
    {
        return $this->resaisirAutorisationDiffusionVersionCorrigee;
    }

    public function getResaisirAttestationsVersionCorrigee(): bool
    {
        return $this->resaisirAttestationsVersionCorrigee;
    }

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // réaction à l'événement de dépôt d'un fichier de thèse
        $events->attach(FichierTheseController::FICHIER_THESE_TELEVERSE, [$this, 'onFichierTheseTeleverse']);
    }

    /**
     * @return TheseRepository
     * @deprecated Utiliser directement celui du theseService si besoin !
     */
    public function getRepository() : TheseRepository
    {
        return $this->theseService->getRepository();
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
            $this->depotValidationService->validateRdvBu($these, $this->userContextService->getIdentityIndividu());
            $successMessage = "Validation enregistrée avec succès.";

            // notification BDD et BU + doctorant (à la 1ere validation seulement)
            $notifierDoctorant = ! $this->depotValidationService->existsValidationRdvBuHistorisee($these);
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
     * Transfère toutes les données saisies sur une thèse *historisée* vers une autre thèse.
     *
     * @param These $fromThese Thèse source historisée
     * @param These $toThese   Thèse destination
     */
    public function transferTheseData(These $fromThese, These $toThese)
    {
        if (! $fromThese->estHistorise()) {
            throw new DepotServiceException("La thèse source doit être une thèse historisée.");
        }

        $fromId = $fromThese->getId();
        $toId = $toThese->getId();

        $sql = <<<EOS
DO $$ BEGIN        
    update FICHIER          set THESE_ID = $toId where THESE_ID = $fromId;
    update ATTESTATION      set THESE_ID = $toId where THESE_ID = $fromId;
    update DIFFUSION        set THESE_ID = $toId where THESE_ID = $fromId;
    update METADONNEE_THESE set THESE_ID = $toId where THESE_ID = $fromId;
    update RDV_BU           set THESE_ID = $toId where THESE_ID = $fromId;
    update VALIDATION       set THESE_ID = $toId where THESE_ID = $fromId;
END $$;
EOS;
        try {
            $this->entityManager->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rencontrée lors des updates en bdd.", null, $e);
        }
    }

    /**
     * @param These $these
     * @param \Application\Entity\Db\Utilisateur|null $utilisateur
     * @return array
     * @throws \Notification\Exception\NotificationImpossibleException
     */
    public function notifierCorrectionsApportees(These $these, ?Utilisateur $utilisateur = null): array
    {
        $president = $these->getPresidentJury();
        if ($president === null) {
            throw new NotificationImpossibleException("Aucun président du jury pour la thèse [".$these->getId()."]");
        }

        //Recherche de l'utilisateur  associé à l'individu
        $individu = $president->getIndividu();
        $utilisateurs = $this->getUtilisateurService()->getRepository()->findByIndividu($individu);

        // Notification directe de l'utilisateur déjà existant
        if (!empty($utilisateurs)) {
            $this->getNotifierService()->triggerValidationDepotTheseCorrigee($these, end($utilisateurs));
            return ['success', "Notification des corrections faite à <strong>".end($utilisateurs)->getEmail()."</strong>"];
        }
        else {
            // Recupération du "meilleur" email
            $email = null;
            if ($email === null and $president->getIndividu() !== null and $president->getIndividu()->getEmailPro()) $email = $president->getIndividu()->getEmailPro();
            if ($email === null and $president->getMembre() !== null and $president->getMembre()->getEmail()) $email = $president->getMembre()->getEmail();

            if ($email) {
                // Creation du compte local puis notification (si mail)
                $individu->setEmailPro($email);
                $username = ($individu->getNomUsuel() ?: $individu->getNomPatronymique()) . "_" . $president->getId();
                $user = $this->utilisateurService->createFromIndividu($individu, $username, 'none');
                $token = $this->userService->updateUserPasswordResetToken($user);
                $this->getNotifierService()->triggerInitialisationCompte($user, $token);
                $this->getNotifierService()->triggerValidationDepotTheseCorrigee($these);
                return ['success', "Création de compte initialisée et notification des corrections faite à <strong>" . $email . "</strong>"];
            } else {
                // Echec (si aucun mail, faudra le renseigner dans un membre fictif par exemple)
                $this->getNotifierService()->triggerPasDeMailPresidentJury($these, $president);
                return ['error', "Aucune action de réalisée car aucun email de trouvé."];
            }
        }
    }

    /**
     * Fixe la date butoir de dépôt de la version corrigée de la thèse spécifiée.
     *
     * @param \These\Entity\Db\These $these
     * @param \DateTime|null $dateButoirDepotVersionCorrigeeAvecSursis
     */
    public function updateSursisDateButoirDepotVersionCorrigee(These $these, DateTime $dateButoirDepotVersionCorrigeeAvecSursis = null)
    {
        $these->setDateButoirDepotVersionCorrigeeAvecSursis($dateButoirDepotVersionCorrigeeAvecSursis);

        try {
            $this->entityManager->flush($these);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du sursis", null, $e);
        }
    }
}