<?php

namespace Soutenance\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use DateTime;
use Exception;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;

/**
 * Filtre générateur du nom de l'avis téléversé.
 *
 * @author Unicaen
 */
class JustificatifFormatter extends AbstractFilter
{
    private $separator = '-';
    /** @var These */
    private $these;

    /**
     * JustificatifFormatter constructor.
     * @param These $these
     */
    public function __construct($these)
    {
        $this->these = $these;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @throws Exception
     * @return mixed
     */
    public function filter($fichier)
    {

        $parts = [];
//        $parts['id']            = $fichier->getShortId();
        $parts['nature']        = mb_strtoupper($fichier->getNature()->getCode());
        $parts['dateDepot']     = (new DateTime())->format('Ymd_His');
        $parts['displayName']   = mb_strtoupper($this->transformText($this->these->getDoctorant()->getIndividu()->getNomUsuel()."_".$this->these->getDoctorant()->getIndividu()->getPrenom()));

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