<?php

namespace Application;

use Exception;
use InvalidArgumentException;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

/**
 * Service incontournable (si si) à utiliser pour la génération/manipulation de chaîne de caratères
 * étant des SOURCE_CODE.
 *
 * @author Unicaen
 */
class SourceCodeStringHelper
{
    private string $separator = '::';
    private ?string $defaultPrefix = null;

    /**
     * @param string $defaultPrefix
     */
    public function setDefaultPrefix(string $defaultPrefix)
    {
        $this->defaultPrefix = $defaultPrefix;
    }

    /**
     * Retourne la partie d'une chaîne de caractères située après le "préfixe établissement"
     * (i.e. le code établissement + un séparateur).
     *
     * @param string $value Ex: "UCN::ABC123"
     * @return string Ex: "ABC123"
     * @throws \Exception La chaîne de caractères spécifiée n'est pas préfixée
     */
    public function removePrefixFrom(string $value): string
    {
        $pos = $this->computeSeparatorPosition($value);
        if ($pos === false) {
            throw new Exception("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
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
    public function addPrefixTo(string $value, string $prefix): string
    {
        return $prefix . $this->separator . $value;
    }

    /**
     * Ajoute devant une chaîne de caractères le préfixe par défaut puis le séparateur.
     *
     * @param  string $value Ex: "ABC123"
     * @return string Ex: "SyGAL::ABC123"
     */
    public function addDefaultPrefixTo(string $value): string
    {
        if ($this->defaultPrefix === null) {
            throw new InvalidArgumentException("Anomalie: aucun préfixe par défaut n'a été spécifié.");
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
    public function addEtablissementPrefixTo(string $value, Etablissement $etablissement): string
    {
        if (! $etablissement->getStructure()->getCode()) {
            throw new InvalidArgumentException(
                "Impossible de préfixer car l'établissement dont l'id est {$etablissement->getId()} n'a pas de code");
        }

        return $this->addPrefixTo($value, $etablissement->getStructure()->getCode());
    }

    /**
     * Retourne le "préfixe établissement" de la chaîne de caractères spécifiée.
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @return string Ex: "UCN"
     * @throws Exception La chaîne de caractères spécifiée n'est pas préfixée
     */
    public function extractPrefixFrom(string $value): ?string
    {
        $pos = $this->computeSeparatorPosition($value);
        if ($pos === false) {
            throw new Exception("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, 0, $pos);
    }

    /**
     * Génère ce motif de recherche : "tous les source_code suffixés par".
     *
     * @param string $value Ex: "ABC123"
     * @return string Ex: "%::ABC123"
     */
    public function generateSearchPatternForAnyPrefix(string $value): string
    {
        return $this->addPrefixTo($value, '%');
    }

    /**
     * Génère ce motif de recherche : "tous les source_code préfixés par".
     *
     * @param string $value Ex: "UCN"
     * @return string Ex: "UCN::%"
     */
    public function generateSearchPatternForThisPrefix(string $value): string
    {
        return $this->addPrefixTo('%', $value);
    }

    /**
     * Détermine la position du séparateur dans a chaîne spécifiée.
     * Retourne `false` si le séparateur n'est pas trouvé.
     *
     * @param string $value
     * @return false|int
     */
    protected function computeSeparatorPosition(string $value)
    {
        return stripos($value, $this->separator);
    }
}