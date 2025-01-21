<?php

namespace Formation\Service\Notification;

use Application\Entity\Db\Role;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\ListeDiffusion\ListeDiffusionServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Entity\Db\SessionStructureValide;
use Formation\Provider\Template\MailTemplates;
use Formation\Service\Url\UrlServiceAwareTrait;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class FormationNotificationFactory extends NotificationFactory
{
    use RenduServiceAwareTrait;
    use ListeDiffusionServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;
    use UrlServiceAwareTrait;

    /** INSCRIPTION ***************************************************************************************************/

    public function createNotificationInscriptionEnregistree(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ENREGISTREE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionListePrincipale(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_PRINCIPALE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionListeComplementaire(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//            'inscription' => $inscription,
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_LISTE_COMPLEMENTAIRE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationInscriptionClose(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session' => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_CLOSE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationInscriptionEchec(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::INSCRIPTION_ECHEC, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    /** SESSIONS ******************************************************************************************************/

    public function createNotificationSessionImminente(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_IMMINENTE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    public function createNotificationSessionImminenteFormateur(Session $session) : Notification
    {
//        $vars = [
//            'formation' => $session->getFormation(),
//            'session'   => $session,
//        ];
        $vars = $this->createTemplateVarsForSession($session);
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_IMMINENTE_FORMATEUR, $vars);

        $mails = [];
        /** @var Formateur $formateur */
        foreach ($session->getFormateurs() as $formateur) {
            $mail = $formateur->getIndividu()->getEmailUtilisateur()??$formateur->getIndividu()->getEmailPro();
            if ($mail !== null) $mails[] = $mail;
        }

        if (empty($mails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les formateurs de la session {$session->getCode()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mails)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    private function createTemplateVarsForSession(Session $session): array
    {
        $formationTemplateVariable = $this->getFormationTemplateVariable($session->getFormation());
        $formationSessionTemplateVariable = $this->getFormationSessionTemplateVariable($session);

        return [
            'formation' => $formationTemplateVariable,
            'session' => $formationSessionTemplateVariable,
        ];
    }

    public function createNotificationSessionTerminee(Inscription $inscription) : Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session'   => $inscription->getSession(),
//            'inscription'   => $inscription,
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_TERMINEE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;
        return $notif;
    }

    public function createNotificationSessionAnnulee(Inscription $inscription): Notification
    {
//        $vars = [
//            'doctorant' => $inscription->getDoctorant(),
//            'formation' => $inscription->getSession()->getFormation(),
//            'session' => $inscription->getSession(),
//        ];
        $vars = $this->createTemplateVarsForInscription($inscription);
        unset($vars['inscription']);

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SESSION_ANNULEE, $vars);
        $mail = $inscription->getDoctorant()->getIndividu()->getEmailPro();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$inscription->getDoctorant()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }

    private function createTemplateVarsForInscription(Inscription $inscription): array
    {
        $session = $inscription->getSession();
        $doctorant = $inscription->getDoctorant();

        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($doctorant);
        $formationTemplateVariable = $this->getFormationTemplateVariable($session->getFormation());
        $formationSessionTemplateVariable = $this->getFormationSessionTemplateVariable($session);
        $formationInscriptionTemplateVariable = $this->getFormationInscriptionTemplateVariable($inscription);

        return [
            'doctorant' => $doctorantTemplateVariable,
            'session' => $formationSessionTemplateVariable,
            'formation' => $formationTemplateVariable,
            'inscription' => $formationInscriptionTemplateVariable,
        ];
    }

    public function createNotificationTransmettreInscritsSession(Session $session): Notification
    {
        $formationTemplateVariable = $this->getFormationTemplateVariable($session->getFormation());
        $formationSessionTemplateVariable = $this->getFormationSessionTemplateVariable($session);

        $this->urlService->setVariables([
            'session' => $session,
        ]);

        $vars = [
            'session' => $formationSessionTemplateVariable,
            'formation' => $formationTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::TRANSMETTRE_LISTE_INSCRITS_FORMATEURS, $vars);

        $mails = [];
        /** @var Formateur $formateur */
        foreach ($session->getFormateurs() as $formateur) {
            $mail = $formateur->getIndividu()->getEmailUtilisateur()??$formateur->getIndividu()->getEmailPro();
            if ($mail !== null) $mails[] = $mail;
        }

        if (empty($mails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les formateurs de la session {$session->getCode()}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mails)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps())
        ;

        return $notif;
    }

    /** FORMATIONS ******************************************************************************************************/
    public function createNotificationFormationSpecifiqueAjoutee(Formation $formation, SessionStructureValide $structureValide): Notification
    {
        $ed = $structureValide->getStructure()->getEcoleDoctorale(); // NB: est null si la structure n'est pas une ED
        $role = $this->applicationRoleService->getRepository()->findByCode(Role::CODE_DOCTORANT);
        $etablissement = null;
        if ($structureValide->getStructure()->getTypeStructure()->isEtablissement()) {
            $etablissement = $structureValide->getStructure();
        }

        $ng = $this->listeDiffusionService->createNameGenerator($ed, $role, $etablissement);
        $domain = $this->listeDiffusionService->getEmailDomain();
        $ng->setDomain($domain);

//        $vars = [
//            'formation' => $formation,
//        ];
        $vars = $this->createTemplateVarsForFormation($formation);
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::FORMATION_SPECIFIQUE_AJOUTEE, $vars);
        $mail = $ng->generateName();

        // si la liste de diff ne fait pas partie de celles activées, stop
        if (!array_key_exists($mail, $this->listeDiffusionService->fetchListesDiffusionActives())) {
            throw new RuntimeException("L'adresse de destination $mail ne fait pas partie des listes de diffusion actives.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }

    private function createTemplateVarsForFormation(Formation $formation): array
    {
        $formationTemplateVariable = $this->getFormationTemplateVariable($formation);

        return [
            'formation' => $formationTemplateVariable,
        ];
    }
}