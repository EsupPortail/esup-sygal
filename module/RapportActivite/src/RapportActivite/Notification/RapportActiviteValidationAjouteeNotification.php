<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\Db\RapportActiviteValidation;

class RapportActiviteValidationAjouteeNotification extends Notification
{
    protected ?string $templatePath = 'rapport-activite/notification/validation';
    private RapportActiviteValidation $rapportActiviteValidation;

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteValidation $rapportActiviteValidation
     */
    public function setRapportActiviteValidation(RapportActiviteValidation $rapportActiviteValidation): void
    {
        $this->rapportActiviteValidation = $rapportActiviteValidation;
    }

    /**
     * @return self
     */
    public function prepare(): self
    {
        $these = $this->rapportActiviteValidation->getRapportActivite()->getThese();
        $individu = $these->getDoctorant()->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        $this->setTo([$email => $these->getDoctorant()->getIndividu()->getNomComplet()]);
        $this->setCc($these->getDirecteursTheseEmails());
        $this->setSubject(sprintf(
            "%s de %s validé",
            $this->rapportActiviteValidation->getRapportActivite(),
            $this->rapportActiviteValidation->getHistoModificateur() ?: $this->rapportActiviteValidation->getHistoCreateur()
        ));

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'rapportActiviteValidation' => $this->rapportActiviteValidation,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}