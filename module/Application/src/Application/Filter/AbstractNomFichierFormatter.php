<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;

/**
 * Filtre générateur du nom de fichier.
 *
 * @author Unicaen
 */
abstract class AbstractNomFichierFormatter extends AbstractFilter
{
    private $separator = '-';

    /**
     * @param Fichier $fichier
     * @return string
     */
    protected function extractExtensionFromFichier(Fichier $fichier)
    {
        $pathParts = pathinfo($fichier->getNomOriginal());

        return mb_strtolower($pathParts['extension']);
    }

    /**
     * @param string $str
     * @return string
     */
    protected function normalizedString($str)
    {
        $nom = str_replace('_', '-', $this->transformText($str));

        return mb_strtoupper($nom);
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