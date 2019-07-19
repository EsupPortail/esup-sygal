<?php

namespace Application\Service\Notification;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Variable;
use Application\Entity\Db\VersionFichier;
use Application\Notification\CorrectionAttendueUpdatedNotification;
use Application\Notification\ResultatTheseAdmisNotification;
use Application\Notification\ResultatTheseModifieNotification;
use Application\Notification\ValidationDepotTheseCorrigeeNotification;
use Application\Notification\ValidationRdvBuNotification;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Notification\Notification;
use UnicaenApp\Options\ModuleOptions;
use Zend\View\Helper\Url as UrlHelper;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory extends \Notification\Service\NotificationFactory
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var ModuleOptions
     */
    private $appModuleOptions;

    /**
     * {@inheritdoc}
     */
    public function initNotification(Notification $notification)
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
     * Notification concernant la validation à l'issue du RDV BU.
     *
     * @param ValidationRdvBuNotification $notification
     * @return Notification
     */
    public function createNotificationForValidationRdvBu(ValidationRdvBuNotification $notification)
    {
        $these = $notification->getThese();

        $notification->setEmailBdd($this->fetchEmailBdd($these));
        $notification->setEmailBu($this->fetchEmailBu($these));

        //$this->trigger($notification);
        return $notification;
    }

    /**
     * Notification du BDD concernant l'évolution des résultats de thèses.
     *
     * @param array $data
     * @return Notification
     */
    public function createNotificationForBdDUpdateResultat(array $data)
    {
        $these = current($data)['these'];

        $emailBdd = $this->fetchEmailBdd($these);
        $emailBu = $this->fetchEmailBu($these);

        $notif = new ResultatTheseModifieNotification();
        $notif->setData($data);
        $notif->setTo([$emailBdd, $emailBu]);

        //$this->trigger($notif);
        return $notif;
    }

    /**
     * Notification des doctorants dont le résultat de la thèse est passé à Admis.
     *
     * @param array $data
     * @return Notification
     */
    public function createNotificationForDoctorantResultatAdmis(array $data)
    {
        foreach ($data as $array) {
            $these = $array['these'];
            /* @var These $these */

            $emailBdd = $this->fetchEmailBdd($these);

            $notif = new ResultatTheseAdmisNotification();
            $notif->setThese($these);
            $notif->setEmailBdd($emailBdd);

            //$this->trigger($notif);
            return $notif;
        }
    }

    /**
     * Notifie que le retraitement automatique du fichier PDF est terminé.
     *
     * @param string               $destinataires        Emails séparés par une virgule
     * @param FichierThese         $fichierTheseRetraite Fichier retraité concerné
     * @param ValiditeFichier|null $validite             Résultat du test d'archivabilité éventuel
     * @return Notification
     * @return Notification
     */
    public function createNotificationForRetraitementFini($destinataires, FichierThese $fichierTheseRetraite, ValiditeFichier $validite = null)
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Retraitement terminé")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-retraitement-fini')
            ->setTemplateVariables([
                'fichierRetraite' => $fichierTheseRetraite,
                'validite' => $validite,
                'url' => '',
            ]);

        return $notif;
    }

    /**
     * @param ImportObservResult $record
     * @param These $these
     * @return ImportObservResult|null
     * @return Notification
     */
    public function createNotificationForCorrectionAttendue(ImportObservResult $record, These $these)
    {
        // interrogation de la règle métier pour savoir comment agir...
        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule
            ->setThese($these)
            ->setDateDerniereNotif($record->getDateNotif())
            ->execute();
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

        $notif = new CorrectionAttendueUpdatedNotification();
        $notif
            ->setThese($these)
            ->setEstPremiereNotif($estPremiereNotif);

        //$this->trigger($notif);
//        return $notif;

        return $record;
    }

    /**
     * @param These $these
     * @return Notification
     */
    public function createNotificationForDateButoirCorrectionDepassee(These $these)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Corrections " . lcfirst($these->getCorrectionAutoriseeToString(true)) . " non faites")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-date-butoir-correction-depassee')
            ->setTemplateVariables([
                'these' => $these,
            ]);

        //$this->trigger($notif);
        return $notif;
    }

    /**
     * Notification par mail des directeurs de thèse pour les inviter à valider les corrections.
     *
     * @param These $these
     * @return Notification
     */
    public function createNotificationForValidationDepotTheseCorrigee(These $these)
    {
        $url = $this->urlHelper->__invoke('these/validation-these-corrigee', ['these' => $these->getId()], ['force_canonical' => true]);

        // envoi de mail aux directeurs de thèse
        $notif = new ValidationDepotTheseCorrigeeNotification();
        $notif
            ->setThese($these)
            ->setEmailBdd($this->fetchEmailBdd($these))
            ->setTemplateVariables([
                'these' => $these,
                'url' => $url,
            ]);

        //$this->trigger($notif);

//        $infoMessages = $notif->getInfoMessages();
//        $this->messageContainer->setMessages([
//            'info' => $infoMessages[0],
//        ]);
//        if ($errorMessages = $notif->getWarningMessages()) {
//            $this->messageContainer->addMessages([
//                'danger' => $errorMessages[0],
//            ]);
//        }

        return $notif;
    }

    /**
     * @param Notification $notif
     * @param These $these
     * @return Notification
     */
    public function createNotificationForValidationCorrectionThese(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBdd($these);
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        //$this->trigger($notif);

//        $infoMessage = sprintf("Un mail de notification vient d'être envoyé aux Bureau des Doctorats (%s)", $to);
//        $this->messageContainer->setMessage($infoMessage, 'info');

        return $notif;
    }

    /**
     * @param Notification $notif
     * @param These $these
     * @return Notification
     */
    public function createNotificationForValidationCorrectionTheseEtudiant(Notification $notif, These $these)
    {
        $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getIndividu()->getEmail();
        if (!$to) {
//            $this->messageContainer->setMessage("Impossible d'envoyer un mail à {$these->getDoctorant()} car son adresse est inconnue", 'danger');

            return $notif;
        }
        $notif->setTo($to);

        //$this->trigger($notif);

//        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à votre doctorant (%s)", $to);
//        if ($this->messageContainer->getMessage()) {
//            $new_message = "<ul><li>" . $this->messageContainer->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
//            $this->messageContainer->setMessage($new_message, 'info');
//        } else {
//            $this->messageContainer->setMessage($infoMessage, 'info');
//        }

        return $notif;
    }

    /**
     * Notification à l'issu du remplissage du formulaire RDV BU par le doctorant.
     *
     * @param These $these
     * @param bool $estLaPremiereSaisie
     * @return Notification
     */
    public function createNotificationForRdvBuSaisiParDoctorant(These $these, $estLaPremiereSaisie)
    {
        $subject = sprintf("%s Saisie des informations pour la prise de rendez-vous BU", $these->getLibelleDiscipline());
        $to = $this->fetchEmailBu($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setSubject($subject)
            ->setTemplatePath('application/these/mail/notif-modif-rdv-bu-doctorant')
            ->setTemplateVariables([
                'these' => $these,
                'updating' => !$estLaPremiereSaisie,
            ]);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à la BU (%s).", $to);
        $notif->setInfoMessages($infoMessage);

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier de thèse.
     *
     * @param These $these
     * @param VersionFichier $version
     * @return Notification
     */
    public function createNotificationForTheseTeleversee(These $these, VersionFichier $version)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = $this->createNotification('notif-depot-these');
        $notif
            ->setTo($to)
            ->setSubject("Dépôt d'une thèse")
//            ->setTemplatePath('application/these/mail/notif-depot-these') // le template est dans la NotifEntity
            ->setTemplateVariables([
                'these' => $these,
                'version' => $version,
            ]);

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier.
     *
     * @param These $these
     * @return Notification
     */
    public function createNotificationForFichierTeleverse(These $these)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        return $notif;
    }

    /**
     * @param EcoleDoctorale $ecole
     * @return Notification
     */
    public function createNotificationForLogoAbsentEcoleDoctorale(EcoleDoctorale $ecole)
    {
        $mails = [];
        foreach ($this->getEcoleDoctoraleService()->getIndividuByEcoleDoctoraleId($ecole->getId()) as $individu) {
            /** @var Individu $individu */
            $email = $individu->getEmail();
            if ($email !== null) $mails[] = $email;
        }

        $libelle = $ecole->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'école doctorale", $libelle, $mails);

        //$this->trigger($notif);
        return $notif;
    }

    /**
     * @param UniteRecherche $unite
     * @return Notification
     */
    public function createNotificationForLogoAbsentUniteRecherche(UniteRecherche $unite)
    {
        $mails = [];
        foreach ($this->getUniteRechercheService()->getIndividuByUniteRechercheId($unite->getId()) as $individu) {
            /** @var Individu $individu */
            $email = $individu->getEmail();
            if ($email !== null) $mails[] = $email;
        }

        $libelle = $unite->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'unité de recherche", $libelle, $mails);

        //$this->trigger($notif);
        return $notif;
    }

    /**
     * @param Etablissement $etablissement
     * @return Notification
     */
    public function createNotificationForLogoAbsentEtablissement(Etablissement $etablissement)
    {
        //TODO ne pas laisser en dur ... (mail les administrateurs techniques de l'établissement)
        $mails = ["jean-philippe.metivier@unicaen.fr", "bertrand.gauthier@unicaen.fr"];

        $libelle = $etablissement->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'établissement", $libelle, $mails);

        //$this->trigger($notif);
        return $notif;
    }

    /**
     * @param MailConfirmation $mailConfirmation
     * @param string $titre
     * @param string $corps
     * @return Notification
     */
    public function createNotificationForMailConfirmation(MailConfirmation $mailConfirmation, $titre, $corps)
    {
        $notif = new Notification();
        $notif
            ->setSubject($titre)
            ->setTo($mailConfirmation->getEmail())
            ->setTemplatePath('application/doctorant/empty-mail')
            ->setTemplateVariables([
                'destinataire' => $mailConfirmation->getIndividu()->getNomUsuel(),
                'titre' => $titre,
                'corps' => $corps,
            ]);
        //$this->trigger($notif);
        return $notif;
    }

    /**
     * @param string $type
     * @param string $libelle
     * @param string[] $to
     * @return Notification
     */
    private function createNotificationForLogoStructureAbsent($type, $libelle, $to)
    {
        $notif = new Notification();
        $notif
            ->setSubject("Logo manquant pour $type [" . $libelle . "]")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-logo-absent')
            ->setTemplateVariables([
                'type' => $type,
                'libelle' => $libelle,
            ]);

        return $notif;
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBdd(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);

        return $variable->getValeur();
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBu(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BU, $these);

        return $variable->getValeur();
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setAppModuleOptions(ModuleOptions $options)
    {
        $this->appModuleOptions = $options;
    }

    /**
     * Notifie que le retraitement automatique du fichier PDF est terminé.
     *
     * @param string $destinataires Emails séparés par une virgule
     * @param These $these
     * @param string $outputFilePath Chemin vers le fichier stocké en local
     * @return Notification
     */
    public function createNotificationFusionFini($destinataires, $these, $outputFilePath)
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Retraitement terminé")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-fusion-fini')
            ->setTemplateVariables([
                'these' => $these,
                'outputFilePath' => $outputFilePath,
                'url' => '',
            ]);

        return $notif;
    }
}