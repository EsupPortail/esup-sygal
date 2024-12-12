<?php

namespace Soutenance\Renderer;

use Soutenance\Entity\Avis;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class SoutenanceAvisTemplateVariable extends AbstractTemplateVariable
{
    private Avis $avis;

    public function setAvis(Avis $avis): void
    {
        $this->avis = $avis;
    }
}