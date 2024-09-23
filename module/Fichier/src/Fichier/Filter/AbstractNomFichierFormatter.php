<?php

namespace Fichier\Filter;

use Fichier\Entity\Db\Fichier;
use Laminas\Filter\AbstractFilter;
use UnicaenApp\Util;

/**
 * Filtre générateur de nom de fichier.
 *
 * @author Unicaen
 */
abstract class AbstractNomFichierFormatter extends AbstractFilter
{
    protected string $separator = '-';

    protected function extractExtensionFromFichier(Fichier $fichier): string
    {
        $pathParts = pathinfo($fichier->getNomOriginal());

        return mb_strtolower($pathParts['extension']);
    }

    protected function normalizedString(string $str): string
    {
        $nom = str_replace('_', $this->separator, $this->transformText($str));

        return mb_strtoupper($nom);
    }

    private function transformText(string $str): string
    {
        $s = $this->separator;

        $from = "ÀÁÂÃÄÅÇÐÈÉÊËÌÍÎÏÒÓÔÕÖØÙÚÛÜŸÑàáâãäåçðèéêëìíîïòóôõöøùúûüÿñ€@ \"'";
        $to   = "AAAAAACDEEEEIIIIOOOOOOUUUUYNaaaaaacdeeeeiiiioooooouuuuynEA$s$s$s";

        return Util::strtr($str, $from, $to);
    }
}