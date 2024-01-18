<?php

namespace Admission\Entity\Db;

use Application\Constants;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;

class AdmissionAvis implements HistoriqueAwareInterface, ResourceInterface, AdmissionOperationInterface
{
    const RESOURCE_ID = 'AdmissionAvis';

    use HistoriqueAwareTrait;

    // Codes des types d'avis, issus de "UNICAEN_AVIS_TYPE.CODE" :
    const AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_THESE = 'AVIS_ADMISSION_DIR_THESE';
    const AVIS_TYPE__CODE__AVIS_ADMISSION_CODIR_THESE = 'AVIS_ADMISSION_CODIR_THESE';
    const AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_UR = 'AVIS_ADMISSION_DIR_UR';
    const AVIS_TYPE__CODE__AVIS_ADMISSION_DIR_ED = 'AVIS_ADMISSION_DIR_ED';

    // Codes des valeurs d'avis, issus de "UNICAEN_AVIS_VALEUR.CODE" :
    const AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET = 'AVIS_ADMISSION_VALEUR_INCOMPLET';
//    const AVIS_VALEUR__CODE__AVIS_ADMISSION_DIR_ED_VALEUR_INCOMPLET = 'AVIS_ADMISSION_DIR_ED_VALEUR_INCOMPLET';
    const AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_POSITIF = 'AVIS_ADMISSION_VALEUR_POSITIF';
    const AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF = 'AVIS_ADMISSION_VALEUR_NEGATIF';

    private ?int $id = null;

    private Admission $admission;

    private Avis $avis;

    public function __construct(?Admission $admission = null, ?AvisType $avisType = null)
    {
        if ($admission !== null) {
            $this->setAdmission($admission);
        }
        if ($avisType !== null) {
            $this->setAvis(
                (new Avis())->setAvisType($avisType)
            );
        }
    }

    public function __toString(): string
    {
        $str = $this->getAvis()->getAvisType();
        $str .= $this->getAvis()->getAvisValeur() ? " : ".$this->getAvis()->getAvisValeur()->getValeur() : "";
        if ($date = $this->getHistoModification() ?: $this->getHistoCreation()) {
            $str .= sprintf(" (le %s par %s)",
                $date->format(Constants::DATETIME_FORMAT),
                $this->getHistoModificateur() ?: $this->getHistoCreateur());
        }

        return $str;
    }

    public function matches(AdmissionOperationInterface $otherOperation): bool
    {
        return
            $otherOperation instanceof self && (
                // même id non null ou même type d'avis
                $this->getId() && $otherOperation->getId() && $this->getId() === $otherOperation->getId() ||
                $this->getAvis()->getAvisType() === $otherOperation->getAvis()->getAvisType()
            );
    }

    public function getTypeToString(): string
    {
        return (string) $this->getAvis()->getAvisType();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAdmission(Admission $admission): self
    {
        $this->admission = $admission;

        return $this;
    }

    public function getAdmission(): Admission
    {
        return $this->admission;
    }

    public function getAvis(): Avis
    {
        return $this->avis;
    }

    public function setAvis(Avis $avis): self
    {
        $this->avis = $avis;
        return $this;
    }

    public function getValeurBool(): bool
    {
        return $this->getAvis()->getAvisValeur()->getValeurBool();
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
