<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * RdvBu
 */
class RdvBu implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const SEPARATEUR_MOTS_CLES_RAMEAU = '*';
    const SEPARATEUR_MOTS_CLES_RAMEAU_LIB = 'astérisque';

    /**
     * @var boolean
     */
    private $conventionMelSignee = false;

    /**
     * @var string
     */
    private $coordDoctorant;

    /**
     * @var string
     */
    private $dispoDoctorant;

    /**
     * @var boolean
     */
    private $versionArchivableFournie = false;

    /**
     * @var boolean
     */
    private $exemplPapierFourni = false;

    /**
     * @var string
     */
    private $motsClesRameau;

    /**
     * @var string
     */
    private $divers;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var These
     */
    private $these;

    /**
     * RdvBu constructor.
     *
     * @param These $these
     */
    public function __construct(These $these = null)
    {
        $this->setThese($these);
    }

    /**
     * Détermine les attributs de l'objet spécifié qui sont différents de cet objet.
     *
     * @param RdvBu $rdvBu
     * @return array getter => libellé
     */
    public function diffWith(RdvBu $rdvBu)
    {
        $getters = [
            'getCoordDoctorant' => "Coordonnées du doctorant",
            'getDispoDoctorant' => "Disponibilités du doctorant",
        ];

        $diff = [];
        foreach ($getters as $getter => $lib) {
            if ($rdvBu->{$getter}() !== $this->{$getter}()) {
                $diff[$getter] = $lib;
            }
        }

        return $diff;
    }

    /**
     * Détermine si les infos qui doivent être saisies pour le RDV BU l'ont été.
     *
     * @return bool
     */
    public function isInfosBuSaisies()
    {
        return
            $this->getExemplPapierFourni() && $this->getConventionMelSignee() && $this->getMotsClesRameau() &&
            $this->isVersionArchivableFournie();
    }

    /**
     * Set conventionMelSignee
     *
     * @param boolean $conventionMelSignee
     *
     * @return RdvBu
     */
    public function setConventionMelSignee($conventionMelSignee)
    {
        $this->conventionMelSignee = $conventionMelSignee;

        return $this;
    }

    /**
     * Get conventionMelSignee
     *
     * @return boolean
     */
    public function getConventionMelSignee()
    {
        return $this->conventionMelSignee;
    }

    /**
     * Set coordDoctorant
     *
     * @param string $coordDoctorant
     *
     * @return RdvBu
     */
    public function setCoordDoctorant($coordDoctorant)
    {
        $this->coordDoctorant = $coordDoctorant;

        return $this;
    }

    /**
     * Get coordDoctorant
     *
     * @return string
     */
    public function getCoordDoctorant()
    {
        return $this->coordDoctorant;
    }

    /**
     * Set dispoDoctorant
     *
     * @param string $dispoDoctorant
     *
     * @return RdvBu
     */
    public function setDispoDoctorant($dispoDoctorant)
    {
        $this->dispoDoctorant = $dispoDoctorant;

        return $this;
    }

    /**
     * Get dispoDoctorant
     *
     * @return string
     */
    public function getDispoDoctorant()
    {
        return $this->dispoDoctorant;
    }

    /**
     * @return boolean
     */
    public function isVersionArchivableFournie()
    {
        return $this->versionArchivableFournie;
    }

    /**
     * @param boolean $versionArchivableFournie
     * @return RdvBu
     */
    public function setVersionArchivableFournie($versionArchivableFournie)
    {
        $this->versionArchivableFournie = $versionArchivableFournie;

        return $this;
    }

    /**
     * Set exemplPapierFourni
     *
     * @param boolean $exemplPapierFourni
     *
     * @return RdvBu
     */
    public function setExemplPapierFourni($exemplPapierFourni)
    {
        $this->exemplPapierFourni = $exemplPapierFourni;

        return $this;
    }

    /**
     * Get exemplPapierFourni
     *
     * @return boolean
     */
    public function getExemplPapierFourni()
    {
        return $this->exemplPapierFourni;
    }

    /**
     * Set motsClesRameau
     *
     * @param string $motsClesRameau
     *
     * @return RdvBu
     */
    public function setMotsClesRameau($motsClesRameau)
    {
        $this->motsClesRameau = $motsClesRameau;

        return $this;
    }

    /**
     * Get motsClesRameau
     *
     * @return string
     */
    public function getMotsClesRameau()
    {
        return $this->motsClesRameau;
    }

    /**
     * @return string
     */
    public function getDivers()
    {
        return $this->divers;
    }

    /**
     * @param string $divers
     * @return RdvBu
     */
    public function setDivers($divers)
    {
        $this->divers = $divers;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set these
     *
     * @param These $these
     *
     * @return RdvBu
     */
    public function setThese(These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these
     *
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }
}

