<?php

namespace RapportActivite\Formatter;

use Fichier\Entity\Db\Fichier;
use Fichier\Filter\AbstractNomFichierFormatter;
use RapportActivite\Entity\Db\RapportActivite;

/**
 * Filtre générateur du nom de fichier de rapport d'activité.
 *
 * @author Unicaen
 */
class RapportActiviteNomFichierFormatter extends AbstractNomFichierFormatter
{
    private string $separator = '-';

    /**
     * @var \RapportActivite\Entity\Db\RapportActivite
     */
    private RapportActivite $rapport;

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     */
    public function __construct(RapportActivite $rapport)
    {
        $this->rapport = $rapport;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $value
     * @return string
     */
    public function filter($value): string
    {
        $doctorant = $this->rapport->getThese()->getDoctorant();
        $ed = $this->rapport->getThese()->getEcoleDoctorale()->getStructure()->getCode();

        $extension = $this->extractExtensionFromFichier($value);

        $parts = [];
        $parts['type'] = $this->normalizedString($this->type());
        $parts['date'] = $this->rapport->getAnneeUniv()->toString('-');
        $parts['ed'] = 'ED' . $ed;
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = ucfirst(mb_strtolower($this->normalizedString($doctorant->getIndividu()->getPrenom())));
        $parts['id'] = $value->getShortUuid(); // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }

    protected function type(): string
    {
        if ($this->rapport->getTypeRapport()->estRapportActivite()) {
            return $this->rapport->getTypeRapport()->getCode() . ($this->rapport->estFinContrat() ? '_FINCONTRAT' : '_ANNUEL');
        } else {
            return $this->rapport->getTypeRapport()->getCode();
        }
    }
}