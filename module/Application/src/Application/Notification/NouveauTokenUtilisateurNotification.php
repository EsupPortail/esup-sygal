<?php

namespace Application\Notification;

use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\UtilisateurToken;
use Notification\Notification;

class NouveauTokenUtilisateurNotification extends Notification
{
    protected $templatePath = 'application/notification/mail/notif-nouveau-token-utilisateur';
    /** @var Utilisateur */
    protected $utilisateur;
    /** @var UtilisateurToken */
    protected $token;
    /** @var string */
    protected $lien;

    /**
     * @return static
     */
    public function prepare()
    {
        $to = $this->utilisateur->getEmail();
        $cc = null;

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé à la Maison du doctorat (%s)",
            $to,
        );

        $errorMessage = null;
        if ($to === null or trim($to) === '') {
            $errorMessage = sprintf(
                "<strong>NB:</strong> L'utilisateur %s n'a pas pu être notifié " .
                "car leur adresse électronique n'est pas connue.",
                $this->utilisateur->getDisplayName()
            );
        }

        $this
            ->setSubject("Création d'un jeton d'authentification pour l'application " . $this->appInfos()->nom)
            ->setTo($to)
            ->setCc($cc)
            ->setTemplateVariables([
                'message' => $errorMessage,
                'utilisateur' => $this->utilisateur,
                'token' => $this->token,
                'lien' => $this->lien,
            ]);

        $this->setInfoMessages($infoMessage);
        if ($errorMessage) {
            $this->setWarningMessages($errorMessage);
        }

        return $this;
    }

}