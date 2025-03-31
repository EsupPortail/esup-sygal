<?php

namespace Application\Export\Xlsx;

use Webmozart\Assert\Assert;

/**
 * Date processor chargé d'exploser des colonnes de données en plusieurs parce qu'elles contiennent des données agrégées.
 *
 * - Exemple avec les colonnes `['nb_presents']` et une liste de "codes" `['INSA','ULHN']` : dans la ligne de données
 * `['seance_id'=>123, 'nb_presents'=>'INSA:12, ULHN:5']`, la colonne 'nb_presents' sera explosée en 2 colonnes,
 * ce qui transformerait la ligne en ceci :
 * `['seance_id'=>123, 'nb_presents_INSA'=>'12', 'nb_presents_ULHN'=>'5']`.
 *
 * - Même exemple mais avec la liste de "codes" `['INSA','UCN','ULHN','URN']`, la ligne deviendrait ceci :
 * `['seance_id'=>123, 'nb_presents_INSA'=>'12', 'nb_presents_UCN'=>'', 'nb_presents_ULHN'=>'5', 'nb_presents_URN'=>'']`.
 *
 *  - Même exemple mais en préservant les colonnes, la ligne deviendrait ceci :
 *  `['seance_id'=>123, 'nb_presents'=>'INSA:12, ULHN:5', 'nb_presents_INSA'=>'12', 'nb_presents_UCN'=>'', 'nb_presents_ULHN'=>'5', 'nb_presents_URN'=>'']`.
 */
class ExplodeDataProcessor implements XlsxExportDataProcessorInterface
{
    protected array $columns;

    /**
     * Spécifie les noms de colonnes concernées par l'explosion.
     * @param array $columns Ex : ['nb_presents', 'nb_absents']
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @var array<string>
     */
    protected array $codes;

    /**
     * Spécifie l'esemble des codes possibles. Ex : `['INSA','UCN','ULHN','URN']`.
     * @param array<string> $codes
     */
    public function setCodes(array $codes): void
    {
        $this->codes = $codes;
    }

    protected bool $preserveColumns = false;

    /**
     * Spécifie si les colonnes faisant l'objet d'une explosion doivent être conservées ou pas.
     */
    public function setPreserveColumns(bool $preserveColumns): void
    {
        $this->preserveColumns = $preserveColumns;
    }

    protected string $outerPregSplitPattern = "/[\s,]+/";

    public function setOuterPregSplitPattern(string $outerPregSplitPattern): void
    {
        $this->outerPregSplitPattern = $outerPregSplitPattern;
    }

    protected string $innerPregSplitPattern = "/[\s:]+/";

    public function setInnerPregSplitPattern(string $innerPregSplitPattern): void
    {
        $this->innerPregSplitPattern = $innerPregSplitPattern;
    }

    public function __construct(array $options = null)
    {
        if (isset($options['columns'])) {
            $this->setColumns($options['columns']);
        }
        if (isset($options['codes'])) {
            $this->setCodes($options['codes']);
        }
        if (isset($options['preserve_columns'])) {
            $this->setPreserveColumns((bool)$options['preserve_columns']);
        }
        if (isset($options['outer_preg_split_pattern'])) {
            $this->setOuterPregSplitPattern($options['outer_preg_split_pattern']);
        }
        if (isset($options['inner_preg_split_pattern'])) {
            $this->setInnerPregSplitPattern($options['inner_preg_split_pattern']);
        }
    }

    public function processData(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        foreach ($this->columns as $column) {
            Assert::keyExists($data[0], $column, "La colonne %s n'existe pas dans les données");
        }

        return array_map(
            fn($row) => $this->processColumns($row, $this->columns),
            $data
        );
    }

    /**
     * @param array $row Ex : ['seance_id'=>123, 'nb_presents'=>'INSA:12, ULHN:5']
     * @param array<string> $columnsToTransform Ex : ['nb_presents']
     * @return array Ex : ['seance_id'=>123, 'nb_presents_INSA'=>'12', 'nb_presents_UCN'=>'', 'nb_presents_ULHN'=>'5', 'nb_presents_URN'=>'']
     */
    protected function processColumns(array $row, array $columnsToTransform): array
    {
        $transformedRow = [];

        // parcours des colonnes pour en exploser/décliner certaines par établissement
        foreach ($row as $col => $value) {
            if (in_array($col, $columnsToTransform)) {
                if ($this->preserveColumns) {
                    $transformedRow[$col] = $value;
                }
                // explosion/déclinaison par établissement
                $array = $this->explodeColumn($value, $col);
                $transformedRow += $array;
            } else {
                $transformedRow[$col] = $value;
            }
        }

        return $transformedRow;
    }

    /**
     * @param string|null $value Ex : 'INSA:12, ULHN:5' ou 'INSA:12,ULHN:5'
     * @param string $column Ex : 'nb_presents'
     * @return array Ex : ['nb_presents_INSA'=>'12', 'nb_presents_UCN'=>'', 'nb_presents_ULHN'=>'5', 'nb_presents_URN'=>'']
     */
    protected function explodeColumn(?string $value, string $column): array
    {
        // ex si 4 codes : ['nb_presents_INSA'=>'', 'nb_presents_UCN'=>'', 'nb_presents_ULHN'=>'', 'nb_presents_URN'=>'']
        $array = array_fill_keys(
            array_map(
                fn(string $code) => $this->generateColumnName($column, $code),
                $this->codes
            ),
            ''
        );

        if ($value === null) {
            return $array;
        }

        $parts = preg_split($this->outerPregSplitPattern, $value);
        Assert::notSame(false, $parts, "Le preg_split avec le pattern $this->outerPregSplitPattern a échoué sur la valeur suivante : " . $value);
        foreach ($parts as $part) {
            $split = preg_split($this->innerPregSplitPattern, $part);
            Assert::notSame(false, $split, "Le preg_split avec le pattern $this->innerPregSplitPattern a échoué sur la valeur suivante : " . $part);
            [$code, $val] = $split;
            Assert::inArray($code, $this->codes, "Valeur rencontrée ne correspondant à aucun code connu : %s");
            $col = $this->generateColumnName($column, $code);
            $array[$col] = $val;
        }

        return $array;
    }

    protected function generateColumnName(string $column, string $code): string
    {
        return $column. '_' . $code;
    }
}