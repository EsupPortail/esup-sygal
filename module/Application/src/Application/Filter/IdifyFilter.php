<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Zend\Filter\Exception\RuntimeException;
use Zend\Filter\FilterInterface;

/**
 * Retourne l'id ou le code d'une instance d'entité.
 * Si un scalaire est spécifié, il est retourné tel quel.
 *
 * @author Unicaen
 */
class IdifyFilter implements FilterInterface
{
    /**
     * Accès rapide au filtrage.
     *
     * @param mixed $value
     * @return string|int
     */
    static public function id($value)
    {
        return (new static())->filter($value);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws RuntimeException If filtering $value is impossible
     * @return string|int
     */
    public function filter($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_scalar($value)) {
            return $value;
        }

        switch (true) {
            case $value instanceof VersionFichier:
                return $value->getCode();
                break;
            case $value instanceof NatureFichier:
                return $value->getCode();
                break;
            case $value instanceof Fichier:
                return $value->getUuid();
                break;
            case $value instanceof FichierThese:
                return $value->getFichier()->getUuid();
                break;
        }

        if (is_object($value) && method_exists($value, 'getId')) {
            return $value->getId();
        }

        throw new RuntimeException("Valeur à filtrer inattendue");
    }
}