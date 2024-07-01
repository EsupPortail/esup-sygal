<?php

namespace Depot\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\ValiditeFichier;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Depot\Entity\Db\FichierThese;
use Depot\Notification\ChangementCorrectionAttendueNotification;
use Depot\Notification\ValidationDepotTheseCorrigeeNotification;
use Depot\Notification\ValidationPageDeCouvertureNotification;
use Depot\Notification\ValidationRdvBuNotification;
use Depot\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Fichier\Entity\Db\VersionFichier;
use Import\Model\ImportObservResult;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Exception\RuntimeException;
use Notification\Notification;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Options\ModuleOptions;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class DepotNotificationFactory extends \Notification\Factory\NotificationFactory
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EmailTheseServiceAwareTrait;

    protected UrlHelper $urlHelper;

    private ModuleOptions $appModuleOptions;

    public function setUrlHelper(UrlHelper $urlHelper): void
    {
        $this->urlHelper = $urlHelper;
    }

    public function setAppModuleOptions(ModuleOptions $options): void
    {
        $this->appModuleOptions = $options;
    }

    public function initNotification(Notification $notification): void
    {
        parent::initNotification($notification);

        // injecte le nom de l'appli dans la variable 'appName' utilisée par tous les templates
        $appInfos = $this->appModuleOptions->getAppInfos();
        $appName = $appInfos['nom'];
        $notification->setTemplateVariables([
            'appName' => $appName,
        ]);
    }

    /**
     * Notifie que le retraitement automatique du fichier PDF est terminé.
     *
     * @param string               $destinataires        Emails séparés par une virgule
     * @param FichierThese         $fichierTheseRetraite Fichier retraité concerné
     * @param ValiditeFichier|null $validite             Résultat du test d'archivabilité éventuel
     * @return Notification
     */
    public function createNotificationForRetraitementFini(
        string $destinataires,
        FichierThese $fichierTheseRetraite,
        ValiditeFichier $validite = null): Notification
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Retraitement terminé")
            ->setTo($to)
            ->setTemplatePath('depot/depot/mail/notif-retraitement-fini')
            ->setTemplateVariables([
                'fichierRetraite' => $fichierTheseRetraite,
                'validite' => $validite,
                'url' => '',
            ]);

        return $notif;
    }

    /**
     * Notification à l'issu du remplissage du formulaire RDV BU par le doctorant.
     *
     * @param These $these
     * @param bool $estLaPremiereSaisie
     * @return Notification
     */
    public function createNotificationForRdvBuSaisiParDoctorant(These $these, bool $estLaPremiereSaisie): Notification
    {
        $subject = sprintf("%s Saisie des informations pour la prise de rendez-vous avec la bibliothèque universitaire", $these->getLibelleDiscipline());
        $to = $this->emailTheseService->fetchEmailAspectsBibliotheque($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setSubject($subject)
            ->setTemplatePath('depot/depot/mail/notif-modif-rdv-bu-doctorant')
            ->setTemplateVariables([
                'these' => $these,
                'updating' => !$estLaPremiereSaisie,
            ]);

        $notif->addSuccessMessage(
            sprintf("Un mail de notification vient d'être envoyé à la bibliothèque universitaire (%s).", $to)
        );

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier de thèse.
     *
     * @param These $these
     * @param VersionFichier $version
     * @return Notification
     */
    public function createNotificationForTheseTeleversee(These $these, VersionFichier $version): Notification
    {
        $to = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        $notif = $this->createNotification('notif-depot-these');
        $notif
            ->setTo($to)
            ->setSubject("Dépôt d'une thèse")
//            ->setTemplatePath('depot/depot/mail/notif-depot-these') // le template est dans la NotifEntity
            ->setTemplateVariables([
                'these' => $these,
                'version' => $version,
            ]);

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier.
     */
    public function createNotificationForFichierTeleverse(These $these): Notification
    {
        $to = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        return $notif;
    }

    public function createNotificationForAccordSursisCorrection(These $these): Notification
    {
        $emailBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emailBU = $this->emailTheseService->fetchEmailAspectsBibliotheque($these);
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);

        $toLabel = "Maison du doctorat, Bibliothèque Universitaire et (co)directeur de thèse";
        $to = array_merge(
            $emailBDD,
            $emailBU,
            $emailsDirecteurs,
        );

        return $this->createNotification()
            ->setTo($to)
            ->setToLabel($toLabel)
            ->setSubject("Sursis accordé pour les corrections de thèse")
            ->addSuccessMessage("Un mail de notification vient d'être envoyé aux destinataires suivants : $toLabel")
            ->setTemplatePath('depot/depot/mail/notif-sursis-correction-accorde')
            ->setTemplateVariables(compact('these'));
    }

    /**
     * Notifie que la fusion de la page de couverture avec la thèse PDF est terminée.
     *
     * @param string $destinataires Emails séparés par une virgule
     * @param These $these
     * @param string $outputFilePath Chemin vers le fichier stocké en local
     * @return Notification
     */
    public function createNotificationFusionFini(string $destinataires, These $these, string $outputFilePath): Notification
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Ajout de la page de couverture terminé")
            ->setTo($to)
            ->setTemplatePath('depot/depot/mail/notif-fusion-fini')
            ->setTemplateVariables([
                'these' => $these,
                'outputFilePath' => $outputFilePath,
                'url' => '',
            ]);

        return $notif;
    }

    /**
     * Notification à l'issue de la validation de la page de couverture.
     */
    public function createNotificationValidationPageDeCouverture(These $these, string $action): ValidationPageDeCouvertureNotification
    {
        $notification = new ValidationPageDeCouvertureNotification();
        $notification->setThese($these);
        $notification->setAction($action);
        $notification->setEmailsBu($this->emailTheseService->fetchEmailAspectsBibliotheque($these));

        return $notification;
    }

    /**
     * Notification concernant la validation à l'issue du RDV BU.
     */
    public function createNotificationValidationRdvBu(These $these): ValidationRdvBuNotification
    {
        $notification = new ValidationRdvBuNotification();
        $notification->setThese($these);

        $notification->setEmailsAspectsDoctorat($this->emailTheseService->fetchEmailAspectsDoctorat($these));
        $notification->setEmailsAspectsBibliotheque($this->emailTheseService->fetchEmailAspectsBibliotheque($these));

        return $notification;
    }

    /**
     * Notification à propos de corrections attendues.
     *
     * @param ImportObservResult $record
     * @param These $these
     * @param string|null $message
     * @return \Notification\NotificationResult|null
     */
    public function createNotificationCorrectionAttendue(ImportObservResult $record, These $these, ?string &$message = null): ?Notification
    {
        // interrogation de la règle métier pour savoir comment agir...
        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule
            ->setThese($these)
            ->setDateDerniereNotif($record->getDateNotif())
            ->execute();
        $message = $rule->getMessage(' ');
        $estPremiereNotif = $rule->estPremiereNotif();
        $dateProchaineNotif = $rule->getDateProchaineNotif();

        if ($dateProchaineNotif === null) {
            return null;
        }

        $dateProchaineNotif->setTime(0, 0, 0);
        $now = (new \DateTime())->setTime(0, 0, 0);

        if ($now != $dateProchaineNotif) {
            return null;
        }

        $notif = new ChangementCorrectionAttendueNotification();
        $notif
            ->setThese($these)
            ->setEstPremiereNotif($estPremiereNotif);

        return $notif;
    }

    /**
     * Notification pour inviter à valider les corrections.
     */
    public function createNotificationValidationDepotTheseCorrigee(These $these, ?Utilisateur $presidentJury = null): Notification
    {
        $targetedUrl = $this->urlHelper->__invoke( 'these/validation-these-corrigee', ['these' => $these->getId()], ['force_canonical' => true]);
        $president = $this->getRoleService()->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_PRESIDENT_JURY, $these->getEtablissement());
        $url = $this->urlHelper->__invoke('zfcuser/login', ['type' => 'local'], ['query' => ['redirect' => $targetedUrl, 'role' => $president->getRoleId()], 'force_canonical' => true], true);

        // envoi de mail aux directeurs de thèse
        $notif = new ValidationDepotTheseCorrigeeNotification();
        $notif
            ->setThese($these)
            ->setEmailsBdd($this->emailTheseService->fetchEmailAspectsDoctorat($these))
            ->setTemplateVariables([
                'these' => $these,
                'url'   => $url,
            ]);
        if ($presidentJury !== null) {
            $notif->setDestinataire($presidentJury);
        }

        return $notif;
    }

    /**
     * Notification à propos de la validation des corrections attendues.
     */
    public function createNotificationValidationCorrectionThese(These $these): Notification
    {
        $notif = new Notification();
        $notif
            ->setSubject("Validation des corrections de la thèse")
            ->setTemplatePath('application/notification/mail/notif-validation-correction-these')
            ->setTemplateVariables([
                'these' => $these,
                'role' => $this->roleService->getRepository()->findOneBy(['code' => Role::CODE_PRESIDENT_JURY]),
                'url' => $this->urlHelper->__invoke('these/depot', ['these' => $these->getId()], ['force_canonical' => true]),
            ]);

        $to = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $notif
            ->addSuccessMessage(sprintf("Un mail de notification vient d'être envoyé à la Maison du doctorat (%s)", $to))
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        return $notif;
    }

    /**
     * Notification à propos de la validation des corrections par le doctorant.
     *
     * @throws \Notification\Exception\RuntimeException Aucune adresse mail trouvée pour le doctorant
     */
    public function createNotificationValidationCorrectionTheseEtudiant(These $these): Notification
    {
        $individu = $these->getDoctorant()->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$these->getDoctorant()}");
        }

        return (new Notification())
            ->setSubject("Validation des corrections de la thèse")
            ->setTo($email)
            ->addSuccessMessage(sprintf("Un mail de notification vient d'être envoyé au doctorant (%s)", $email))
            ->setTemplatePath('application/notification/mail/notif-validation-correction-these')
            ->setTemplateVariables([
                'these' => $these,
                'role' => $this->roleService->getRepository()->findOneBy(['code' => Role::CODE_PRESIDENT_JURY]),
                'url' => $this->urlHelper->__invoke('these/depot', ['these' => $these->getId()], ['force_canonical' => true]),
            ]);
    }

}