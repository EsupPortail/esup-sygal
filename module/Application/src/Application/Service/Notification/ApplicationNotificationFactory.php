<?php

namespace Application\Service\Notification;

use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Individu\Entity\Db\Individu;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use UnicaenApp\Exception\LogicException;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class ApplicationNotificationFactory extends NotificationFactory
{
    /**
     * @var UrlHelper
     */
    protected UrlHelper $urlHelper;

    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Notification pour confirmation d'une adresse mail.
     *
     * @param MailConfirmation $mailConfirmation
     * @param string $confirmUrl
     * @return \Notification\Notification
     */
    public function createNotificationMailConfirmation(MailConfirmation $mailConfirmation, string $confirmUrl): Notification
    {
        $notif = new Notification();
        $notif
            ->setSubject("Confirmation de votre adresse électronique")
            ->setTo($mailConfirmation->getEmail())
            ->setTemplatePath('doctorant/mail/demande-confirmation-mail')
            ->setTemplateVariables([
                'destinataire' => $mailConfirmation->getIndividu()->getNomUsuel(),
                'confirmUrl' => $confirmUrl,
            ]);

        return $notif;
    }

    /**
     * Notification à propos d'un rôle attribué/retiré à un utilisateur.
     *
     * @var string $type
     * @var Role $role
     * @var Individu $individu
     */
    public function createNotificationChangementRole($type, $role, $individu): Notification
    {
        $mail = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if ($mail === null) {
            throw new RuntimeException("Aucun email dispo pour l'individu {$individu}");
        }

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

        return $notif;
    }

    /**
     * Notification à propos de la création d'un compte local.
     *
     * @param Utilisateur $utilisateur
     * @param string $token
     * @return \Notification\Notification
     */
    public function createNotificationInitialisationCompte(Utilisateur $utilisateur, string $token): Notification
    {
        $email = $utilisateur->getEmail();
        if ($email === null) {
            throw new RuntimeException("Aucun email dispo pour l'utilisateur {$utilisateur->getUsername()} !");
        }

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        $notif = new Notification();
        $notif
            ->setSubject("Initialisation de votre compte")
            ->setTo($email)
            ->setTemplatePath('application/utilisateur/mail/init-compte')
            ->setTemplateVariables([
                'username' => $utilisateur->getUsername(),
                'url' => $this->urlHelper->__invoke('utilisateur/init-compte', ['token' => $token], ['force_canonical' => true], true),
            ]);

        return $notif;
    }

    /**
     * Notification à propos de la réinitialisation d'un compte local.
     *
     * @param Utilisateur $utilisateur
     * @param string $url
     */
    public function createNotificationResetCompte($utilisateur, $url): Notification
    {
        $email = $utilisateur->getEmail();
        if ($email === null) {
            throw new RuntimeException("Aucun email dispo pour l'utilisateur {$utilisateur->getUsername()}");
        }

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        $notif = new Notification();
        $notif
            ->setSubject("Réinitialisation de votre mot de passe de votre compte")
            ->setTo($email)
            ->setTemplatePath('application/utilisateur/mail/reinit-compte')
            ->setTemplateVariables([
                'username' => $utilisateur->getUsername(),
                'url' => $url,
            ]);

        return $notif;
    }

    /**
     * Notification à propos d'abonnés de liste de diffusion sans adresse connue.
     *
     * @param string[] $to
     * @param string $liste
     * @param string[] $individusAvecAdresse
     * @param string[] $individusSansAdresse
     * @return \Notification\Notification
     */
    public function createNotificationAbonnesListeDiffusionSansAdresse(
        array  $to,
        string $liste,
        array  $individusAvecAdresse,
        array  $individusSansAdresse): Notification
    {
        $to = array_unique(array_filter($to));

        $notif = new Notification();
        $notif
            ->setSubject("Abonnés de liste de diffusion sans adresse mail")
            ->setTo($to)
            ->setTemplatePath('application/liste-diffusion/mail/notif-abonnes-sans-adresse')
            ->setTemplateVariables([
                'liste' => $liste,
                'individusAvecAdresse' => $individusAvecAdresse,
                'individusSansAdresse' => $individusSansAdresse,
            ]);

        return $notif;
    }
}