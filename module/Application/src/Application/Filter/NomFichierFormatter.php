<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use Zend\Filter\AbstractFilter;

/**
 * Filtre générateur du nom de fichier téléversé.
 *
 * @author Unicaen
 */
class NomFichierFormatter extends AbstractFilter
{
    private $separator = '-';

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @return string
     */
    public function filter($fichier)
    {
        $parts = [];

        // on inclue un id unique car il peut y avoir plusieurs fichiers de même nature déposés
        $parts['id'] = $fichier->getShortUuid();

        $parts['version'] = $fichier->getVersion()->getCode();

        $nature = str_replace('_', '-', $fichier->getNature()->getCode());
        $parts['nature'] = mb_strtoupper($nature);

        $name = implode($this->separator, $parts);

        $pathParts = pathinfo($fichier->getNomOriginal());
        $extension = mb_strtolower($pathParts['extension']);

        return $name . '.' . $extension;
    }
}