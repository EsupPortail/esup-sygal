<?php

namespace Admission\Service\Notification;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\TypeValidation;
use Admission\Notification\AdmissionAvisNotification;
use Admission\Notification\AdmissionOperationAttenduNotification;
use Admission\Provider\Template\MailTemplates;
use Admission\Rule\Email\ExtractionEmailRuleAwareTrait;
use Admission\Service\Url\UrlServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Notification\Exception\RuntimeException;
use Notification\Notification;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;
use Notification\Factory\NotificationFactory as NF;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory extends NF
{
    use RenduServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UrlServiceAwareTrait;
    use ExtractionEmailRuleAwareTrait;

    public function createNotificationDossierIncomplet(Admission $admission): Notification
    {
        $notif = new Notification();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        //Création du lien vers le dossier d'admission
        $vars = ['admission' => $admission];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $vars['individu'] = $individu;
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::NOTIFICATION_DOSSIER_INCOMPLET, $vars);
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationOperationAttendue(): AdmissionOperationAttenduNotification
    {
        return new AdmissionOperationAttenduNotification();
    }

    public function addOperationAttendueToTemplateOperationAttendue(AdmissionOperationInterface $operationAttendue, AdmissionOperationAttenduNotification $notificationOperationAttendue): AdmissionOperationAttenduNotification
    {
        $admission = $operationAttendue->getAdmission();
        $individu = $admission->getIndividu();
        $vars = [
            'individu' => $individu,
            'admission' => $admission,
            'typeValidation' => $operationAttendue,
            'anomalies' => $notificationOperationAttendue
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::OPERATION_ATTENDUE, $vars);
        $notificationOperationAttendue->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notificationOperationAttendue;
    }

    public function createNotificationValidationAjoutee(AdmissionValidation $admissionValidation): Notification
    {
        $admission = $admissionValidation->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $vars = [
            'admission' => $admission,
            'admissionValidation' => $admissionValidation,
            'typeValidation' => $admissionValidation->getTypeValidation(),
            'individu' => $individu
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_AJOUTEE, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $notif->setCc([$emailDirecteur => $directeur->getNomComplet()]);
        }

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, [])),
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s (%s)', $notif->getCc()[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }

    public function createNotificationValidationSupprimee(AdmissionValidation $admissionValidation): Notification
    {
        $admission = $admissionValidation->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        $vars = [
            'admission' => $admission,
            'admissionValidation' => $admissionValidation,
            'individu' => $individu,
            'typeValidation' => $admissionValidation->getTypeValidation()
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_SUPPRIMEE, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $notif->setCc([$emailDirecteur => $directeur->getNomComplet()]);
        }

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, []))
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s (%s)', $notif->getCc()[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }

    public function createNotificationAvis(): AdmissionAvisNotification
    {
        return new AdmissionAvisNotification();
    }

    public function createNotificationAvisAjoute(AdmissionAvisNotification $notificationAvisAdmission): Notification
    {
        $admissionAvis = $notificationAvisAdmission->getAdmissionAvis();
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $vars = [
            'admission' => $admission,
            'admissionAvis' => $admissionAvis,
            'individu' => $individu,
            'typeValidation' => $admissionAvis->getAvis()->getAvisType(),
            'anomalies' => $notificationAvisAdmission
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        $cc = [];
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $cc = [$emailDirecteur => $directeur->getNomComplet()];
        }

        $rolesCC['role'] = [Role::CODE_RESP_ED, Role::CODE_RESP_UR];
        if($admissionAvis->getAvis()->getAvisType()->getCode() === AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE && $admissionAvis->getAvis()->getAvisValeur()->getCode() === AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_POSITIF){
            //Récupération des emails des responsables d'ED et d'UR
            $cc = array_merge($cc, $this->extractionMailRule->extractEmailsFromAdmissionRoles($admission, $rolesCC));
            $notificationAvisAdmission->setAnomalies($this->extractionMailRule->getAnomalies());
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DOSSIER_VALIDE, $vars);
        }else if($admissionAvis->getAvis()->getAvisType()->getCode() === AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE && $admissionAvis->getAvis()->getAvisValeur()->getCode() === AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF) {
            //Récupération des emails des responsables d'ED et d'UR
            $cc = array_merge($cc, $this->extractionMailRule->extractEmailsFromAdmissionRoles($admission, $rolesCC));
            $notificationAvisAdmission->setAnomalies($this->extractionMailRule->getAnomalies());
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DOSSIER_REJETE, $vars);
        }else{
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::AVIS_AJOUTE, $vars);
        }

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
            ->setCc($cc);

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, [])),
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s', $notif->getCc()[$key]);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }

    public function createNotificationAvisModifie(AdmissionAvisNotification $notificationAvisAdmission): Notification
    {
        $admissionAvis = $notificationAvisAdmission->getAdmissionAvis();
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $vars = [
            'admission' => $admission,
            'admissionAvis' => $admissionAvis,
            'individu' => $individu,
            'typeValidation' => $admissionAvis->getAvis()->getAvisType(),
            'anomalies' => $notificationAvisAdmission
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $cc = [];
        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $cc = [$emailDirecteur => $directeur->getNomComplet()];
        }

        $rolesCC['role'] = [Role::CODE_RESP_ED, Role::CODE_RESP_UR];
        if($admissionAvis->getAvis()->getAvisType()->getCode() === AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE && $admissionAvis->getAvis()->getAvisValeur()->getCode() === AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_POSITIF){
            //Récupération des emails des responsables d'ED et d'UR
            $cc = array_merge($cc, $this->extractionMailRule->extractEmailsFromAdmissionRoles($admission, $rolesCC));
            $notificationAvisAdmission->setAnomalies($this->extractionMailRule->getAnomalies());
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DOSSIER_VALIDE, $vars);
        }else if($admissionAvis->getAvis()->getAvisType()->getCode() === AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE && $admissionAvis->getAvis()->getAvisValeur()->getCode() === AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF) {
            //Récupération des emails des responsables d'ED et d'UR
            $cc = array_merge($cc, $this->extractionMailRule->extractEmailsFromAdmissionRoles($admission, $rolesCC));
            $notificationAvisAdmission->setAnomalies($this->extractionMailRule->getAnomalies());
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DOSSIER_REJETE, $vars);
        }else{
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::AVIS_MODIFIE, $vars);
        }

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
            ->setCc($cc);

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, [])),
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s (%s)', $notif->getCc()[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }

    public function createNotificationAvisSupprime(AdmissionAvis $admissionAvis): Notification
    {
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $vars = [
            'admission' => $admission,
            'admissionAvis' => $admissionAvis,
            'individu' => $individu,
            'typeValidation' => $admissionAvis->getAvis()->getAvisType()
        ];
        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::AVIS_SUPPRIME, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $notif->setCc([$emailDirecteur => $directeur->getNomComplet()]);
        }

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, [])),
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s (%s)', $notif->getCc()[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }

    public function createNotificationDeclarationDossierIncomplet(AdmissionAvis $admissionAvis): Notification
    {
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $vars = [
            'admission' => $admission,
            'admissionAvis' => $admissionAvis,
            'individu' => $individu,
        ];

        $url = $this->urlService->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::NOTIFICATION_DECLARATION_DOSSIER_INCOMPLET, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur();
            $notif->setCc([$emailDirecteur => $directeur->getNomComplet()]);
        }

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($notif->getTo()), function(array $accu, string $key) use ($notif) {
                $accu[] = sprintf('%s (%s)', $notif->getTo()[$key], $key);
                return $accu;
            }, [])),
        );

        if ($notif->getCc()) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($notif->getCc()), function(array $accu, string $key) use ($notif) {
                    $accu[] = sprintf('%s (%s)', $notif->getCc()[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $notif->addSuccessMessage($successMessage);

        return $notif;
    }
}