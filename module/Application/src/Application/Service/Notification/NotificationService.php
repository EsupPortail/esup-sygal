<?php

namespace Application\Service\Notification;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Variable;
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

/**
 * Service d'envoi de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationService extends \Notification\Service\NotificationService
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

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
     */
    public function triggerBdDUpdateResultat(array $data)
    {
        $emailBdd = $this->fetchEmailBdd(current($data)['these']);

        $notif = new ResultatTheseModifieNotification();
        $notif->setData($data);
        $notif->setEmailBdd($emailBdd);

        $this->trigger($notif);
    }

    /**
     * Notification des doctorants dont le résultat de la thèse est passé à Admis.
     *
     * @param array $data
     */
    public function triggerDoctorantResultatAdmis(array $data)
    {
        foreach ($data as $array) {
            $these = $array['these'];
            /* @var These $these */

            $emailBdd = $this->fetchEmailBdd($these);

            $notif = new ResultatTheseAdmisNotification();
            $notif->setThese($these);
            $notif->setEmailBdd($emailBdd);

            $this->trigger($notif);
        }
    }

    /**
     * Notifie que le retraitement automatique du fichier PDF est terminé.
     *
     * @param string               $destinataires   Emails séparés par une virgule
     * @param Fichier              $fichierRetraite Fichier retraité concerné
     * @param ValiditeFichier|null $validite        Résultat du test d'archivabilité éventuel
     * @return Notification
     */
    public function triggerRetraitementFini($destinataires, Fichier $fichierRetraite, ValiditeFichier $validite = null)
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = new Notification();
        $notif
            ->setSubject("Retraitement terminé")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-retraitement-fini')
            ->setTemplateVariables([
                'fichierRetraite' => $fichierRetraite,
                'validite'        => $validite,
                'url'             => '',
            ]);

        $this->trigger($notif);

        return $notif;
    }

    /**
     * @param ImportObservResult $record
     * @param These              $these
     * @return ImportObservResult|null
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

        return $record;
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
        $this->setMessages([
            'info' => $infoMessages[0],
        ]);
        if ($errorMessages = $notif->getWarningMessages()) {
            $this->addMessages([
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
        $this->setMessage($infoMessage, 'info');
    }

    /**
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerValidationCorrectionTheseEtudiant(Notification $notif, These $these)
    {
        $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getIndividu()->getEmail();
        if (!$to) {
            $this->setMessage("Impossible d'envoyer un mail à {$these->getDoctorant()} car son adresse est inconnue", 'danger');

            return;
        }
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à votre doctorant (%s)", $to);
        if ($this->getMessage()) {
            $new_message = "<ul><li>" . $this->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
            $this->setMessage($new_message, 'info');
        } else {
            $this->setMessage($infoMessage, 'info');
        }
    }

    /**
     * Notification générique de la BU.
     *
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerNotificationBU(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBu($these);
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à la BU (%s).", $to);
        $this->setMessage($infoMessage, 'info');
    }

    /**
     * Notification générique de la BU.
     *
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerNotificationBdD(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBdd($these);
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        $this->trigger($notif);
    }

    /**
     * @param EcoleDoctorale $ecole
     */
    public function triggerLogoAbsentEcoleDoctorale(EcoleDoctorale $ecole)
    {
        $mails = [];
        foreach ($this->ecoleDoctoraleService->getIndividuByEcoleDoctoraleId($ecole->getId()) as $individu) {
            /** @var Individu $individu */
            $email = $individu->getEmail();
            if ($email !== null) $mails[] = $email;
        }

        $libelle = $ecole->getLibelle();

        $notif = $this->createNotificationForLogoStructureAbsent("l'école doctorale", $libelle, $mails);

        $this->trigger($notif);
    }

    /**
     * @param UniteRecherche $unite
     */
    public function triggerLogoAbsentUniteRecherche(UniteRecherche $unite)
    {
        $mails = [];
        foreach ($this->uniteRechercheService->getIndividuByUniteRechercheId($unite->getId()) as $individu) {
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
        //TODO ne pas laisser en dur ... (mail les administrateurs techniques de l'établissement)
        $mails = ["jean-philippe.metivier@unicaen.fr", "bertrand.gauthier@unicaen.fr"];

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
}