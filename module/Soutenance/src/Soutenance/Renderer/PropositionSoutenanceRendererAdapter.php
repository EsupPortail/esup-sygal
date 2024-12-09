<?php

namespace Soutenance\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Soutenance\Entity\Proposition;

class PropositionSoutenanceRendererAdapter extends AbtractRendererAdapter
{
    private Proposition $proposition;

    public function __construct(Proposition $proposition)
    {
        $this->proposition = $proposition;
    }

    /**
     * @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \These\Renderer\TheseRendererAdapter}
     */
    public function toStringDateRetourRapport(): string
    {
        $date = $this->proposition->getRenduRapport();
        if ($date) return $date->format('d/m/Y');
        return "<span style='color:darkorange;'>Aucune date de rendu précisée</span>";
    }

    /**
     * @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter}
     */
    public function toStringDateSoutenance(): string
    {
        $date = $this->proposition->getDate();
        if ($date) return $date->format('d/m/Y à H:i');
        return "<span style='color:darkorange;'>Aucune date de rendu précisée</span>";
    }

    /**
     * @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter}
     */
    public function toStringLieu(): string
    {
        $lieu = $this->proposition->getLieu();
        if ($lieu) return $lieu;
        return "<span style='color:darkorange;'>Aucun lieu précisé</span>";
    }

    /**
     * @noinspection  PhpUnused Utilisé par la macro Soutenance#Adresse
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter}
     */
    public function toStringAdresse(): string
    {
        $lieu = $this->proposition->getAdresseActive();
        if ($lieu) return $lieu->format();
        return "<span style='color:darkorange;'>Aucune adresse précisée</span>";
    }

    /**
     * @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter}
     */
    public function toStringPublicOuHuisClos(): string
    {
        $mode = $this->proposition->isHuitClos();
        if ($mode === false) return " sera publique ";
        if ($mode === true) return " se déroulera en huis clos";
        return "<span style='color:darkorange;'>Aucun mode déclaré</span>";
    }
}