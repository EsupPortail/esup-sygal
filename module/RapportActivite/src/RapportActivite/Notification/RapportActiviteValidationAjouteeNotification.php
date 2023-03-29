<?php

namespace RapportActivite\Notification;

use Notification\Notification;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RuntimeException;

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
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour le doctorant {$these->getDoctorant()}");
        }

        $this->setTo([$email => $these->getDoctorant()->getIndividu()->getNomComplet()]);
        $this->setCc($these->getDirecteursTheseEmails());
        $this->setSubject(sprintf(
            "%s de %s validé",
            $this->rapportActiviteValidation->getRapportActivite(),
            $this->rapportActiviteValidation->getHistoModificateur() ?: $this->rapportActiviteValidation->getHistoCreateur()
        ));

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé à %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, [])),
        );
        if ($this->cc) {
            $successMessage .= sprintf(
                " et en copie à %s",
                implode(', ', array_reduce(array_keys($this->cc), function(array $accu, string $key) {
                    $accu[] = sprintf('%s (%s)', $this->cc[$key], $key);
                    return $accu;
                }, [])),
            );
        }

        $this->setTemplateVariables([
            'rapportActiviteValidation' => $this->rapportActiviteValidation,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}