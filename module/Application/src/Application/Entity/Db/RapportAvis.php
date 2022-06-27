<?php

namespace Application\Entity\Db;

use Application\Constants;
use DateTime;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * RapportAvis
 */
class RapportAvis implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;

    /**
     * Attention : constantes correspondant au type Postgres énuméré "avis_enum".
     */
    const AVIS_FAVORABLE = 'Favorable';
    const AVIS_DEFAVORABLE = 'Défavorable';

    const AVIS = [
        self::AVIS_FAVORABLE => self::AVIS_FAVORABLE,
        self::AVIS_DEFAVORABLE => self::AVIS_DEFAVORABLE,
    ];

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @var string
     */
    private $avis;

    /**
     * @var string
     */
    private $commentaires;

//    /**
//     * RapportAvis constructor.
//     *
//     * @param string|null $avis
//     * @param Rapport|null $rapport
//     */
//    public function __construct(string $avis = null, Rapport $rapport = null)
//    {
//        if ($avis) {
//            $this->setAvis($avis);
//        }
//        if ($rapport) {
//            $this->setRapport($rapport);
//        }
//    }

    /**
     * Représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("Avis '%s' du %s par %s",
            $this->getAvis(),
            $this->getHistoCreation()->format(Constants::DATETIME_FORMAT),
            $this->getHistoCreateur());
    }

//    /**
//     * Get histoModification
//     *
//     * @return DateTime
//     */
//    public function getHistoModification(): DateTime
//    {
//        return $this->histoModification ?: $this->getHistoCreation();
//    }

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
     * @param Rapport|null $rapport
     * @return self
     */
    public function setRapport(Rapport $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get these
     *
     * @return Rapport
     */
    public function getRapport(): ?Rapport
    {
        return $this->rapport;
    }

    /**
     * @return string
     */
    public function getAvis(): ?string
    {
        return $this->avis;
    }

    /**
     * @return bool|null
     */
    public function getAvisAsBoolean(): ?bool
    {
        if ($this->avis === null) {
            return null;
        }

        return $this->avis === self::AVIS_FAVORABLE;
    }

    /**
     * @param string $avis
     * @return self
     */
    public function setAvis(string $avis): self
    {
        $this->avis = $avis;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    /**
     * @param string $commentaires
     * @return self
     */
    public function setCommentaires(string $commentaires): self
    {
        $this->commentaires = $commentaires;
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
        return 'RapportAvis';
    }
}
