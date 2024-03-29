<?php

namespace These\Notification;

use Notification\Notification;
use These\Entity\Db\Interfaces\TheseAwareTrait;

class ChangementsResultatsThesesNotification extends Notification
{
    use TheseAwareTrait;

    protected ?string $templatePath = 'these/these/mail/notif-evenement-import';

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
            'message' => "Vous êtes informé-e que des modifications de résultats de thèses ont été détectées.",
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