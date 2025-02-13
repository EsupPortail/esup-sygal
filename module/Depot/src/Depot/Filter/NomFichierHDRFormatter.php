<?php

namespace Depot\Filter;

use Depot\Entity\Db\FichierHDR;
use Fichier\Filter\AbstractNomFichierFormatter;

/**
 * Filtre générateur du nom de fichier lié à une HDR.
 *
 * @author Unicaen
 */
class NomFichierHDRFormatter extends AbstractNomFichierFormatter
{
    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  FichierHDR $fichierHDR
     */
    public function filter($fichierHDR): string
    {
        //TODO CHANGER LORSQUE CE SERA CANDIDAT
        $doctorant = $fichierHDR->getHDR()->getCandidat();

        $parts = [];

        $proposition = $fichierHDR->getHDR()->getCurrentProposition();
        $date = $proposition?->getDate() ?: null;
        $parts['annee'] = $date ? $date->format('Y') : date('Y');

        $parts['nomCandidat']    = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomCandidat'] = $this->normalizedString($doctorant->getIndividu()->getPrenom());

        // on inclue un id unique car il peut y avoir plusieurs fichiers de même nature déposés, exemples :
        // - plusieurs pré-rapports de soutenance pour une même HDR,
        // - 2 fichiers de HDRs pour un doctorant menant 2 HDRs en parallèle.
        $parts['id'] = $fichierHDR->getFichier()->getShortUuid();

        switch (true) {
            case $fichierHDR->getFichier()->getNature()->estThesePdf():
            case $fichierHDR->getFichier()->getNature()->estFichierNonPdf():
                $parts['version'] = $fichierHDR->getFichier()->getVersion()->getCode();
                break;
            default:
                $nature = $fichierHDR->getFichier()->getNature()->getCodeToLowerAndDash();
                $parts['nature'] = mb_strtoupper($nature);
                break;
        }

        if ($fichierHDR->getFichier()->getNature()->estFichierNonPdf()) {
            $parts['annexe'] = 'ANNEXE';
        }

        $name = implode($this->separator, $parts);

        $extension = $this->extractExtensionFromFichier($fichierHDR->getFichier());

        return $name . '.' . $extension;
    }
}