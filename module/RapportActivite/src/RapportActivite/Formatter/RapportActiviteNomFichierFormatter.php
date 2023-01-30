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
    private RapportActivite $rapportActivite;

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     */
    public function __construct(RapportActivite $rapport)
    {
        $this->rapportActivite = $rapport;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $value
     * @return string
     */
    public function filter($value): string
    {
        $doctorant = $this->rapportActivite->getThese()->getDoctorant();
        $ed = $this->rapportActivite->getThese()->getEcoleDoctorale()->getStructure()->getCode();

        $extension = $this->extractExtensionFromFichier($value);

        $parts = [];
        $parts['type'] = $this->normalizedString($this->type());
        $parts['date'] = $this->rapportActivite->getAnneeUniv()->toString('-');
        $parts['ed'] = 'ED' . $ed;
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = ucfirst(mb_strtolower($this->normalizedString($doctorant->getIndividu()->getPrenom())));
        $parts['id'] = $value->getShortUuid(); // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }

    protected function type(): string
    {
        return 'RAPPORT_ACTIVITE' . ($this->rapportActivite->estFinContrat() ? '_FINCONTRAT' : '_ANNUEL');
    }
}