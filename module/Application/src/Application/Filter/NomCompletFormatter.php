<?php

namespace Application\Filter;

use Application\Entity\Db\Utilisateur;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Laminas\Filter\AbstractFilter;
use LogicException;
use stdClass;
use UnicaenApp\Entity\Ldap\People;

/**
 * Formatte le nom complet d'un individu ou assimilé.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NomCompletFormatter extends AbstractFilter
{
    private mixed $value = null;

    private bool $avecCivilite = false;
    private bool $avecAutresPrenoms = false;
    private bool $avecNomUsage = false;
    private string $avecNomUsageFormat = ", nom d'usage : %s";

    /**
     * Spécifie l'individu ou assimilé dont on veut générer le nom complet formatté.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retourne le nom complet formatté de l'individu ou assimilé spécifié
     * via {@see setValue()}.
     */
    public function f(): string
    {
        $value = $this->value;

        // normalisation
        if ($value instanceof IndividuAwareInterface) {
            $value = $value->getIndividu();
        }
        if ($value instanceof Individu) {
            $nomUsuel = $value->getNomUsuel();
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getPrenom($this->avecAutresPrenoms);
            $civilite = $value->getCivilite();
        }
        elseif ($value instanceof People) {
            $sns = (array) $value->getSn(true);
            $nomUsuel = current($sns);
            $nomPatro = $value->getNomPatronymique();
            $prenom   = $value->getGivenName();
            $civilite = $value->getSupannCivilite();
        }
        elseif ($value instanceof Utilisateur) {
            $nomUsuel = $value->getNom() ?: $value->getDisplayName();
            $nomPatro = $value->getPrenom() ?: $value->getDisplayName();
            $prenom   = '';
            $civilite = '';
        }
        elseif ($value instanceof stdClass) {
            foreach (['nomUsuel', 'nomPatronymique', 'prenom', 'civilite'] as $prop) {
                if (!isset($value->$prop)) {
                    throw new LogicException("L'objet à formatter doit posséder l'attribut public '$prop'.");
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
            throw new LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        $nomUsuel = mb_strtoupper($nomUsuel);
        $nomPatro = mb_strtoupper($nomPatro);
        $civilite = $this->avecCivilite ? $civilite : null;

        if ($this->avecNomUsage && $nomPatro !== $nomUsuel) {
            $nomComplet = "$nomPatro $prenom" . sprintf($this->avecNomUsageFormat, $nomUsuel);
        } else {
            $nomComplet = "$nomPatro $prenom";
        }

        $parts = [
            $civilite,
            $nomComplet
        ];

        return implode(' ', array_filter($parts));
    }

    /**
     * Génère le nom complet formatté de l'individu ou assimilé spécifié en argument.
     *
     * @see f()
     */
    public function filter($value): string
    {
        return $this->setValue($value)->f();
    }

    /**
     * Active ou non l'inclusion de la civilité (si elle est connue).
     */
    public function avecCivilite(bool $avecCivilite = true): static
    {
        $this->avecCivilite = $avecCivilite;

        return $this;
    }

    /**
     * Active ou non l'inclusion des autres prénoms (le cas échéant).
     */
    public function avecAutresPrenoms(bool $avecAutresPrenoms = true): static
    {
        $this->avecAutresPrenoms = $avecAutresPrenoms;

        return $this;
    }

    /**
     * Active ou non l'inclusion du nom d'usage, éventuellement selon le format spécifié.
     */
    public function avecNomUsage(bool $avecNomUsage = true, string $format = null): static
    {
        $this->avecNomUsage = $avecNomUsage;
        if ($format !== null) {
            $this->avecNomUsageFormat = $format;
        }

        return $this;
    }
}