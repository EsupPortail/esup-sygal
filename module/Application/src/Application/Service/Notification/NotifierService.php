<?php

namespace Application\Service\Notification;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Validation;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Variable;
use Application\Notification\CorrectionAttendueUpdatedNotification;
use Application\Notification\ResultatTheseAdmisNotification;
use Application\Notification\ResultatTheseModifieNotification;
use Application\Notification\ValidationDepotTheseCorrigeeNotification;
use Application\Notification\ValidationRdvBuNotification;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Notification\Notification;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use UnicaenAuth\Entity\Db\RoleInterface;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Helper\Url as UrlHelper;

/**
 * Service d'envoi de notifications par mail.
 *
 * @method NotificationFactory getNotificationFactory()
 *
 * @author Unicaen
 */
class NotifierService extends \Notification\Service\NotifierService
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Notification concernant la validation à l'issue du RDV BU.
     *
     * @param ValidationRdvBuNotification $notification
     */
    public function triggerValidationRdvBu(ValidationRdvBuNotification $notification)
    {
        $these = $notification->getThese();

        $notification->setEmailBdd($this->fetchEmailBdd($these));
        $notification->setEmailBu($this->fetchEmailBu($these));

        $this->trigger($notification);
    }

    /**
     * Notification du BDD concernant l'évolution des résultats de thèses.
     *
     * @param array $data
     * @return ResultatTheseModifieNotification
     */
    public function triggerBdDUpdateResultat(array $data)
    {
        $these = current($data)['these'];

        $emailBdd = $this->fetchEmailBdd($these);
        $emailBu = $this->fetchEmailBu($these);

        $notif = new ResultatTheseModifieNotification();
        $notif->setData($data);
        $notif->setTo([$emailBdd, $emailBu]);

        $this->trigger($notif);

        return $notif;
    }

    /**
     * Notification des doctorants dont le résultat de la thèse est passé à Admis.
     *
     * @param array $data
     * @return ResultatTheseAdmisNotification[]
     */
    public function triggerDoctorantResultatAdmis(array $data)
    {
        $notifs = [];

        foreach ($data as $array) {
            $these = $array['these'];
            /* @var These $these */

            $emailBdd = $this->fetchEmailBdd($these);

            $notif = new ResultatTheseAdmisNotification();
            $notif->setThese($these);
            $notif->setEmailBdd($emailBdd);

            $this->trigger($notif);

            $notifs[] = $notif;
        }

        return $notifs;
    }

    /**
     * @param ImportObservResult $record
     * @param These              $these
     * @return CorrectionAttendueUpdatedNotification|null
     */
    public function triggerCorrectionAttendue(ImportObservResult $record, These $these)
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

        $this->trigger($notif);

        return $notif;
    }

    /**
     * @param These $these
     */
    public function triggerDateButoirCorrectionDepassee(These $these)
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

        $this->trigger($notif);
    }

    /**
     * Notification par mail des directeurs de thèse pour les inviter à valider les corrections.
     *
     * @param These $these
     */
    public function triggerValidationDepotTheseCorrigee(These $these)
    {
        // envoi de mail aux directeurs de thèse
        $notif = new ValidationDepotTheseCorrigeeNotification();
        $notif
            ->setThese($these)
            ->setEmailBdd($this->fetchEmailBdd($these))
            ->setTemplateVariables([
                'these' => $these,
                'url'   => $this->urlHelper->__invoke(
                    'these/validation-these-corrigee',
                    ['these' => $these->getId()],
                    ['force_canonical' => true]),
            ]);

        $this->trigger($notif);

        $infoMessages = $notif->getInfoMessages();
        $this->messageContainer->setMessages([
            'info' => $infoMessages[0],
        ]);
        if ($errorMessages = $notif->getWarningMessages()) {
            $this->messageContainer->addMessages([
                'danger' => $errorMessages[0],
            ]);
        }
    }

    /**
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerValidationCorrectionThese(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBdd($these);
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé aux Bureau des Doctorats (%s)", $to);
        $this->messageContainer->setMessage($infoMessage, 'info');
    }

    /**
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerValidationCorrectionTheseEtudiant(Notification $notif, These $these)
    {
        $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getIndividu()->getEmail();
        if (!$to) {
            $this->messageContainer->setMessage("Impossible d'envoyer un mail à {$these->getDoctorant()} car son adresse est inconnue", 'danger');

            return;
        }
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à votre doctorant (%s)", $to);
        if ($this->messageContainer->getMessage()) {
            $new_message = "<ul><li>" . $this->messageContainer->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
            $this->messageContainer->setMessage($new_message, 'info');
        } else {
            $this->messageContainer->setMessage($infoMessage, 'info');
        }
    }

    /**
     * Notification générique de la BU.
     *
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerRdvBuSaisiParDoctorant(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBu($these);
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à la BU (%s).", $to);
        $this->messageContainer->setMessage($infoMessage, 'info');
    }

    public function triggerInformationManquante(These $these, $manques)
    {
        $mails = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Informations manquantes pour la thèse [" . $these->getTitre() . "]")
            ->setTo($mails)
            ->setTemplatePath('application/these/mail/notif-informations-manquantes')
            ->setTemplateVariables([
                'manques'      => $manques,
                'these'        => $these,
            ]);
        $this->trigger($notif);
    }



    public function triggerEcoleDoctoraleAbsente(These $these) {
        $mails = $this->fetchEmailBdd($these);
        $notif = $this->createNotificationForStructureAbsente("l'école doctorale", $these, $mails);
        $this->trigger($notif);
    }

    /**
     * @param EcoleDoctorale $ecole
     */
    public function triggerLogoAbsentEcoleDoctorale(EcoleDoctorale $ecole)
    {
        $mails = [];
        foreach ($this->getEcoleDoctoraleService()->getIndividuByEcoleDoctoraleId($ecole->getId()) as $individu) {
            /** @var Individu $individu */
            $email = $individu->getEmail();
            if ($email !== null) $mails[] = $email;
        }

        $libelle = $ecole->getLibelle();
        $notif = $this->createNotificationForLogoStructureAbsent("l'école doctorale", $libelle, $mails);
        $this->trigger($notif);
    }


    public function triggerUniteRechercheAbsente(These $these) {
        $mails = $this->fetchEmailBdd($these);
        $notif = $this->createNotificationForStructureAbsente("l'unité de recherche", $these, $mails);
        $this->trigger($notif);
    }

    /**
     * @param UniteRecherche $unite
     */
    public function triggerLogoAbsentUniteRecherche(UniteRecherche $unite)
    {
        $mails = [];
        foreach ($this->getUniteRechercheService()->getIndividuByUniteRechercheId($unite->getId()) as $individu) {
            /** @var Individu $individu */
            $email = $individu->getEmail();
            if ($email !== null) $mails[] = $email;
        }

        $libelle = $unite->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'unité de recherche", $libelle, $mails);

        $this->trigger($notif);
    }

    /**
     * @param Etablissement $etablissement
     */
    public function triggerLogoAbsentEtablissement(Etablissement $etablissement)
    {

        //Récupération des mails des personnes ayant le rôle d'administrateur technique
        $mails = [];
        $role = $this->getRoleService()->getRepository()->findByCode(Role::CODE_ADMIN_TECH);
        $irs = $this->getIndividuService()->getRepository()->findByRole($role);
        foreach($irs as $ir) {
            $mails[] = $ir->getIndividu()->getEmail();
        }

        $libelle = $etablissement->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'établissement", $libelle, $mails);

        $this->trigger($notif);
    }

    /**
     * @param MailConfirmation $mailConfirmation
     * @param string           $titre
     * @param string           $corps
     */
    public function triggerMailConfirmation(MailConfirmation $mailConfirmation, $titre, $corps)
    {
        $notif = new Notification();
        $notif
            ->setSubject($titre)
            ->setTo($mailConfirmation->getEmail())
            ->setTemplatePath('application/doctorant/empty-mail')
            ->setTemplateVariables([
                'destinataire' => $mailConfirmation->getIndividu()->getNomUsuel(),
                'titre'        => $titre,
                'corps'        => $corps,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param string   $type
     * @param string   $libelle
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
                'type'    => $type,
                'libelle' => $libelle,
            ]);

        return $notif;
    }

    /**
     * @param string   $type
     * @param These   $these
     * @param string[] $to
     * @return Notification
     */
    private function createNotificationForStructureAbsente($type, $these, $to)
    {
        $notif = new Notification();
        $notif
            ->setSubject("$type manquante pour la thèse [" . $these->getTitre() . "]")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-structure-absente')
            ->setTemplateVariables([
                'type'      => $type,
                'these'     => $these,
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
     * @param FlashMessenger $flashMessenger
     * @param string         $namespacePrefix
     */
    public function feedFlashMessenger(FlashMessenger $flashMessenger, $namespacePrefix = '')
    {
        $notificationLogs = $this->getLogs();

        if (! empty($notificationLogs['info'])) {
            $flashMessenger->addMessage($notificationLogs['info'], $namespacePrefix . 'info');
        }
        if (! empty($notificationLogs['danger'])) {
            $flashMessenger->addMessage($notificationLogs['danger'], $namespacePrefix . 'danger');
        }
    }

    /**
     * @var string $type
     * @var Role $role
     * @var Individu $individu
     */
    public function triggerChangementRole($type, $role, $individu)
    {
        $mail = $individu->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Modification de vos rôles dans l'application")
            ->setTo($mail)
            ->setTemplatePath('application/utilisateur/changement-role')
            ->setTemplateVariables([
                'type'         => $type,
                'role'         => $role,
                'individu'     => $individu,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param Validation $validation
     */
    public function triggerDevalidationProposition($validation) {
        $mail = $validation->getIndividu()->getEmail();
        $these = $validation->getThese();

        $notif = new Notification();
        $notif
            ->setSubject("Votre validation de la proposition de soutenance a été annulée")
            ->setTo($mail)
            ->setTemplatePath('soutenance/notification/devalidation')
            ->setTemplateVariables([
                'validation'     => $validation,
                'these'          => $these,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @param Validation $validation
     */
    public function triggerValidationProposition($these, $validation)
    {
        $emails   = $these->getDirecteursTheseEmails();
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Une validation de votre proposition de soutenance vient d'être faite")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-acteur')
            ->setTemplateVariables([
                'validation'     => $validation,
            ]);
        $this->trigger($notif);
    }

    /** @param These $these */
    public function triggerNotificationUniteRechercheProposition($these)
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $emails[] = $individuRole->getIndividu()->getEmail();
        }

        $notif = new Notification();
        $notif
            ->setSubject("Demande de validation d'une proposition de soutenance")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-structure')
            ->setTemplateVariables([
                'these'     => $these,
            ]);
        $this->trigger($notif);
    }

    /** @param These $these */
    public function triggerNotificationEcoleDoctoraleProposition($these)
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $emails[] = $individuRole->getIndividu()->getEmail();
        }

        $notif = new Notification();
        $notif
            ->setSubject("Demande de validation d'une proposition de soutenance")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-structure')
            ->setTemplateVariables([
                'these'     => $these,
            ]);
        $this->trigger($notif);
    }

    /** @param These $these */
    public function triggerNotificationBureauDesDoctoratsProposition($these)
    {
        $emails = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Demande de validation d'une proposition de soutenance")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-structure')
            ->setTemplateVariables([
                'these'     => $these,
            ]);
        $this->trigger($notif);
    }

    /** @param These $these */
    public function triggerNotificationPropositionValidee($these)
    {
        $emails = [];
        //structures
        $emails[]  = $this->fetchEmailBdd($these);
        /** @var IndividuRole $individuRole */
        foreach ($this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure()) as $individuRole) $emails[] = $individuRole->getIndividu()->getEmail();
        foreach ($this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure()) as $individuRole) $emails[] = $individuRole->getIndividu()->getEmail();
        //acteurs
        $emails[]  = $these->getDoctorant()->getIndividu()->getEmail();
        foreach ($these->getDirecteursTheseEmails() as $email => $name) $emails[] = $email;
        var_dump($emails);


        $notif = new Notification();
        $notif
            ->setSubject("Demande de validation d'une proposition de soutenance")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/validation-soutenance')
            ->setTemplateVariables([
                'these'     => $these,
            ]);
        $this->trigger($notif);
    }

    /** @param These $these */
    public function triggerNotificationPresoutenance($these)
    {
        $emails = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Vous pouvez procéder aux rensignement de la présoutenance")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/presoutenance')
            ->setTemplateVariables([
                'these'     => $these,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @param Individu $currentUser
     * @param RoleInterface $currentRole
     * @param string $motif
     */
    public function triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $motif)
    {
        $emails   = $these->getDirecteursTheseEmails();
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Votre proposistion de soutenance a été réfusé")
            ->setTo($emails)
            ->setTemplatePath('soutenance/notification/refus')
            ->setTemplateVariables([
                'acteur' => $currentUser,
                'role' => $currentRole,
                'motif' => $motif,
                'these' => $these,
            ]);
        $this->trigger($notif);
    }

    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $membre->getIndividu()->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Demande de signature de l'engagement d'impartialité de la thèse de ".$these->getDoctorant()->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/engagement-impartialite-demande')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'membre' => $membre,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerSignatureEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Signature de l'engagement d'impartialité de la thèse de ".$these->getDoctorant()->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/engagement-impartialite-signature')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'membre' => $membre,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerAnnulationEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $membre->getIndividu()->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Annulation de l'engagement d'impartialité de la thèse de ".$these->getDoctorant()->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/engagement-impartialite-annulation')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'membre' => $membre,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $rapporteur
     */
    public function triggerDemandeAvisSoutenance($these, $proposition, $rapporteur)
    {
        $email   = $rapporteur->getIndividu()->getEmail();
        $notif = new Notification();
        $notif
            ->setSubject("Demande de l'avis de soutenance de la thèse de ".$these->getDoctorant()->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/demande-avis-soutenance')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'membre' => $rapporteur,
            ]);
        $this->trigger($notif);
    }



}