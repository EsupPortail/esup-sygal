<?php

namespace Soutenance\Service\Notification;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\Email\EmailServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Candidat\Entity\Db\Candidat;
use Candidat\Renderer\CandidatTemplateVariable;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Renderer\DoctorantTemplateVariable;
use HDR\Entity\Db\HDR;
use HDR\Renderer\HDRTemplateVariable;
use Individu\Entity\Db\Individu;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Template\MailTemplates;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Url\UrlServiceAwareTrait;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceAwareTrait;
use These\Entity\Db\These;
use These\Renderer\TheseTemplateVariable;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationHDR;
use Validation\Entity\Db\ValidationThese;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;

/** Todo à déplacer dans UnicaenRenderer dans les prochaines versions */
class StringElement
{
    public string $texte = "Aucun texte";

    public function getTexte(): string
    {
        return $this->texte;
    }
}

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class SoutenanceNotificationFactory extends NotificationFactory
{
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use EmailServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    public function createNotificationDevalidationProposition(These|HDR $entity, ValidationThese|ValidationHDR $validation): Notification
    {
        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_ANNULEE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_ANNULEE, $vars) ;
        $mail = $validation->getIndividu()->getEmailUtilisateur();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour " . $validation->getIndividu()->getNomComplet());
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    protected function getEntityTemplateVariable(These|HDR $entity): TheseTemplateVariable|HDRTemplateVariable
    {
        if ($entity instanceof These) {
            return $this->getTheseTemplateVariable($entity);
        } else {
            return $this->getHDRTemplateVariable($entity);
        }
    }

    protected function getApprenantTemplateVariable(Doctorant|Candidat $apprenant): DoctorantTemplateVariable|CandidatTemplateVariable
    {
        if ($apprenant instanceof Doctorant) {
            return $this->getDoctorantTemplateVariable($apprenant);
        } else {
            return $this->getCandidatTemplateVariable($apprenant);
        }
    }

    public function createNotificationValidationProposition(These|HDR $entity, ValidationThese|ValidationHDR $validation): Notification
    {
        $emails = $this->emailService->fetchEmailActeursDirects($entity);
        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });
        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les acteurs directs de la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_ACTEUR_DIRECT, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_ACTEUR_DIRECT, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationUniteRechercheProposition(These|HDR $entity): Notification
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($entity->getUniteRecherche()->getStructure(), null, $entity->getEtablissement());
        //
        // todo : quid si rien pour l'établissement spécifié ? => il faut bien pouvoir notifier qqun, non ?!
        // if (!$individuRoles) {
        //     // tentative sans contrainte sur l'établissement
        //     $individuRoles = $this->roleService->findIndividuRoleByStructure($entity->getUniteRecherche()->getStructure());
        // }
        //
        $emails = $this->emailService->collectEmailsFromIndividuRoles($individuRoles);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'unité de recherche de la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_DEMANDE_UR, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_DEMANDE_UR, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationEcoleDoctoraleProposition(These|HDR $entity): Notification
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($entity->getEcoleDoctorale()->getStructure(), null, $entity->getEtablissement());
        $emails = $this->emailService->collectEmailsFromIndividuRoles($individuRoles);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'école doctorale de la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());
        $ecoleDoctoraleTemplateVariable = $this->getStructureTemplateVariable($entity->getEcoleDoctorale());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'ecole-doctorale' => $ecoleDoctoraleTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'ecole-doctorale' => $ecoleDoctoraleTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_DEMANDE_ED, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_DEMANDE_ED, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationBureauDesDoctoratsProposition(These|HDR $entity): Notification
    {
        if ($entity instanceof These) {
            $emails = $this->emailService->fetchEmailAspectsDoctorat($entity);
            $vars = [
                'these' => $entity,
            ];
        } else {
            $emails = $this->emailService->fetchEmailGestionnairesHDR($entity);
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_DEMANDE_ETAB, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_DEMANDE_ETAB, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationPropositionValidee(These|HDR $entity): Notification
    {
        if($entity instanceof These){
            $emailsBDD = $this->emailService->fetchEmailAspectsDoctorat($entity);
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emails = array_merge($emailsBDD, $emailsED, $emailsUR, $emailsActeurs);
        }else{
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsGestHDR = $this->emailService->fetchEmailGestionnairesHDR($entity);
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emails = array_merge($emailsGestHDR, $emailsUR, $emailsActeurs);
        }

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationSuppressionProposition(These|HDR $entity): Notification
    {
        if($entity instanceof These){
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emails = array_merge($emailsED, $emailsUR, $emailsActeurs);
        }else{
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emails = array_merge($emailsUR, $emailsActeurs);
        }
        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });
        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les acteurs directs de la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_PROPOSITION_SUPPRESSION, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_PROPOSITION_SUPPRESSION, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationPresoutenance(These|HDR $entity): Notification
    {
        if($entity instanceof These){
            $emails = $this->emailService->fetchEmailAspectsDoctorat($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$entity->getId()}");
            }
            $vars = [
                'these' => $entity,
            ];
        }else{
            $emails = $this->emailService->fetchEmailGestionnairesHDR($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la/le gestionnaire HDR de l'HDR {$entity->getId()}");
            }
            $vars = [
                'hdr' => $entity,
            ];
        }

        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationRefusPropositionSoutenance(These|HDR $entity, Individu $currentUser, Role $currentRole, string $motif): Notification
    {
        $emails = $this->emailService->fetchEmailActeursDirects($entity);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour les acteurs directs de la thèse {$entity->getId()}");
        }

        $refus = new StringElement();
        $refus->texte = $motif;

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);
        
        $individuTemplateVariable = $this->getIndividuTemplateVariable($currentUser);
        $roleTemplateVariable = $this->getRoleTemplateVariable($currentRole);
        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        if ($entity instanceof These) {
            $vars = [
                'individu' => $individuTemplateVariable,
                'role' => $roleTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'stringelement' => $refus,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'individu' => $individuTemplateVariable,
                'role' => $roleTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'stringelement' => $refus,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_PROPOSITION_REFUS, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_PROPOSITION_REFUS, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    public function createNotificationDemandeSignatureEngagementImpartialite(These|HDR $entity, Membre $membre): Notification
    {
        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
                'rapporteur' => $membre,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
                'rapporteur' => $membre,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_DEMANDE_ENGAGEMENT_IMPARTIALITE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_DEMANDE_ENGAGEMENT_IMPARTIALITE, $vars) ;
//        $mail = $membre->getActeur()?->getEmail(true);
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        $mail = $acteur?->getEmail(true);
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationSignatureEngagementImpartialite(These|HDR $entity, Membre $membre): Notification
    {
        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
            ];
        }

        if($entity instanceof These){
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_SIGNATURE_ENGAGEMENT_IMPARTIALITE, $vars);
            $emails = $this->emailService->fetchEmailAspectsDoctorat($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$entity->getId()}");
            }
        }else{
            $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_SIGNATURE_ENGAGEMENT_IMPARTIALITE, $vars);
            $emails = $this->emailService->fetchEmailGestionnairesHDR($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la/le gestionnaire HDR de l'HDR {$entity->getId()}");
            }
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationRefusEngagementImpartialite(These|HDR $entity, Membre $membre): Notification
    {
        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembre,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_REFUS_ENGAGEMENT_IMPARTIALITE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_REFUS_ENGAGEMENT_IMPARTIALITE, $vars) ;

        if($entity instanceof These){
            $emailsAD = $this->emailService->fetchEmailActeursDirects($entity);
            $emailsBDD = $this->emailService->fetchEmailAspectsDoctorat($entity);
            $emails = array_merge($emailsAD, $emailsBDD);
        }else{
            $emailsAD = $this->emailService->fetchEmailActeursDirects($entity);
            $emailsGestHDR = $this->emailService->fetchEmailGestionnairesHDR($entity);
            $emails = array_merge($emailsAD, $emailsGestHDR);
        }

        if (empty($emails)) {
            throw new RuntimeException("Aucun mail trouvé");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAnnulationEngagementImpartialite(These|HDR $entity, Membre $membre): Notification
    {
        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_ANNULATION_ENGAGEMENT_IMPARTIALITE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_ANNULATION_ENGAGEMENT_IMPARTIALITE, $vars) ;
        $mail = $membre->getEmail();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }


    /**************************** avis ***************************/

    public function createNotificationAvisRendus(These|HDR $entity): Notification
    {
        if($entity instanceof These){
            $emails = $this->emailService->fetchEmailAspectsDoctorat($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$entity->getId()}");
            }
            $vars = [
                'these' => $entity,
            ];
        }else{
            $emails = $this->emailService->fetchEmailGestionnairesHDR($entity);
            if (empty($emails)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la/le gestionnaire HDR de l'HDR {$entity->getId()}");
            }
            $vars = [
                'hdr' => $entity,
            ];
        }

        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_TOUS_AVIS_RENDUS, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_TOUS_AVIS_RENDUS, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisRendusDirection(These|HDR $entity): Notification
    {
        $email = $this->emailService->fetchEmailEncadrants($entity);
        if (empty($email)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour les encadrants de la thèse");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_TOUS_AVIS_RENDUS_DIRECTION, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_TOUS_AVIS_RENDUS_GARANT, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisFavorable(These|HDR $entity, Avis $avis): Notification
    {
        if($entity instanceof These){
            $emailBDD = $this->emailService->fetchEmailAspectsDoctorat($entity);
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsDirecteurs = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);
        }else{
            $emailsGestHDR = $this->emailService->fetchEmailGestionnairesHDR($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsGarants = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailsGestHDR, $emailsGarants, $emailsUR);
        }

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_THESE_AVIS_FAVORABLE . "] la thèse {$entity->getId()}");
        }

        $membre = $avis->getMembre();
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
                'membre' => $avis->getMembre(),
                'rapporteur' => $acteur,
                'avis' => $avis,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
                'membre' => $avis->getMembre(),
                'rapporteur' => $acteur,
                'avis' => $avis,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($avis->getMembre());
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $acteurTemplateVariable = $this->getActeurTemplateVariable($acteur);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'acteur' => $acteurTemplateVariable,
//            'avis' => $avis, // enlevé car aucune macro utilisant une variable 'avis'
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'acteur' => $acteurTemplateVariable,
//            'avis' => $avis, // enlevé car aucune macro utilisant une variable 'avis'
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_AVIS_FAVORABLE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_AVIS_FAVORABLE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisDefavorable(These|HDR $entity, Avis $avis): Notification
    {
        if($entity instanceof These){
            $emailBDD = $this->emailService->fetchEmailAspectsDoctorat($entity);
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsDirecteurs = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);
        }else{
            $emailsGestHDR = $this->emailService->fetchEmailGestionnairesHDR($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsGarants = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailsGestHDR, $emailsGarants, $emailsUR);
        }

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_THESE_AVIS_DEFAVORABLE . "] la thèse {$entity->getId()}");
        }

        $membre = $avis->getMembre();
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
                'membre' => $avis->getMembre(),
                'rapporteur' => $acteur,
                'avis' => $avis,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
                'membre' => $avis->getMembre(),
                'rapporteur' => $acteur,
                'avis' => $avis,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($avis->getMembre());
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $acteurTemplateVariable = $this->getActeurTemplateVariable($acteur);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());

        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'acteur' => $acteurTemplateVariable,
                'Avis' => $avis,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'acteur' => $acteurTemplateVariable,
                'Avis' => $avis,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_AVIS_DEFAVORABLE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_AVIS_DEFAVORABLE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }


    /**************************** présoutenance ***************************/

    public function createNotificationDemandeAvisSoutenance(These|HDR $entity, Membre $rapporteur): Notification
    {
//        $email = $rapporteur->getActeur()?->getEmail(true);
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($rapporteur);
        $email = $acteur?->getEmail(true);
        if ($email === null) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_THESE_DEMANDE_PRERAPPORT . "] la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
                'rapporteur' => $acteur,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
                'rapporteur' => $acteur,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($rapporteur);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($rapporteur->getProposition());

        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_DEMANDE_PRERAPPORT, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_DEMANDE_PRERAPPORT, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationFeuVertSoutenance(Proposition $proposition): Notification
    {
        $entity = $proposition->getObject();

        if($entity instanceof These){
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);
        }else{
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsGarants = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailsGarants, $emailsUR);
        }

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_THESE_FEU_VERT . "] la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'soutenance' => $proposition,
                'these' => $entity,
            ];
        } else {
            $vars = [
                'soutenance' => $proposition,
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($entity->getUniteRecherche());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        if ($entity instanceof These) {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'unite-recherche' => $uniteRechercheTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_FEU_VERT, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_FEU_VERT, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationStopperDemarcheSoutenance($entity): Notification
    {
        if($entity instanceof These){
            $emailsActeurs = $this->emailService->fetchEmailActeursDirects($entity);
            $emailsED = $this->emailService->fetchEmailEcoleDoctorale($entity);
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);
        }else{
            $emailsUR = $this->emailService->fetchEmailUniteRecherche($entity);
            $emailsGarants = $this->emailService->fetchEmailEncadrants($entity);
            $emails = array_merge($emailsGarants, $emailsUR);
        }

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_THESE_STOP_DEMARCHE . "] la thèse {$entity->getId()}");
        }

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_STOP_DEMARCHE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_STOP_DEMARCHE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** Mail concernants les rapporteur·trices ************************************************************************/

    public function createNotificationConnexionRapporteur(Proposition $proposition, Membre $rapporteur): Notification
    {
        $mail = $rapporteur->getEmail();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur " . $rapporteur->getDenomination() . " (id:" . $rapporteur->getId() . ")");
        }

        $entity = $proposition->getObject();

        if ($entity instanceof These) {
            $vars = [
                'soutenance' => $proposition,
                'these' => $entity,
                'rapporteur' => $rapporteur,
            ];
        } else {
            $vars = [
                'soutenance' => $proposition,
                'hdr' => $entity,
                'rapporteur' => $rapporteur,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($rapporteur);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        if ($entity instanceof These) {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_CONNEXION_RAPPORTEUR, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_CONNEXION_RAPPORTEUR, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationNotificationRapporteurRetard(Membre $membre): Notification
    {
        $proposition = $membre->getProposition();
        $entity = $proposition->getObject();
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
//        if ($membre->getActeur() === null) {
        if ($acteur === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun acteur n'est lié.");
        }
        $email = $membre->getEmail();
        if ($email === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun email n'est connu");
        }

        if ($entity instanceof These) {
            $vars = [
                'soutenance' => $proposition,
                'these' => $entity,
                'rapporteur' => $membre,
            ];
        } else {
            $vars = [
                'soutenance' => $proposition,
                'hdr' => $entity,
                'rapporteur' => $membre,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        if ($entity instanceof These) {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceMembre' => $soutenanceMembreTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_DEMANDE_RAPPORT_SOUTENANCE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_DEMANDE_RAPPORT_SOUTENANCE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** Mails de fin de procédure *************************************************************************************/

    public function createNotificationEnvoiConvocationApprenant(Doctorant|Candidat $apprenant, Proposition $proposition): Notification
    {
        $entity = $proposition->getObject();
        if ($proposition instanceof PropositionThese) {
            $vars = [
                'soutenance' => $proposition,
                'these' => $entity,
            ];
            $templateCode = MailTemplates::SOUTENANCE_THESE_CONVOCATION_DOCTORANT;
        } else {
            $vars = [
                'soutenance' => $proposition,
                'hdr' => $entity,
            ];
            $templateCode = MailTemplates::SOUTENANCE_HDR_CONVOCATION_CANDIDAT;
        }
        $email = $apprenant->getIndividu()->getEmailUtilisateur();
        if ($email === null) {
            throw new RuntimeException("Aucun mail pour la notification [" . $templateCode . "]");
        }

        $validation = $entity instanceof These ?
            $this->validationTheseService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $entity) :
            $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $entity);
        if (empty($validation)) {
            throw new RuntimeException("Aucune validation de trouvée");
        }

        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($apprenant);
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation[0]);
        if ($entity instanceof These) {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'validation' => $validationTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $this->getRenduService()->generateRenduByTemplateCode($templateCode, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationEnvoiConvocationMembre(ActeurThese|ActeurHDR $acteur, Proposition $proposition): Notification
    {
        $entity = $proposition->getObject();
        if($proposition instanceof PropositionThese){
            $templateCode = MailTemplates::SOUTENANCE_THESE_CONVOCATION_DOCTORANT;
        }else{
            $templateCode = MailTemplates::SOUTENANCE_HDR_CONVOCATION_CANDIDAT;
        }

        $membre = $acteur->getMembre();
        //TODO
        $email = $membre->getEmail();
        $apprenant = $entity->getApprenant();

        if ($email === null) {
            throw new RuntimeException("Aucun mail pour la notification [" . $templateCode . "]");
        }
        $validation = $entity instanceof These ?
            $this->validationTheseService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $entity) :
            $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $entity);
        if (empty($validation)) {
            throw new RuntimeException("Aucune validation de trouvée");
        }

        if ($entity instanceof These) {
            $vars = [
                'soutenance' => $proposition,
                'these' => $entity,
                'membre' => $membre,
            ];
        } else {
            $vars = [
                'soutenance' => $proposition,
                'hdr' => $entity,
                'membre' => $membre,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($apprenant);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation[0]);
        $soutenanceActeurTemplateVariable = $this->getSoutenanceActeurTemplateVariable($acteur);
        if ($entity instanceof These) {
            $soutenanceActeurTemplateVariable->setActeursPouvantEtrePresidentDuJury(
                $this->acteurTheseService->getRepository()->findAllActeursPouvantEtrePresidentDuJury($proposition)
            );
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'soutenanceActeur' => $soutenanceActeurTemplateVariable,
                'validation' => $validationTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $soutenanceActeurTemplateVariable->setActeursPouvantEtrePresidentDuJury(
                $this->acteurHDRService->getRepository()->findAllActeursPouvantEtrePresidentDuJury($proposition)
            );
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'soutenanceActeur' => $soutenanceActeurTemplateVariable,
                'validation' => $validationTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_CONVOCATION_MEMBRE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_CONVOCATION_MEMBRE, $vars) ;
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationTransmettreDocumentsDirection(These|HDR $entity, Proposition $proposition): Notification
    {
        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($entity->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        if ($entity instanceof These) {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'these' => $entityTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
            $mail = array_merge($entity->getDirecteursTheseEmails(), $entity->getCoDirecteursTheseEmails());
            if (count($mail) === 0) {
                throw new RuntimeException("Aucun mail trouvé pour les directeurs de thèse");
            }
        } else {
            $vars = [
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'hdr' => $entityTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'etablissement' => $etablissementTemplateVariable,
                'Url' => $this->urlService,
            ];
            $mail = $entity->getGarantEmails();
            if (count($mail) === 0) {
                throw new RuntimeException("Aucun mail trouvé pour le garant");
            }
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_TRANSMETTRE_DOCUMENTS_DIRECTION, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_TRANSMETTRE_DOCUMENTS_GARANT, $vars) ;

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationDemandeAdresse(?Proposition $proposition): Notification
    {
        $entity = $proposition->getObject();

        if ($entity instanceof These) {
            $vars = [
                'these' => $entity,
            ];
        } else {
            $vars = [
                'hdr' => $entity,
            ];
        }
        $this->urlService->setVariables($vars);

        $entityTemplateVariable = $this->getEntityTemplateVariable($entity);
        $apprenantTemplateVariable = $this->getApprenantTemplateVariable($entity->getApprenant());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        if ($entity instanceof These) {
            $vars = [
                'these' => $entityTemplateVariable,
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'doctorant' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        } else {
            $vars = [
                'hdr' => $entityTemplateVariable,
                'soutenanceProposition' => $soutenancePropositionTemplateVariable,
                'candidat' => $apprenantTemplateVariable,
                'Url' => $this->urlService,
            ];
        }

        $rendu = $entity instanceof These ?
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_THESE_DEMANDE_ADRESSE_EXACTE, $vars) :
            $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_DEMANDE_ADRESSE_EXACTE, $vars) ;
        $emails = $this->emailService->fetchEmailActeursDirects($entity);
        if (count($emails) === 0) {
            throw new RuntimeException("Aucun mail trouvé pour les acteurs directs");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationDemandeSaisieInfosSoutenance(HDR $hdr): Notification
    {
        $vars = [
            'hdr' => $hdr,
        ];

        $this->urlService->setVariables($vars);

        $vars = [
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_HDR_DEMANDE_SAISIE_INFOS_SOUTENANCE, $vars) ;
        $email = $hdr->getApprenant()->getIndividu()->getEmailUtilisateur();
        if ($email === null) {
            throw new RuntimeException("Aucun mail pour la notification [" . MailTemplates::SOUTENANCE_HDR_DEMANDE_SAISIE_INFOS_SOUTENANCE . "]");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());

        return $notif;
    }
}