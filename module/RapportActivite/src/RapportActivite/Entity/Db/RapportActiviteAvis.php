<?php

namespace RapportActivite\Entity\Db;

use Application\Constants;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenAvis\Entity\Db\Avis;

class RapportActiviteAvis implements HistoriqueAwareInterface, ResourceInterface
{
    const RESOURCE_ID = 'RapportActiviteAvis';

    use HistoriqueAwareTrait;

    // Codes issus de "UNICAEN_AVIS_TYPE.CODE" :
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST = 'AVIS_RAPPORT_ACTIVITE_GEST';
    const AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR = 'AVIS_RAPPORT_ACTIVITE_DIR';
    const AVIS_TYPE__CODES_ORDERED = [
        0 => self::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST,
        1 => self::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR,
    ];

    // Codes issus de "UNICAEN_AVIS_VALEUR.CODE" :
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET = 'AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET';
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF = 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF';
    const AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF = 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF';

    /**
     * @var integer
     */
    private int $id;

    /**
     * @var RapportActivite
     */
    private RapportActivite $rapport;

    /**
     * @var \UnicaenAvis\Entity\Db\Avis
     */
    private Avis $avis;

    /**
     * Représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s (%s, %s)",
            $this->getAvis(),
            $this->getHistoCreation()->format(Constants::DATETIME_FORMAT),
            $this->getHistoCreateur());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set intervenant
     *
     * @param RapportActivite|null $rapport
     * @return self
     */
    public function setRapportActivite(RapportActivite $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get these
     *
     * @return RapportActivite
     */
    public function getRapportActivite(): ?RapportActivite
    {
        return $this->rapport;
    }

    /**
     * @return Avis
     */
    public function getAvis(): Avis
    {
        return $this->avis;
    }

    /**
     * @param Avis $avis
     * @return self
     */
    public function setAvis(Avis $avis): self
    {
        $this->avis = $avis;
        return $this;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
