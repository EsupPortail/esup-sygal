<?php

namespace Application\Entity\Db\Interfaces;

/**
 * Interface spécifiant les accesseurs utiles pour obtenir des informations
 * affichables concernant un thésard, qu'il vienne de l'application elle-même ou de la source
 * de données externe (ex: Apogée).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see    Doctorant
 */
interface DoctorantInterface
{
    /**
     * Retourne la représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString();

    /**
     * Retourne le nom usuel.
     * 
     * @return string
     */
    public function getNomUsuel();

    /**
     * Retourne le nom patronymique.
     * 
     * @return string
     */
    public function getNomPatronymique();

    /**
     * Retourne le ou les prenoms.
     *
     * @param bool $tous
     * @return string
     */
    public function getPrenom($tous = false);

    /**
     * Get estUneFemme
     *
     * @return bool 
     */
    public function estUneFemme();
    
    /**
     * Get civilite
     *
     * @return string 
     */
    public function getCiviliteToString();
    
    /**
     * Get dateNaissance
     *
     * @return string
     */
    public function getDateNaissanceToString();

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail();

    /**
     * Get sourceCode
     *
     * @return string 
     */
    public function getSourceCode();

}