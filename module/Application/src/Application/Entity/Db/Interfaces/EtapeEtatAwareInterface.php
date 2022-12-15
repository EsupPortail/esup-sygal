<?php

namespace Application\Entity\Db\Interfaces;

use Depot\Entity\Db\WfEtape;

/**
 * Trait spécifiant les propriétés et accesseurs utiles pour obtenir l'état d'une étape de workflow.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see    WfEtape
 */
interface EtapeEtatAwareInterface
{
    /**
     * @return WfEtape
     */
    public function getPrecedente();

    /**
     * @param WfEtape $precedente
     * @param bool    $reciprocite
     */
    public function setPrecedente(WfEtape $precedente = null, $reciprocite = true);

    /**
     * @return WfEtape
     */
    public function getSuivante();

    /**
     * @param WfEtape $suivante
     * @param bool    $reciprocite
     */
    public function setSuivante(WfEtape $suivante = null, $reciprocite = true);


    /**
     * @return bool
     */
    public function getFranchie();

    /**
     * @param boolean $franchie
     * @return WfEtape
     */
    public function setFranchie($franchie = true);

    /**
     * @return boolean
     */
    public function getAtteignable();

    /**
     * @param boolean $atteignable
     * @return WfEtape
     */
    public function setAtteignable($atteignable = true);

}