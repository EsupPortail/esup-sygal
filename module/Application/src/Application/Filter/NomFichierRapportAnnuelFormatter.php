<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\RapportAnnuel;

/**
 * Filtre générateur du nom de fichier de rapport annuel.
 *
 * @author Unicaen
 */
class NomFichierRapportAnnuelFormatter extends AbstractNomFichierFormatter
{
    private $separator = '-';

    /**
     * @var RapportAnnuel
     */
    private $rapportAnnuel;

    /**
     * NomFichierRapportAnnuelFormatter constructor.
     *
     * @param RapportAnnuel $rapportAnnuel
     */
    public function __construct(RapportAnnuel $rapportAnnuel)
    {
        $this->rapportAnnuel = $rapportAnnuel;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @return string
     */
    public function filter($fichier)
    {
        $doctorant = $this->rapportAnnuel->getThese()->getDoctorant();

        $extension = $this->extractExtensionFromFichier($fichier);

        $parts = [];
        $parts['nature'] = $this->normalizedString($fichier->getNature()->getCode());
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getPrenom());
        $parts['date'] = $this->rapportAnnuel->getAnneeUnivToString('-');
        $parts['id'] = $fichier->getShortUuid(); // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }
}