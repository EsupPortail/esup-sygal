<?php

namespace Soutenance\Filter;

use Fichier\Entity\Db\Fichier;
use Fichier\Filter\NomFichierFormatter;
use Individu\Entity\Db\Individu;

/**
 * Filtre générateur du nom de l'avis téléversé.
 *
 * @author Unicaen
 */
class NomAvisFormatter extends NomFichierFormatter
{
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
     */
    public function filter($fichier): string
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