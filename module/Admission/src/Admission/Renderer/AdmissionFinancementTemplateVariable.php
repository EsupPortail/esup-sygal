<?php

namespace Admission\Renderer;

use Admission\Entity\Db\Financement;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionFinancementTemplateVariable extends AbstractTemplateVariable
{
    private Financement $financement;

    public function setFinancement(Financement $financement): void
    {
        $this->financement = $financement;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getContratDoctoralLibelle()
    {
        if ($this->financement->getContratDoctoral() === null) {
            return "<b>Non renseigné</b>";
        } else {
            if ($this->financement->getContratDoctoral()) {
                $financement = $this->financement->getFinancement()?->getLibelleLong();
                $financement .= $this->financement->getFinancementCompl() ? ", " . $this->financement->getFinancementCompl()?->getLibelleLong() : "";
                return $this->financement->getFinancement() ?
                    "Oui - " . $financement :
                    'Oui - Aucun employeur choisi';
            } else {
                return "Aucun contrat doctoral prévu";
            }
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getTempsTravailInformations()
    {
        if ($this->financement->getTempsTravail() === 1) {
            return "temps complet";
        } else if ($this->financement->getTempsTravail() === 2) {
            return "temps partiel";
        } else {
            return "<b>Non renseigné</b>";
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEstSalarieInfos()
    {
        if ($this->financement->getEstSalarie() === 1) {
            return "Oui";
        } else if ($this->financement->getEstSalarie() === 0) {
            return "Non";
        } else {
            return "<b>Non renseigné</b>";
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getStatutProfessionnelInfos()
    {
        return $this->financement->getEstSalarie() == 1 ? "<b>Statut professionnel : " . $this->financement->getStatutProfessionnel() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDetailContratDoctoral(): string
    {
        return $this->financement->getDetailContratDoctoral();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEtablissementPartenaire(): ?string
    {
        return $this->financement->getEtablissementPartenaire();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getStatutProfessionnel(): ?string
    {
        return $this->financement->getStatutProfessionnel();
    }
}