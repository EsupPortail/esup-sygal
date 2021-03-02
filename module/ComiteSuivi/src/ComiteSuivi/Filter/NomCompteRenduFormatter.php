<?php

namespace ComiteSuivi\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\Individu;
use Application\Filter\NomFichierFormatter;
use ComiteSuivi\Entity\Db\Membre;

/**
 * Filtre générateur du nom de l'avis téléversé.
 *
 * @author Unicaen
 */
class NomCompteRenduFormatter extends NomFichierFormatter
{
    private $separator = '-';
    /** @var Membre */
    private $membre;

    /**
     * NomAvisFormatter constructor.
     *
     * @param Membre $membre
     */
    public function __construct(Membre $membre)
    {
        $this->membre = $membre;
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
        $parts['displayName'] = $this->normalizedString($this->membre->getDenomination());
        $parts['nature'] = $this->normalizedString($fichier->getNature()->getCode());

        $name = implode($this->separator, $parts);

        $pathParts = pathinfo($fichier->getNomOriginal());
        $extension = mb_strtolower($pathParts['extension']);

        return $name . '.' . $extension;
    }
}