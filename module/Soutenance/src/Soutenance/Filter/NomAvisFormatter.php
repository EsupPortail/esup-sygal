<?php

namespace Soutenance\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\Individu;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

/**
 * Filtre générateur du nom de l'avis téléversé.
 *
 * @author Unicaen
 */
class NomAvisFormatter extends AbstractFilter
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
//        $parts['dateDepot']     = $fichier->getHistoCreation()->format('Ymd');
        $parts['displayName']   = mb_strtoupper($this->transformText($this->individu->getNomUsuel()." ".$this->individu->getPrenom()));
//        $parts['displayName']   = mb_strtoupper($this->transformText($this->individu->__toString()));
//        $parts['type']          = mb_strtoupper($this->transformText('avis-soutenance'));

        switch (true) {
            case $fichier->getNature()->estThesePdf():
            case $fichier->getNature()->estFichierNonPdf():
                $parts['version'] = $fichier->getVersion()->getCode();
                break;
            default:
                $nature = str_replace('_', '-', $fichier->getNature()->getCode());
                $parts['nature'] = mb_strtoupper($nature);
                break;
        }

        $name = implode($this->separator, $parts);

        $pathParts = pathinfo($fichier->getNomOriginal());
        $extension = mb_strtolower($pathParts['extension']);

        return $name . '.' . $extension;
    }

    /**
     * @param string $str
     * @param string $encoding
     *
     * @return string
     */
    private function transformText($str, $encoding = 'UTF-8')
    {
        $s = $this->separator;

        $from = "ÀÁÂÃÄÅÇÐÈÉÊËÌÍÎÏÒÓÔÕÖØÙÚÛÜŸÑàáâãäåçðèéêëìíîïòóôõöøùúûüÿñ€@ \"'";
        $to   = "AAAAAACDEEEEIIIIOOOOOOUUUUYNaaaaaacdeeeeiiiioooooouuuuynEA$s$s$s";

        return Util::strtr($str, $from, $to, false, $encoding);
    }
}