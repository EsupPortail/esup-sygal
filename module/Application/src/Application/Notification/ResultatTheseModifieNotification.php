<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ResultatTheseModifieNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/these/mail/notif-resultat-these-modifie';

    /**
     * @return static
     */
    public function prepare()
    {
        $this->setTo($this->emailBdd);
        $this->setSubject("Résultats de thèses modifiés");

        $this->setTemplateVariables([
            'these' => $this->these,
            'data' => $this->data,
            'message' => "Vous êtes informé-e que des modifications de résultats de thèses ont été détectées lors de la synchro avec Apogée.",
        ]);

        return $this;
    }

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @var string
     */
    private $emailBdd;

    /**
     * @param string $emailBdd
     * @return self
     */
    public function setEmailBdd($emailBdd)
    {
        $this->emailBdd = $emailBdd;

        return $this;
    }
}