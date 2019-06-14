<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

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
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($fichier)
    {
        $doctorant = $fichier->getThese()->getDoctorant();

        $parts = [];

        $date = $fichier->getThese()->getDateSoutenance() ?: $fichier->getThese()->getDatePrevisionSoutenance();
        $parts['annee'] = $date ? $date->format('Y') : date('Y');

        $parts['nomDoctorant']    = mb_strtoupper($this->transformText($doctorant->getNomUsuel()));
        $parts['prenomDoctorant'] = mb_strtoupper($this->transformText($doctorant->getPrenom()));

        // on inclue un id unique car il peut y avoir plusieurs fichiers de même nature déposés, exemples :
        // - plusieurs pré-rapports de soutenance pour une même thèse,
        // - 2 fichiers de thèses pour un doctorant menant 2 thèses en parallèle.
        $parts['id'] = $fichier->getShortId();

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

        if ($fichier->getEstAnnexe()) {
            $parts['annexe'] = 'ANNEXE';
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
    protected function transformText($str, $encoding = 'UTF-8')
    {
        $s = $this->separator;

        $from = "ÀÁÂÃÄÅÇÐÈÉÊËÌÍÎÏÒÓÔÕÖØÙÚÛÜŸÑàáâãäåçðèéêëìíîïòóôõöøùúûüÿñ€@ \"'";
        $to   = "AAAAAACDEEEEIIIIOOOOOOUUUUYNaaaaaacdeeeeiiiioooooouuuuynEA$s$s$s";

        return Util::strtr($str, $from, $to, false, $encoding);
    }
}