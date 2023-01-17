<?php

namespace Depot\Notification;

use Individu\Entity\Db\Individu;
use Notification\Exception\RuntimeException;
use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;
use UnicaenApp\Exception\LogicException;

class ValidationPageDeCouvertureNotification extends Notification
{
    use TheseAwareTrait;

    const ACTION_VALIDER = 'valider';
    const ACTION_DEVALIDER = 'devalider';

    protected ?string $templatePath = 'application/notification/mail/notif-validation-page-couverture';
    private string $action;
    private array $emailsBu = [];

    /**
     * @param string[] $emailsBu
     * @return self
     */
    public function setEmailsBu(array $emailsBu): self
    {
        $this->emailsBu = $emailsBu;

        return $this;
    }

    /**
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self
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
     * @return self
     */
    public function prepare(): self
    {
        /** @var Individu[] $individusSansMail */
        $individusSansMail = [];
        $emailsDirecteurs = $this->these->getDirecteursTheseEmails($individusSansMail);

        $individu = $this->these->getDoctorant()->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Aucune adresse mail trouvée pour le doctorant {$this->these->getDoctorant()}");
        }

        $cc = array_merge(
            $emailsDirecteurs,
            $this->emailsBu,
        );

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé au doctorant (%s), avec copie à la direction de thèse (%s) " .
            "et à %s",
            $email,
            implode(',', $emailsDirecteurs),
            implode(',', $this->emailsBu),
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
            ->setTo($email)
            ->setCc($cc)
            ->setTemplateVariables([
                'these'   => $this->these,
                'isValidation' => $isValidation,
                'message' => $errorMessage,
            ]);

        $this->addSuccessMessage($successMessage);
        if ($errorMessage) {
            $this->addErrorMessage($errorMessage);
        }

        return $this;
    }
}