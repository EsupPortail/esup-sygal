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
    protected bool $avecNomUsuelEtPatro   = false;
    protected bool $prenomDabord   = false;
    protected bool $tousLesPrenoms = false;
    protected bool $avecNomPatroSeul = false;

    /**
     * Constructeur.
     *
     * @param bool $nomEnMajuscule
     * @param bool $avecCivilite
     * @param bool $avecNomUsuelEtPatro
     * @param bool $prenomDabord
     * @param bool $tousLesPrenoms
     * @param bool $avecNomPatroSeul
     */
    public function __construct(
        bool $nomEnMajuscule = true,
        bool $avecCivilite = false,
        bool $avecNomUsuelEtPatro = false,
        bool $prenomDabord = false,
        bool $tousLesPrenoms = false,
        bool $avecNomPatroSeul = false)
    {
        $this->nomEnMajuscule       = $nomEnMajuscule;
        $this->avecCivilite         = $avecCivilite;
        $this->avecNomUsuelEtPatro         = $avecNomUsuelEtPatro;
        $this->prenomDabord = $prenomDabord;
        $this->tousLesPrenoms = $tousLesPrenoms;
        $this->avecNomPatroSeul = $avecNomPatroSeul;
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
            $civilite = $value->getCivilite();
        }
        elseif ($value instanceof Doctorant) {
            $nomUsuel = $value->getIndividu()->getNomUsuel();
            $nomPatro = $value->getIndividu()->getNomPatronymique();
            $prenom   = $value->getIndividu()->getPrenom($this->tousLesPrenoms);
            $civilite = $value->getIndividu()->getCivilite();
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
            $nomUsuel = $value['nom_usuel'] ?? $value['nomUsuel'] ?? '?';
            $nomPatro = $value['nom_patronymique'] ?? $value['nomPatronymique'] ?? '?';
            $prenom   = $value['prenom1'];
            $civilite = $value['civilite'];
        }
        else {
            throw new \LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        $nomUsuel = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomUsuel) : $nomUsuel);
        $nomPatro = ucfirst($this->nomEnMajuscule ? mb_strtoupper($nomPatro) : $nomPatro);
        $civilite = $this->avecCivilite ? $civilite : null;

        if ($this->avecNomPatroSeul) {
            $nomComplet = $this->prenomDabord ? "$prenom $nomPatro" : "$nomPatro $prenom";
        }else{
            $nomComplet = $this->prenomDabord ? "$prenom $nomUsuel" : "$nomUsuel $prenom";
        }

        $parts = [
            $civilite,
            $nomComplet
        ];

        $result = implode(' ', array_filter($parts));

        if ($this->avecNomUsuelEtPatro && !$this->avecNomPatroSeul &&$nomPatro !== $nomUsuel) {
            $result .= ", né·e $nomPatro";
        }

	    return $result;
    }
}