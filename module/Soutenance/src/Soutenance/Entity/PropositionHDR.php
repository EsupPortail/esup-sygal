<?php

namespace Soutenance\Entity;

use HDR\Entity\Db\HDR;

class PropositionHDR extends Proposition{
    private ?HDR $hdr = null;

    public function __construct(?HDR $hdr = null)
    {
        parent::__construct();
        $this->setHDR($hdr);
    }

    /**
     * @return HDR|null
     */
    public function getHDR(): ?HDR
    {
        return $this->hdr;
    }

    /**
     * @param HDR|null $hdr
     */
    public function setHDR(?HDR $hdr): void
    {
        $this->hdr = $hdr;
    }

    /**
     * Si la proposition de soutenance est à l'état "Validée", on retourne true,
     * Si la proposition de soutenance est à l'état "Rejetée", on retourne false,
     * sinon retourne null.
     *
     * @return bool|null
     */
    public function getSoutenanceAutorisee(): bool|null
    {
        if ($this->getEtat()->getCode() === Etat::VALIDEE) :
            return true;
        elseif ($this->getEtat()->getCode() === Etat::REJETEE) :
            return false;
        else:
            return null;
        endif;
    }
}
