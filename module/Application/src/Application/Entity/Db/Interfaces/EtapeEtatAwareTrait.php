<?php

namespace Application\Entity\Db\Interfaces;

use Depot\Entity\Db\WfEtape;

/**
 * Trait spécifiant les propriétés et accesseurs utiles pour obtenir l'état d'une étape de workflow.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see    \Depot\Entity\Db\WfEtape
 */
trait EtapeEtatAwareTrait
{

    /**
     * @var WfEtape
     */
    private $precedente;

    /**
     * @var WfEtape
     */
    private $suivante;

    /**
     * @return \Depot\Entity\Db\WfEtape
     */
    public function getPrecedente()
    {
        return $this->precedente;
    }

    /**
     * @param WfEtape $precedente
     * @param bool    $reciprocite
     */
    public function setPrecedente(WfEtape $precedente = null, $reciprocite = true)
    {
        $this->precedente = $precedente;

        if ($reciprocite && $precedente !== null) {
            $precedente->setSuivante($this, false);
        }
    }

    /**
     * @return WfEtape
     */
    public function getSuivante()
    {
        return $this->suivante;
    }

    /**
     * @param WfEtape $suivante
     * @param bool    $reciprocite
     */
    public function setSuivante(WfEtape $suivante = null, $reciprocite = true)
    {
        $this->suivante = $suivante;

        if ($reciprocite && $suivante !== null) {
            $suivante->setPrecedente($this, false);
        }
    }


    /**
     * @var boolean
     */
    private $franchie = false;

    /**
     * @return boolean
     */
    public function getFranchie()
    {
        return $this->franchie;
    }

    /**
     * @param boolean $franchie
     */
    public function setFranchie($franchie = true)
    {
        $this->franchie = (bool) $franchie;
    }

    /**
     * @var boolean
     */
    private $atteignable = false;

    /**
     * @return boolean
     */
    public function getAtteignable()
    {
        return $this->atteignable;
    }

    /**
     * @param boolean $atteignable
     */
    public function setAtteignable($atteignable = true)
    {
        $this->atteignable = (bool) $atteignable;
    }
}