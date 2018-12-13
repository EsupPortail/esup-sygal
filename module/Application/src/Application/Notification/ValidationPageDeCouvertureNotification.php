<?php

namespace Application\Notification;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;
use UnicaenApp\Exception\LogicException;

class ValidationPageDeCouvertureNotification extends Notification
{
    use TheseAwareTrait;

    const ACTION_VALIDER = 'valider';
    const ACTION_DEVALIDER = 'devalider';

    protected $templatePath = 'application/notification/mail/notif-validation-page-couverture';

    private $action;

    /**
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        if (!in_array($action, $actions = [self::ACTION_VALIDER, self::ACTION_DEVALIDER])) {
            throw new LogicException(sprintf(
                "L'action '%s' spécifiée ne fait pas partie des actions possibles : %s.",
                $action,
                implode(', ', $actions)
            ));
        }

        $this->action = $action;

        return $this;
    }

    /**
     * @return static
     */
    public function prepare()
    {
        /** @var Individu[] $individusSansMail */
        $individusSansMail = [];
        $to = $this->these->getDoctorant()->getIndividu()->getEmail();
        $cc = $this->these->getDirecteursTheseEmails($individusSansMail);

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé au doctorant avec le(s) directeur(s) de thèse en copie (%s)",
            $to,
            implode(',', $cc)
        );

        $errorMessage = null;
        if (count($individusSansMail)) {
            $temp = current($individusSansMail);
            $source = $temp->getSource();
            $errorMessage = sprintf(
                "<strong>NB:</strong> Les directeurs de thèses suivants n'ont pas pu être notifiés " .
                "car leur adresse électronique n'est pas connue dans %s : <br> %s",
                $source,
                implode(',', $individusSansMail)
            );
        }

        $isValidation = $this->action === self::ACTION_VALIDER;

        $this
            ->setSubject("Page de couverture de votre thèse")
            ->setTo($to)
            ->setCc($cc)
            ->setTemplateVariables([
                'these'   => $this->these,
                'isValidation' => $isValidation,
                'message' => $errorMessage,
            ]);

        $this->setInfoMessages($infoMessage);
        if ($errorMessage) {
            $this->setWarningMessages($errorMessage);
        }

        return $this;
    }
}