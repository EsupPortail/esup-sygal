<?php

namespace Application\Filter;

use Application\Entity\Db\Etablissement;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Filter\FilterInterface;

class EtablissementPrefixFilter implements FilterInterface
{
    const ETAB_PREFIX_SEP = '::';
    
    /**
     * Retourne la partie d'une chaîne de caractères située après le "préfixe établissement"
     * (i.e. le code établissement + un séparateur).
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @throws RuntimeException La chaîne de caractère spécifiée n'est pas préfixée
     * @return string Ex: "ABC123"
     */
    public function filter($value)
    {
        $pos = stripos($value, self::ETAB_PREFIX_SEP);
        if ($pos === false) {
            throw new RuntimeException("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, $pos + strlen(self::ETAB_PREFIX_SEP));
    }

    /**
     * Proxy de la méthode filter().
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @throws RuntimeException La chaîne de caractère spécifiée n'est pas préfixée
     * @return string Ex: "ABC123"
     *
     * @see filter()
     */
    public function removePrefixFrom($value)
    {
        return $this->filter($value);
    }

    /**
     * Retourne le code établissement présent dans le "préfixe établissement" de la chaîne de caractères spécifiée.
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @throws RuntimeException La chaîne de caractère spécifiée n'est pas préfixée
     * @return string Ex: "UCN"
     */
    public function extractCodeEtablissementFrom($value)
    {
        $pos = stripos($value, self::ETAB_PREFIX_SEP);
        if ($pos === false) {
            throw new RuntimeException("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, 0, $pos);
    }

    /**
     * Ajoute devant une chaîne de caractères le préfixe spécifié puis le séparateur.
     *
     * @param  string $value  Ex: "ABC123"
     * @param  string $prefix Préfixe éventuel, ex: 'UCN'.
     *                        Si aucun préfixe n'est spécifié, le préfixe par défaut est utilisé.
     * @return string Ex: "UCN::ABC123"
     */
    public function addPrefixTo($value, $prefix = null)
    {
        if ($prefix === null) {
            $prefix = Etablissement::CODE_COMUE;
        }

        return $prefix . self::ETAB_PREFIX_SEP . $value;
    }

    /**
     * Ajoute devant une chaîne de caractères le "préfixe établissement"
     * (i.e. le code établissement + un séparateur) spécifié.
     *
     * @param  string        $value         Ex: "ABC123"
     * @param  Etablissement $etablissement Etablissement dont le code sera utilisé comme préfixe.
     *                                      Si aucun établissement n'est spécifié, le préfixe par défaut est utilisé.
     * @return string Ex: "UCN::ABC123"
     */
    public function addPrefixEtablissementTo($value, Etablissement $etablissement = null)
    {
        if ($etablissement === null) {
            return $this->addPrefixTo($value);
        }

        if (! $etablissement->getCode()) {
            throw new RuntimeException(
                "Impossible de préfixer car l'établissement dont l'id est {$etablissement->getId()} n'a pas de code");
        }

        return $etablissement->getCode() . self::ETAB_PREFIX_SEP . $value;
    }

    /**
     * Retourne le motif de recherche en bdd "quelque soit le préfixe établissement".
     *
     * @param string $value Ex: "ABC123"
     * @return string Ex: "%::ABC123"
     * @deprecated Utiliser à la place generateSourceCodeSearchPatternForAnyEtablissement()
     */
    public function addSearchPatternPrefix($value)
    {
        return $this->generateSourceCodeSearchPatternForAnyEtablissement($value);
    }

    /**
     * Génère ce motif de recherche en bdd par source code : "cet établissement précis".
     *
     * @param string $value Ex: "ABC123"
     * @return string Ex: "%::ABC123"
     */
    public function generateSourceCodeSearchPatternForAnyEtablissement($value)
    {
        return '%' . self::ETAB_PREFIX_SEP . $value;
    }

    /**
     * Génère ce motif de recherche en bdd par source code : "cet établissement précis".
     *
     * @param Etablissement|string $etablissement
     * @return string Ex: "UCN::%"
     */
    public function generateSourceCodeSearchPatternForThisEtablissement($etablissement)
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getStructure()->getCode();
        }

        return $etablissement . self::ETAB_PREFIX_SEP . '%';
    }
}