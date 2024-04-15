<?php

namespace Formation\Service\Notification;

use Application\Entity\Db\Role;
use Application\Service\ListeDiffusion\ListeDiffusionServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Template\MailTemplates;
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
    use RoleServiceAwareTrait;

    /** INSCRIPTION ***************************************************************************************************/

    public function createNotificationInscriptionEnregistree(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];

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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
            'inscription' => $inscription,
        ];

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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session' => $inscription->getSession(),
        ];
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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];
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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
        ];
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
        $vars = [
            'formation' => $session->getFormation(),
            'session'   => $session,
        ];
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

    public function createNotificationSessionTerminee(Inscription $inscription) : Notification
    {
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session'   => $inscription->getSession(),
            'inscription'   => $inscription,
        ];
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
        $vars = [
            'doctorant' => $inscription->getDoctorant(),
            'formation' => $inscription->getSession()->getFormation(),
            'session' => $inscription->getSession(),
        ];
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

    /** FORMATIONS ******************************************************************************************************/
    public function createNotificationFormationSpecifiqueAjoutee(Formation $formation): Notification
    {
        $ed = $formation->getTypeStructure()->getEcoleDoctorale();
        $site = $formation->getSite();
        $role = $this->roleService->getRepository()->findOneByCodeAndStructure(Role::CODE_DOCTORANT, $site->getStructure());
        $ng = $this->listeDiffusionService->createNameGenerator($ed, $role, $role->getStructure());
        $domain = $this->listeDiffusionService->getEmailDomain();
        $ng->setDomain($domain);

        $vars = [
            'formation' => $formation,
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::FORMATION_SPECIFIQUE_AJOUTEE, $vars);
        $mail = $ng->generateName();

        $listesDiffusionActives = $this->listeDiffusionService->fetchListesDiffusionActives();
        foreach ($listesDiffusionActives as $listeDiffusion) {
            $listesDiffusionActivess[$listeDiffusion->getAdresse()] = $listeDiffusion;
        }
        $adressesListesActives = array_keys($listesDiffusionActivess);

        //si une liste a été récupérée et qu'elle n'est pas active
        if(!$mail || !in_array($mail, $adressesListesActives)){
            throw new RuntimeException("Aucune liste de diffusion trouvée pour l'ED {$ed}.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }
}