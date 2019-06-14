<?php

namespace Soutenance\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\Individu;
use Application\Filter\NomFichierFormatter;
use UnicaenApp\Util;
use Zend\Filter\Exception;

/**
 * Filtre générateur du nom de l'avis téléversé.
 *
 * @author Unicaen
 */
class NomAvisFormatter extends NomFichierFormatter
{
    private $separator = '-';
    /** @var Individu */
    private $individu;

    public function __construct($individu)
    {
        $this->individu = $individu;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($fichier)
    {

        $parts = [];
        $parts['id']            = $fichier->getShortId();
        $parts['displayName']   = mb_strtoupper($this->transformText($this->individu->getNomUsuel()." ".$this->individu->getPrenom()));
        $nature = str_replace('_', '-', $fichier->getNature()->getCode());
        $parts['nature'] = mb_strtoupper($nature);

        $name = implode($this->separator, $parts);

        $pathParts = pathinfo($fichier->getNomOriginal());
        $extension = mb_strtolower($pathParts['extension']);

        return $name . '.' . $extension;
    }
}