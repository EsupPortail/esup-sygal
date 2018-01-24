<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 26/04/16
 * Time: 10:54
 */
class NomFichierFormatter extends AbstractFilter
{
    private $separator = '-';

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($fichier)
    {
        $doctorant = $fichier->getThese()->getDoctorant();

        $parts = [];

        $parts['annee'] = date('Y');

        $parts['nomDoctorant']    = mb_strtoupper($this->transformText($doctorant->getNomUsuel()));
        $parts['prenomDoctorant'] = mb_strtoupper($this->transformText($doctorant->getPrenom()));

        $parts['version'] = $fichier->getVersion()->getCode();

        if ($fichier->getEstAnnexe()) {
            $parts['annexe'] = uniqid('ANNEXE-');
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