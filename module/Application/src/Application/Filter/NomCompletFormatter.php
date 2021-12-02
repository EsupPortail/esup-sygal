<?php

namespace Application\Filter;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Interfaces\DoctorantInterface;
use Laminas\Filter\AbstractFilter;
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
    protected $court = false;
    protected $patroPlutotQueUsuel = false;

    /**
     * Constructeur.
     *
     * @param bool $nomEnMajuscule
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenomDabord
     * @param bool $tousLesPrenoms
     */
    public function __construct($nomEnMajuscule = true, $avecCivilite = false, $avecNomPatro = false, $prenomDabord = false, $tousLesPrenoms = false, $court = false, $patroPlutotQueUsuel=false)
    {
        $this->nomEnMajuscule       = $nomEnMajuscule;
        $this->avecCivilite         = $avecCivilite;
        $this->avecNomPatro         = $avecNomPatro;
        $this->prenomDabord         = $prenomDabord;
        $this->tousLesPrenoms       = $tousLesPrenoms;
        $this->court                = $court;
        $this->patroPlutotQueUsuel  = $patroPlutotQueUsuel;
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
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getPrenom($this->tousLesPrenoms);
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
            foreach (['nom_usuel', 'nom_patronymique', 'prenom', 'civilite'] as $prop) {
                if (!array_key_exists($prop, $value)) {
                    throw new \LogicException("Le tableau à formatter doit posséder la clé '$prop'.");
                }
            }
            $nomUsuel = $value['nom_usuel'];
            $nomPatro = $value['nom_patronymique'];
            $prenom   = $value['prenom'];
            $civilite = $value['civilite'];
        }
        else {
            throw new \LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        $nomUsuel = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomUsuel) : $nomUsuel);
        $nomPatro = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomPatro) : $nomPatro);
        $civilite = $this->avecCivilite ? $civilite : null;

        if ($this->patroPlutotQueUsuel && $nomPatro != '') {
            $parts = [
                $civilite,
                $this->prenomDabord ? "$prenom $nomPatro" : "$nomPatro $prenom",
            ];
        } else {
            $parts = [
                $civilite,
                $this->prenomDabord ? "$prenom $nomUsuel" : "$nomUsuel $prenom",
            ];
        }

        $result = implode(' ', array_filter($parts));

        if (! $this->patroPlutotQueUsuel) {
            if ($this->avecNomPatro && $nomPatro !== $nomUsuel) {
                if ($this->court) {
                    $result = "";
                    if ($this->avecCivilite) $result .= "$civilite";
                    if ($this->prenomDabord) $result .= " $prenom";
                    $result .= " $nomPatro-$nomUsuel";
                    if (!$this->prenomDabord) $result .= " $prenom";
                } else {
                    $result .= ", née $nomPatro";
                }
            }
        }

	    return $result;
    }
}