<?php

namespace Application\Filter;

use Application\Entity\Db\Utilisateur;
use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use Laminas\Filter\AbstractFilter;
use UnicaenApp\Entity\Ldap\People;

/**
 * Formatte le nom complet d'un individu (nom usuel, patronymique, etc.)
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NomCompletFormatter extends AbstractFilter
{
    protected bool $nomEnMajuscule = true;
    protected bool $avecCivilite   = false;
    protected bool $avecNomPatro   = false;
    protected bool $prenomDabord   = false;
    protected bool $tousLesPrenoms = false;

    /**
     * Constructeur.
     *
     * @param bool $nomEnMajuscule
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenomDabord
     * @param bool $tousLesPrenoms
     */
    public function __construct(
        bool $nomEnMajuscule = true,
        bool $avecCivilite = false,
        bool $avecNomPatro = false,
        bool $prenomDabord = false,
        bool $tousLesPrenoms = false)
    {
        $this->nomEnMajuscule       = $nomEnMajuscule;
        $this->avecCivilite         = $avecCivilite;
        $this->avecNomPatro         = $avecNomPatro;
        $this->prenomDabord         = $prenomDabord;
        $this->tousLesPrenoms       = $tousLesPrenoms;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws \RuntimeException If filtering $value is impossible
     */
    public function filter($value): string
    {
        // normalisation
        if ($value instanceof Individu) {
            /* @var $value Individu */
            $nomUsuel = $value->getNomUsuel();
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getPrenom($this->tousLesPrenoms);
            $civilite = $value->getCiviliteToString();
        }
        elseif ($value instanceof Doctorant) {
            $nomUsuel = $value->getIndividu()->getNomUsuel();
            $nomPatro = $value->getIndividu()->getNomPatronymique();
            $prenom   = $value->getIndividu()->getPrenom($this->tousLesPrenoms);
            $civilite = $value->getIndividu()->getCiviliteToString();
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
            $nomUsuel = $value->getNom() ?: $value->getDisplayName();
            $nomPatro = $value->getPrenom() ?: $value->getDisplayName();
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

        $parts = [
            $civilite,
            $this->prenomDabord ? "$prenom $nomUsuel" : "$nomUsuel $prenom",
        ];

        $result = implode(' ', array_filter($parts));

        if ($this->avecNomPatro && $nomPatro !== $nomUsuel) {
            $result .= ", né·e $nomPatro";
        }

	    return $result;
    }
}