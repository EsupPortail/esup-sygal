<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;

/**
 * Filtre générateur du nom de fichier.
 *
 * @author Unicaen
 */
class NomFichierFormatter extends AbstractNomFichierFormatter
{
    private $separator = '-';

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @return string
     */
    public function filter($fichier)
    {
        $extension = $this->extractExtensionFromFichier($fichier);

        $parts = [];

        $nomOriginalSansExtension = substr($fichier->getNomOriginal(), 0, -1 * (strlen($extension)+1));
        $parts['nom'] = $this->normalizedString($nomOriginalSansExtension);

        // on inclue un id unique car il peut y avoir plusieurs fichiers de même nom déposés
        $parts['id'] = $fichier->getShortUuid();

        $parts['nature'] = $this->normalizedString($fichier->getNature()->getCode());

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }
}