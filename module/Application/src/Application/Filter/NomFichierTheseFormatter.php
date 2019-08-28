<?php

namespace Application\Filter;

use Application\Entity\Db\FichierThese;
use Zend\Filter\Exception;

/**
 * Filtre générateur du nom de fichier lié à une thèse.
 *
 * @author Unicaen
 */
class NomFichierTheseFormatter extends AbstractNomFichierFormatter
{
    private $separator = '-';

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  FichierThese $fichierThese
     * @return string
     * @throws Exception\RuntimeException If filtering $value is impossible
     */
    public function filter($fichierThese)
    {
        $doctorant = $fichierThese->getThese()->getDoctorant();

        $parts = [];

        $date = $fichierThese->getThese()->getDateSoutenance() ?: $fichierThese->getThese()->getDatePrevisionSoutenance();
        $parts['annee'] = $date ? $date->format('Y') : date('Y');

        $parts['nomDoctorant']    = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getPrenom());

        // on inclue un id unique car il peut y avoir plusieurs fichiers de même nature déposés, exemples :
        // - plusieurs pré-rapports de soutenance pour une même thèse,
        // - 2 fichiers de thèses pour un doctorant menant 2 thèses en parallèle.
        $parts['id'] = $fichierThese->getFichier()->getShortUuid();

        switch (true) {
            case $fichierThese->getFichier()->getNature()->estThesePdf():
            case $fichierThese->getFichier()->getNature()->estFichierNonPdf():
                $parts['version'] = $fichierThese->getFichier()->getVersion()->getCode();
                break;
            default:
                $nature = str_replace('_', '-', $fichierThese->getFichier()->getNature()->getCode());
                $parts['nature'] = mb_strtoupper($nature);
                break;
        }

        if ($fichierThese->getFichier()->getNature()->estFichierNonPdf()) {
            $parts['annexe'] = 'ANNEXE';
        }

        $name = implode($this->separator, $parts);

        $extension = $this->extractExtensionFromFichier($fichierThese->getFichier());

        return $name . '.' . $extension;
    }
}