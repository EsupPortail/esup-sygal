<?php

namespace Application\Filter;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Interfaces\DoctorantInterface;
use Zend\Filter\AbstractFilter;
use UnicaenApp\Entity\Ldap\People;
use Application\Entity\Db\Utilisateur;

/**
 * Formatte le nom complet d'un individu (nom usuel, patronymique, etc.)
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NomCompletFormatter extends AbstractFilter
{
    protected $nomEnMajuscule = true;
    protected $avecCivilite   = false;
    protected $avecNomPatro   = false;
    protected $prenomDabord   = false;
    protected $tousLesPrenoms = false;

    /**
     * Constructeur.
     *
     * @param bool $nomEnMajuscule
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenomDabord
     * @param bool $tousLesPrenoms
     */
    public function __construct($nomEnMajuscule = true, $avecCivilite = false, $avecNomPatro = false, $prenomDabord = false, $tousLesPrenoms = false)
    {
        $this->nomEnMajuscule = $nomEnMajuscule;
        $this->avecCivilite   = $avecCivilite;
        $this->avecNomPatro   = $avecNomPatro;
        $this->prenomDabord   = $prenomDabord;
        $this->tousLesPrenoms = $tousLesPrenoms;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws \RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        // normalisation
        if ($value instanceof Individu) {
            /* @var $value Individu */
            $nomUsuel = $value->getNomUsuel();
            $nomPatro = "";
            $prenom   = $value->getPrenom();
            $civilite = $value->getCiviliteToString();
        }
        elseif ($value instanceof DoctorantInterface) {
            /* @var $value DoctorantInterface */
            $nomUsuel = $value->getNomUsuel();
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getPrenom($this->tousLesPrenoms);
            $civilite = $value->getCiviliteToString();
        }
        elseif ($value instanceof People) {
            /* @var $value People */
            $sns = (array) $value->getSn(true);
            $nomUsuel = current($sns);
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getGivenName();
            $civilite = $value->getSupannCivilite();
        }
        elseif ($value instanceof Utilisateur) {
            /* @var $value Utilisateur */
            $nomUsuel = $value->getDisplayName();
            $nomPatro = $value->getDisplayName();
            $prenom   = '';
            $civilite = '';
        }
        elseif ($value instanceof \stdClass) {
            /* @var $value \stdClass */
            foreach (['nomUsuel', 'nomPatronymique', 'prenom', 'civilite'] as $prop) {
                if (!isset($value->$prop)) {
                    throw new \LogicException("L'objet à formatter doit posséder l'attribut public '$prop'.");
                }
            }
            $nomUsuel = $value->nomUsuel;
            $nomPatro = $value->nomPatronymique;
            $prenom   = $value->prenom;
            $civilite = $value->civilite;
        }
        elseif (is_array($value)) {
            /* @var $value array */
            foreach (['NOM_USUEL', 'NOM_PATRONYMIQUE', 'PRENOM', 'CIVILITE'] as $prop) {
                if (!array_key_exists($prop, $value)) {
                    throw new \LogicException("Le tableau à formatter doit posséder la clé '$prop'.");
                }
            }
            $nomUsuel = $value['NOM_USUEL'];
            $nomPatro = $value['NOM_PATRONYMIQUE'];
            $prenom   = $value['PRENOM'];
            $civilite = $value['CIVILITE'];
        }
        else {
            throw new \LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        $nomUsuel = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomUsuel) : $nomUsuel);
        $nomPatro = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomPatro) : $nomPatro);
        $prenom   = ucfirst(mb_strtolower($prenom));
        $civilite = $this->avecCivilite ? $civilite : null;

        $parts = [
            $civilite,
            $this->prenomDabord ? "$prenom $nomUsuel" : "$nomUsuel $prenom",
        ];

        $result = implode(' ', array_filter($parts));

        if ($this->avecNomPatro && $nomPatro !== $nomUsuel) {
            $result .= ", née $nomPatro";
        }

	    return $result;
    }
}