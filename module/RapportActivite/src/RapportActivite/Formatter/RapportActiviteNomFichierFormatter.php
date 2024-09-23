<?php

namespace RapportActivite\Formatter;

use Fichier\Filter\AbstractNomFichierFormatter;
use Ramsey\Uuid\Uuid;
use RapportActivite\Entity\Db\RapportActivite;

/**
 * Filtre générateur du nom de fichier de rapport d'activité.
 *
 * @author Unicaen
 */
class RapportActiviteNomFichierFormatter extends AbstractNomFichierFormatter
{
    /**
     * @var \RapportActivite\Entity\Db\RapportActivite
     */
    private RapportActivite $rapportActivite;

    /**
     * Retourne un nom de fichier conforme aux règles de nommage pour le rapport spécifié.
     *
     * @param RapportActivite $value
     * @return string
     */
    public function filter($value): string
    {
        $this->rapportActivite = $value;

        $these = $this->rapportActivite->getThese();
        $doctorant = $these->getDoctorant();
        $ed = $these->getEcoleDoctorale()->getStructure()->getCode();
        $ur = $these->getUniteRecherche()->getStructure()->getCode();
        $uid = strstr(Uuid::uuid4()->toString(), '-', true);

        $parts = [];
        $parts['type'] = $this->normalizedString($this->type());
        $parts['date'] = $this->rapportActivite->getAnneeUniv()->toString('-');
        $parts['etab'] = $these->getEtablissement()->getStructure()->getSourceCode();
        $parts['ed'] = 'ED' . $ed;
        $parts['ur'] = $ur;
        $parts['nomDoctorant'] = $this->normalizedString($doctorant->getIndividu()->getNomUsuel());
        $parts['prenomDoctorant'] = ucfirst(mb_strtolower($this->normalizedString($doctorant->getIndividu()->getPrenom())));
        $parts['id'] = $uid; // on inclue un id unique au cas où il y ait plusieurs fichiers de même nom déposés

        $name = implode($this->separator, $parts);

        return $name . '.pdf';
    }

    protected function type(): string
    {
        return 'RAPPORT_ACTIVITE' . ($this->rapportActivite->estFinContrat() ? '_FINCONTRAT' : '_ANNUEL');
    }
}