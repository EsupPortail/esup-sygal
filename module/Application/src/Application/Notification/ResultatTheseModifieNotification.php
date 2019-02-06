<?php

namespace Application\Notification;

use Application\Entity\Db\Interfaces\TheseAwareTrait;
use Notification\Notification;

class ResultatTheseModifieNotification extends Notification
{
    use TheseAwareTrait;

    protected $templatePath = 'application/import/mail/notif-evenement-import';

    /**
     * @return static
     */
    public function prepare()
    {
        // NB: les destinataires sont ajoutés en amont.

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
}