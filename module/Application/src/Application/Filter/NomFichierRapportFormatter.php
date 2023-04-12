<?php

namespace Application\Filter;

use Fichier\Entity\Db\Fichier;
use Application\Entity\Db\Rapport;
use Fichier\Filter\AbstractNomFichierFormatter;

/**
 * Filtre générateur du nom de fichier de rapport d'activité, CSI, etc.
 *
 * @author Unicaen
 */
class NomFichierRapportFormatter extends AbstractNomFichierFormatter
{
    private $separator = '-';

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @param Rapport $rapport
     */
    public function __construct(Rapport $rapport)
    {
        $this->rapport = $rapport;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @return string
     */
    public function filter($fichier): string
    {
        $doctorant = $this->rapport->getThese()->getDoctorant();
        $ed = $this->rapport->getThese()->getEcoleDoctorale()->getStructure()->getCode();

        $extension = $this->extractExtensionFromFichier($fichier);

        $parts = [];
        $parts['type'] = $this->normalizedString($this->type());
        $parts['ed'] = 'ED' . $ed;
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = ucfirst(mb_strtolower($this->normalizedString($doctorant->getIndividu()->getPrenom())));
        $parts['date'] = $this->rapport->getAnneeUniv()->toString('-');
        $parts['id'] = $fichier->getShortUuid(); // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }

    protected function type(): string
    {
        return $this->rapport->getTypeRapport()->getCode();
    }
}