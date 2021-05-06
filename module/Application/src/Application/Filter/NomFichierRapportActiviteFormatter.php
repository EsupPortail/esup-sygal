<?php

namespace Application\Filter;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\Rapport;

/**
 * Filtre générateur du nom de fichier de rapport d'activité annuel ou de fin de thèse.
 *
 * @author Unicaen
 */
class NomFichierRapportActiviteFormatter extends AbstractNomFichierFormatter
{
    private $separator = '-';

    /**
     * @var Rapport
     */
    private $rapportActivite;

    /**
     * NomFichierRapportActiviteFormatter constructor.
     *
     * @param Rapport $rapport
     */
    public function __construct(Rapport $rapport)
    {
        if (! $rapport->getTypeRapport()->estRapportActivite()) {
            throw new \InvalidArgumentException("Le rapport spécifié n'est pas un rapport d'activité");
        }

        $this->rapportActivite = $rapport;
    }

    /**
     * Retourne un nom de fichier conforme aux règles de nommage.
     *
     * @param  Fichier $fichier
     * @return string
     */
    public function filter($fichier)
    {
        $doctorant = $this->rapportActivite->getThese()->getDoctorant();
        $ed = $this->rapportActivite->getThese()->getEcoleDoctorale()->getStructure()->getCode();

        $extension = $this->extractExtensionFromFichier($fichier);

        $parts = [];
        $parts['type'] = $this->normalizedString($this->rapportActivite->getTypeRapport()->getCode());
        $parts['ed'] = 'ED' . $ed;
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = ucfirst(mb_strtolower($this->normalizedString($doctorant->getIndividu()->getPrenom())));
        $parts['date'] = $this->rapportActivite->getAnneeUnivToString('-');
        $parts['id'] = $fichier->getShortUuid(); // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.' . $extension;
    }
}