<?php

namespace Admission\Notification;

use Notification\Notification;
use Admission\Entity\Db\Admission;

class AdmissionSupprimeNotification extends Notification
{
    protected ?string $templatePath = 'admission/notification/rapport-supprime';
    private Admission $admission;

    /**
     * @param Admission $admission
     */
    public function setAdmission(Admission $admission): void
    {
        $this->admission = $admission;
    }

    /**
     * @return self
     */
    public function prepare(): self
    {
        $successMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux personnes suivantes : %s",
            implode(', ', array_reduce(array_keys($this->to), function(array $accu, string $key) {
                $accu[] = sprintf('%s (%s)', $this->to[$key], $key);
                return $accu;
            }, []))
        );

        $this->setTemplateVariables([
            'admission' => $this->admission,
        ]);

        $this->addSuccessMessage($successMessage);

        return $this;
    }
}