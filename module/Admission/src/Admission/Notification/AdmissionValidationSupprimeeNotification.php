<?php

namespace Admission\Notification;

use Admission\Entity\Db\AdmissionValidation;
use Individu\Entity\Db\Individu;
use Notification\Notification;

class AdmissionValidationSupprimeeNotification extends Notification
{
    protected ?string $templatePath = 'admission/admission/notification/validation-supprimee';
    private AdmissionValidation $admissionValidation;

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

        $this->setTo([$email => $admission->getIndividu()->getNomComplet()]);
        if(!empty($admission->getInscription()->first()->getDirecteur())){
            /** @var Individu $directeur */
            $directeur = $admission->getInscription()->first()->getDirecteur();
            $emailDirecteur = $directeur->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
            $this->setCc($emailDirecteur);
        }
        $this->setSubject(sprintf("Votre %s a été dévalidé", $this->admissionValidation));

        $this->setSubject(sprintf(
            "%s de %s dévalidé",
            $this->admissionValidation->getAdmission(),
            $individu->getNomComplet()
        ));

        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'admissionValidation' => $this->admissionValidation,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}