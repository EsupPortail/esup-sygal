<?php

namespace Admission\Service\Notification;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Inscription;
use Admission\Notification\AdmissionAvisNotification;
use Admission\Notification\AdmissionOperationAttenduNotification;
use Admission\Provider\Template\MailTemplates;
use Admission\Rule\Email\ExtractionEmailRuleAwareTrait;
use Admission\Service\Url\UrlServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory as NF;
use Notification\Notification;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory extends NF
{
    use RenduServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UrlServiceAwareTrait;
    use ExtractionEmailRuleAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    public function createNotificationDossierIncomplet(Admission $admission): Notification
    {
        $notif = new Notification();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$individu}");
        }

        $this->urlService->setVariables([
            'admission' => $admission,
            'individu' => $individu,
        ]);

        $individuTemplateVariable = $this->getIndividuTemplateVariable($individu);
        $admissionAdmissionTemplateVariable = $this->getAdmissionAdmissionTemplateVariable($admission);
        $vars = [
            'admission' => $admissionAdmissionTemplateVariable,
            'individu' => $individuTemplateVariable,
            'Url' => $this->urlService,
        ];

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

    public function addOperationAttendueToTemplateOperationAttendue(
        AdmissionOperationInterface $operationAttendue,
        AdmissionOperationAttenduNotification $notificationOperationAttendue): AdmissionOperationAttenduNotification
    {
        $vars = $this->createTemplateVarsForOperation($operationAttendue, $notificationOperationAttendue);

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

        $vars = $this->createTemplateVarsForOperation($admissionValidation);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_AJOUTEE, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();;
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

        $vars = $this->createTemplateVarsForOperation($admissionValidation);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_SUPPRIMEE, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();
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

    private function createTemplateVarsForOperation(AdmissionOperationInterface $operationAttendue,
                                                    ?AdmissionOperationAttenduNotification $notificationOperationAttendue = null): array
    {
        $admission = $operationAttendue->getAdmission();
        $individu = $admission->getIndividu();

        $individuTemplateVariable = $this->getIndividuTemplateVariable($individu);
        $admissionAdmissionTemplateVariable = $this->getAdmissionAdmissionTemplateVariable($admission);
        $admissionAdmissionTemplateVariable->setOperationAttenduNotificationAnomalies($notificationOperationAttendue?->getAnomalies());
        $admissionOperationTemplateVariable = $this->getAdmissionOperationTemplateVariable($operationAttendue);

        $this->urlService->setVariables([
            'individu' => $individuTemplateVariable,
            'admission' => $admissionAdmissionTemplateVariable,
            'typeValidation' => $operationAttendue,
        ]);

        return [
            'individu' => $individuTemplateVariable,
            'admission' => $admissionAdmissionTemplateVariable,
//            'typeValidation' => $operationAttendue, // remplacée par 'admissionOperation'
            'admissionOperation' => $admissionOperationTemplateVariable,
//            'anomalies' => $notificationOperationAttendue // absorbé par 'admission'
            'Url' => $this->urlService,
        ];
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

//        $vars = [
//            'admission' => $admission,
//            'admissionAvis' => $admissionAvis,
//            'individu' => $individu,
//            'typeValidation' => $admissionAvis->getAvis()->getAvisType(),
//            'anomalies' => $notificationAvisAdmission
//        ];
//        $url = $this->urlService->setVariables($vars);
//        $vars['Url'] = $url;
        $vars = $this->createTemplateVarsForAvis($admissionAvis, $notificationAvisAdmission);

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        $cc = [];
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $inscription->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();
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

//        $vars = [
//            'admission' => $admission,
//            'admissionAvis' => $admissionAvis,
//            'individu' => $individu,
//            'typeValidation' => $admissionAvis->getAvis()->getAvisType(),
//            'anomalies' => $notificationAvisAdmission
//        ];
//        $url = $this->urlService->setVariables($vars);
//        $vars['Url'] = $url;
        $vars = $this->createTemplateVarsForAvis($admissionAvis, $notificationAvisAdmission);

        $cc = [];
        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();
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

//        $vars = [
//            'admission' => $admission,
//            'admissionAvis' => $admissionAvis,
//            'individu' => $individu,
//            'typeValidation' => $admissionAvis->getAvis()->getAvisType()
//        ];
//        $url = $this->urlService->setVariables($vars);
//        $vars['Url'] = $url;
        $vars = $this->createTemplateVarsForAvis($admissionAvis);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::AVIS_SUPPRIME, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();
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

        $vars = $this->createTemplateVarsForAvis($admissionAvis);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::NOTIFICATION_DECLARATION_DOSSIER_INCOMPLET, $vars);

        $notif = new Notification();
        $notif->setTo([$email => $admission->getIndividu()->getNomComplet()])
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        if(!empty($inscription->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $directeur->getEmailPro() ?: $directeur->getEmailUtilisateur() ?: $inscription->getEmailDirecteurThese();;
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

    private function createTemplateVarsForAvis(AdmissionAvis $admissionAvis, ?AdmissionAvisNotification $notificationAvisAdmission = null): array
    {
        $admission = $admissionAvis->getAdmission();
        $individu = $admission->getIndividu();

        $this->urlService->setVariables([
            'admission' => $admission,
            'admissionAvis' => $admissionAvis,
            'individu' => $individu,
        ]);

        $individuTemplateVariable = $this->getIndividuTemplateVariable($individu);
        $admissionAdmissionTemplateVariable = $this->getAdmissionAdmissionTemplateVariable($admission);
        $admissionAdmissionTemplateVariable->setAdmissionAvisNotificationAnomalies($notificationAvisAdmission?->getAnomalies());
        $admissionOperationTemplateVariable = $this->getAdmissionOperationTemplateVariable($admissionAvis);

        return [
            'admission' => $admissionAdmissionTemplateVariable,
//            'admissionAvis' => $admissionAvisTemplateVariable, // remplacé par 'admissionOperation'
            'individu' => $individuTemplateVariable,
//            'typeValidation' => $admissionAvis->getAvis()->getAvisType(), // remplacé par 'admissionOperation'
            'admissionOperation' => $admissionOperationTemplateVariable,
//            'anomalies' => $notificationAvisAdmission // enlevé car macro modifiée pour utiliser la var 'admission'
            'Url' => $this->urlService,
        ];
    }
}