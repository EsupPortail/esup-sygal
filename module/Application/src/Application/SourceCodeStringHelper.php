<?php

namespace Application;

use Application\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

/**
 * Service incontournable (si si) à utiliser pour la génération/manipulation de chaîne de caratères
 * étant des SOURCE_CODE.
 *
 * @author Unicaen
 */
class SourceCodeStringHelper
{
    /**
     * @var string
     */
    private $separator = '::';

    /**
     * @var string
     */
    private $defaultPrefix;

    /**
     * @param string $defaultPrefix
     */
    public function setDefaultPrefix($defaultPrefix)
    {
        $this->defaultPrefix = $defaultPrefix;
    }

    /**
     * Retourne la partie d'une chaîne de caractères située après le "préfixe établissement"
     * (i.e. le code établissement + un séparateur).
     *
     * @param string $value Ex: "UCN::ABC123"
     * @throws RuntimeException La chaîne de caractères spécifiée n'est pas préfixée
     * @return string Ex: "ABC123"
     */
    public function removePrefixFrom($value)
    {
        $pos = stripos($value, $this->separator);
        if ($pos === false) {
            throw new RuntimeException("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, $pos + strlen($this->separator));
    }

    /**
     * Ajoute devant une chaîne de caractères le préfixe spécifié puis le séparateur.
     *
     * @param  string $value  Ex: "ABC123"
     * @param  string $prefix Préfixe obligatoire, ex: 'UCN'.
     * @return string Ex: "UCN::ABC123"
     */
    public function addPrefixTo($value, $prefix)
    {
        return $prefix . $this->separator . $value;
    }

    /**
     * Ajoute devant une chaîne de caractères le préfixe par défaut puis le séparateur.
     *
     * @param  string $value Ex: "ABC123"
     * @return string Ex: "SyGAL::ABC123"
     */
    public function addDefaultPrefixTo($value)
    {
        if ($this->defaultPrefix === null) {
            throw new RuntimeException("Anomalie: aucun préfixe par défaut n'a été spécifié.");
        }

        return $this->addPrefixTo($value, $this->defaultPrefix);
    }

    /**
     * Ajoute devant une chaîne de caractères le 'code' de l'établissement spécifié puis le séparateur.
     *
     * @param  string        $value         Ex: "ABC123"
     * @param  Etablissement $etablissement Etablissement dont le 'code' sera utilisé comme préfixe.
     * @return string Ex: "UCN::ABC123"
     */
    public function addEtablissementPrefixTo($value, Etablissement $etablissement)
    {
        if (! $etablissement->getStructure()->getCode()) {
            throw new RuntimeException(
                "Impossible de préfixer car l'établissement dont l'id est {$etablissement->getId()} n'a pas de code");
        }

        return $this->addPrefixTo($value, $etablissement->getStructure()->getCode());
    }

    /**
     * Retourne le code établissement présent dans le "préfixe établissement" de la chaîne de caractères spécifiée.
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @throws RuntimeException La chaîne de caractères spécifiée n'est pas préfixée
     * @return string Ex: "UCN"
     */
    public function extractPrefixFrom($value)
    {
        $pos = stripos($value, $this->separator);
        if ($pos === false) {
            throw new RuntimeException("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, 0, $pos);
    }

    /**
     * Génère ce motif de recherche : "tous les source_code suffixés par".
     *
     * @param string $value Ex: "ABC123"
     * @return string Ex: "%::ABC123"
     */
    public function generateSearchPatternForAnyPrefix($value)
    {
        return $this->addPrefixTo($value, '%');
    }

    /**
     * Génère ce motif de recherche : "tous les source_code préfixés par".
     *
     * @param string $value Ex: "UCN"
     * @return string Ex: "UCN::%"
     */
    public function generateSearchPatternForThisPrefix($value)
    {
        return $this->addPrefixTo('%', $value);
    }
}