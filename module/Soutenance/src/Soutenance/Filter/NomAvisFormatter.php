<?php

namespace Soutenance\Filter;

use Application\Entity\Db\Fichier;
use Individu\Entity\Db\Individu;
use Application\Filter\NomFichierFormatter;

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

    /**
     * NomAvisFormatter constructor.
     *
     * @param Individu $individu
     */
    public function __construct(Individu $individu)
    {
        $this->individu = $individu;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param Fichier $fichier
     * @return string
     */
    public function filter($fichier)
    {
        $parts = [];
        $parts['id'] = $fichier->getShortUuid();
        $parts['displayName'] = $this->normalizedString($this->individu->getNomUsuel() . " " . $this->individu->getPrenom());
        $parts['nature'] = $this->normalizedString($fichier->getNature()->getCode());

        $name = implode($this->separator, $parts);

        $pathParts = pathinfo($fichier->getNomOriginal());
        $extension = mb_strtolower($pathParts['extension']);

        return $name . '.' . $extension;
    }
}