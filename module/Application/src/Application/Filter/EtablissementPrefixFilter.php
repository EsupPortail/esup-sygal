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
     * @throws LogicException La chaîne de caractère spécifiée n'est pas préfixée
     * @return string Ex: "ABC123"
     */
    public function filter($value)
    {
        $pos = stripos($value, self::ETAB_PREFIX_SEP);
        if ($pos === false) {
            throw new LogicException("La chaîne de caractère spécifiée n'est pas préfixée par l'établissement");
        }

        return substr($value, $pos + strlen(self::ETAB_PREFIX_SEP));
    }

    /**
     * Proxy de la méthode filter().
     *
     * @param  string $value Ex: "UCN::ABC123"
     * @throws RuntimeException If filtering $value is impossible
     * @return string Ex: "ABC123"
     *
     * @see filter()
     */
    public function removePrefixFrom($value)
    {
        return $this->filter($value);
    }

    /**
     * Ajoute devant une chaîne de caractères le "préfixe établissement"
     * (i.e. le code établissement + un séparateur) spécifié.
     *
     * @param  string               $value         Ex: "ABC123"
     * @param  Etablissement|string $etablissement Instance d'Etablissement, ou code établissement
     * @return string Ex: "UCN::ABC123"
     */
    public function addPrefixTo($value, $etablissement)
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getStructure()->getCode();
        }

        return $etablissement . self::ETAB_PREFIX_SEP . $value;
    }

    /**
     * Retourne le motif de recherche en bdd "quelque soit le préfixe établissement".
     *
     * @param string $value Ex: "ABC123"
     * @return string Ex: "%::ABC123"
     */
    public function addSearchPatternPrefix($value)
    {
        return '%' . self::ETAB_PREFIX_SEP . $value;
    }
}