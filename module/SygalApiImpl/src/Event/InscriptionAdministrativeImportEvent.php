<?php

namespace SygalApiImpl\Event;

use Laminas\EventManager\Event;

class InscriptionAdministrativeImportEvent extends Event
{
    const NAME = 'IMPORT_INSCRIPTION_ADMINISTRATIVE';

    protected $name = self::NAME;

    public function setInscriptionAdministrativeSourceCode(string $inscriptionAdministrativeSourceCode): void
    {
        $this->setParam('inscriptionAdministrativeSourceCode', $inscriptionAdministrativeSourceCode);
    }

    public function getInscriptionAdministrativeSourceCode(): string
    {
        return $this->getParam('inscriptionAdministrativeSourceCode');
    }
}