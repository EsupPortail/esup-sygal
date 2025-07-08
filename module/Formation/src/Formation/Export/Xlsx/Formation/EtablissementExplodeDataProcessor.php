<?php

namespace Formation\Export\Xlsx\Formation;

use Application\Export\Xlsx\ExplodeDataProcessor;
use Structure\Entity\Db\Etablissement;
use Webmozart\Assert\Assert;

class EtablissementExplodeDataProcessor extends ExplodeDataProcessor
{
    /**
     * @var array<\Structure\Entity\Db\Etablissement>
     */
    protected array $etablissements;

    /**
     * Exemple si 4 établissements : `['INSA', 'UCN', 'ULHN', 'URN']`
     * @var array<string>
     */
    protected array $codes;

    public function setEtablissements(array $etablissements): void
    {
        Assert::allIsInstanceOf($etablissements, Etablissement::class);
        $this->etablissements = $etablissements;

        // codes = source_code des établissements, ex : ['INSA', 'UCN', 'ULHN', 'URN']
        $this->codes = array_map(
            fn(Etablissement $e) => $this->extractEtablissementCode($e),
            $this->etablissements
        );
    }

    /**
     * Retourne pour l'établissement donné le code qu'on s'attend à trouver dans chaque valeur à exploser.
     * Ici on s'attend à trouver un `etablissement.sourceCode`.
     * Ex : dans 'INSA:12, ULHN:5', on considère que 'INSA' ou 'ULHN' correspond à un `etablissement.sourceCode`.
     */
    protected function extractEtablissementCode(Etablissement $etablissement): string
    {
        return $etablissement->getSourceCode();
    }
}