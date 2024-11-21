<?php

namespace RapportActivite\Entity\Db;

use Application\Constants;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use Webmozart\Assert\Assert;

class RapportActiviteAvis implements HistoriqueAwareInterface, ResourceInterface, RapportActiviteOperationInterface
{
    const RESOURCE_ID = 'RapportActiviteAvis';

    use HistoriqueAwareTrait;

    // Codes des types d'avis, issus de "UNICAEN_AVIS_TYPE.CODE" :
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST = 'AVIS_RAPPORT_ACTIVITE_GEST'; // avis gestionnaire ED (conservé pour compatibilité)
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_THESE = 'AVIS_RAPPORT_ACTIVITE_DIR_THESE';
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_CODIR_THESE = 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE';
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_UR = 'AVIS_RAPPORT_ACTIVITE_DIR_UR';
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED = 'AVIS_RAPPORT_ACTIVITE_DIR_ED';

    // Codes des valeurs d'avis, issus de "UNICAEN_AVIS_VALEUR.CODE" :
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET = 'AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET';
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET = 'AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET';
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF = 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF';
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF = 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF';

    // Codes des compléments, issus de "UNICAEN_AVIS_TYPE_VALEUR_COMPLEM.CODE" :
    const AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_THESE =
        'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_THESE';
    const AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_UR =
        'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_UR';

    private ?int $id = null;

    private RapportActivite $rapport;

    private Avis $avis;

    public function __construct(?RapportActivite $rapport = null, ?AvisType $avisType = null)
    {
        if ($rapport !== null) {
            $this->setRapportActivite($rapport);
        }
        if ($avisType !== null) {
            $this->setAvis(
                (new Avis())->setAvisType($avisType)
            );
        }
    }

    public function __toString(): string
    {
        $str = (string) $this->getAvis();

        if ($date = $this->getHistoModification() ?: $this->getHistoCreation()) {
            $str .= sprintf(" (le %s par %s)",
                $date->format(Constants::DATETIME_FORMAT),
                $this->getHistoModificateur() ?: $this->getHistoCreateur());
        }

        return $str;
    }

    public function matches(RapportActiviteOperationInterface $otherOperation): bool
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

    public function setRapportActivite(RapportActivite $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    public function getRapportActivite(): RapportActivite
    {
        return $this->rapport;
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
