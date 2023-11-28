<?php

namespace Admission\Notification;

use Admission\Entity\Db\AdmissionValidation;
use Notification\Notification;
use RuntimeException;

class AdmissionValidationAjouteeNotification extends Notification
{
    protected ?string $templatePath = 'admission/notification/validation';
    private AdmissionValidation $admissionValidation;

    /**
     * @param AdmissionValidation $admissionValidation
     */
    public function setAdmissionValidation(AdmissionValidation $admissionValidation): void
    {
        $this->admissionValidation = $admissionValidation;
    }

    /**
     * @return self
     */
    public function prepare(): self
    {
        $admission = $this->admissionValidation->getAdmission();
        $individu = $admission->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$email) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour le doctorant {$individu}");
        }

        $this->setTo([$email => $admission->getIndividu()->getNomComplet()]);
//        $this->setCc($admission->getDirecteursTheseEmails());
        $this->setSubject(sprintf(
            "%s de %s validé",
            $this->admissionValidation->getAdmission(),
            $this->admissionValidation->getHistoModificateur() ?: $this->admissionValidation->getHistoCreateur()
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
            'admissionValidation' => $this->admissionValidation,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}