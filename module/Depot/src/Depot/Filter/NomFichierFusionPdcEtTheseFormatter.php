<?php

namespace Depot\Filter;

use Fichier\Filter\AbstractNomFichierFormatter;
use These\Entity\Db\These;
use UnicaenApp\Util;

/**
 * Formateur du nom de fichier créé pour la fusion de la page de couverture et du manuscrit de thèse.
 */
class NomFichierFusionPdcEtTheseFormatter extends AbstractNomFichierFormatter
{
    /**
     * NB : Ici, le séparateur est le caractère "underscore" car ce formatter est utilisé pour nommer
     * le fichier que les gestionnaires déposent sur STEP-STAR, or ce dernier n'aime pas le "tiret du 6".
     */
    protected string $separator = '_';

    protected string $prefix = 'sygal_fusion';

    /**
     * @param These $value
     */
    public function filter($value): string
    {
        $individuDoctorant = $value->getDoctorant()->getIndividu();

        $parts = [
            $this->prefix,
            $value->getId(),
            $this->normalizedString($individuDoctorant->getNomUsuel()),
            $this->normalizedString($individuDoctorant->getPrenom()),
            uniqid(),
        ];

        $outputFilePath = implode($this->separator, $parts) . '.pdf';

        return Util::reduce($outputFilePath);
    }
}