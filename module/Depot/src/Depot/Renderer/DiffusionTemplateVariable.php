<?php

namespace Depot\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Depot\Entity\Db\Diffusion;

/**
 * @property \Depot\Entity\Db\Diffusion $target
 */
class DiffusionTemplateVariable extends AbstractTemplateVariable
{
    public function setDiffusion(Diffusion $diffusion): void
    {
        $this->setTarget($diffusion);
    }

    /**
     * @noinspection PhpUnused Méthode utilisée dans un Template (unicaen/renderer)
     */
    public function getAutorisMel(): int
    {
        return $this->target->getAutorisMel();
    }

    /**
     * @noinspection PhpUnused Méthode utilisée dans un Template (unicaen/renderer)
     */
    public function getAutorisEmbargoDuree(): string
    {
        return $this->target->getAutorisEmbargoDuree();
    }

    /**
     * @noinspection PhpUnused Méthode utilisée dans un Template (unicaen/renderer)
     */
    public function getAutorisMotif(): string
    {
        return $this->target->getAutorisMotif();
    }

    /**
     * @noinspection PhpUnused Méthode utilisée dans un Template (unicaen/renderer)
     */
    public function getDroitAuteurOk(): bool
    {
        return $this->target->getDroitAuteurOk();
    }

    /**
     * @noinspection PhpUnused Méthode utilisée dans un Template (unicaen/renderer)
     */
    public function getAutorisPretPapier(): bool
    {
        return $this->target->getAutorisPretPapier();
    }

}