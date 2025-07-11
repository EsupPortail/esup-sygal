<?php

namespace Application\Export\Xlsx;

use Application\Export\Xlsx\Writer\XlsxWriterInterface;
use BadMethodCallException;
use DateTime;
use Exception;
use InvalidArgumentException;
use UnicaenApp\Traits\MessageAwareTrait;
use Webmozart\Assert\Assert;

abstract class AbstractXlsxExport implements XlsxExportInterface
{
    use MessageAwareTrait;

    const SOURCE_TYPE_DB = 'db';
    const SOURCE_TYPE_SERVICE = 'service';

    const LOG_TAB = 'LOG';

    protected string $code;

    protected array $defaultConfig = [
        'input_file_path' => null,
        'data_tabs' => [],
    ];
    protected array $config;
    protected array $dataTabsConfig;

    protected array $context = [];
    protected ?string $inputFilePath = null;
    protected XlsxWriterInterface $writer;

    public function __construct()
    {
        $this->config = $this->defaultConfig;
    }

    public function setWriter(XlsxWriterInterface $writer): void
    {
        $this->writer = $writer;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge(['name' => $this->getCode()], $this->config, $config);

        $this->loadConfig();
    }

    protected function loadConfig(): void
    {
        $this->inputFilePath = $this->config['input_file_path'] ?? null;

        if ($this->inputFilePath !== null) {
            Assert::notEmpty($this->inputFilePath, "Aucun chemin spécifié pour le template");
            Assert::file($this->inputFilePath, "Fichier template introuvable : %s");
        }

        $this->dataTabsConfig = $this->config['data_tabs'];
    }

    public function setContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }

    public function exportToString(): string
    {
        $outputFilePath = $this->exportToTempFile();

        $content = file_get_contents($outputFilePath);
        unlink($outputFilePath);

        return $content;
    }

    public function exportToTempFile(): string
    {
        $outputFilePath = $this->computeTempOutputFilePath();

        $this->generate($outputFilePath);

        return $outputFilePath;
    }

    public function exportToFile(string $outputFilePath): void
    {
        $this->generate($outputFilePath);
    }

    public function exportToClient(string|null $filename = null): void
    {
        if ($filename === null) {
            $filename = basename($this->computeTempOutputFilePath());
        }

        // redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // .xlsx
        header(sprintf('Content-Disposition: attachment;filename="%s"', $filename));
        header('Cache-Control: max-age=0');

        $content = $this->exportToString();

        file_put_contents('php://output', $content);
    }

    protected function generate(string $outputFilePath): void
    {
        // data tabs
        $this->writer->setInputFilePath($this->inputFilePath);
        $data = [];
        foreach ($this->dataTabsConfig as $tabName => $tabConfig) {
            $data[$tabName] = $this->fetchDataForTab($tabName);
        }
        $this->writer->setData($data);
        $this->writer->writeToFile($outputFilePath);

        // log tab
        if ($messagess = $this->getMessages()) {
            $this->writer->setInputFilePath($outputFilePath);
            $logs = [];
            foreach ($messagess as $severity => $messages) {
                $logs[] = ['severity' => $severity, 'messages' => implode(PHP_EOL, $messages)];
            }
            $tabName = static::LOG_TAB;
            $this->writer->setData([$tabName => $logs]);
            try {
                $this->writer->writeToFile($outputFilePath);
            } catch (Exception $e) {
                // une exception est levée si aucun onglet "Log" n'existe : pas de log alors !
            }
        }
    }

    protected function fetchDataForTab(string $tabName): array
    {
        $data = match ($this->getSourceTypeForTab($tabName)) {
            self::SOURCE_TYPE_DB => $this->fetchDbDataForTab($tabName),
            self::SOURCE_TYPE_SERVICE => $this->fetchServiceDataForTab($tabName),
            default => throw new InvalidArgumentException("Type de source inattendu !"),
        };

        foreach ($this->getDataProcessorsForTab($tabName) as $processor) {
            $data = $processor->processData($data);
        }

        $this->applyColumnValueFiltersForTab($tabName, $data);

        return $data;
    }

    /**
     * Méthode chargée fournir les données à écrire dans l'onglet spécifié, à partir de la base de données.
     *
     * @return array Ex : ['col1' => 'val1', 'col2' => 'val2', ...]
     */
    protected function fetchDbDataForTab(string $tabName): array
    {
        throw new BadMethodCallException("Méthode non implémentée !");
    }

    /**
     * Méthode chargée fournir les données à écrire dans l'onglet spécifié, à partir d'un tableau de données PHP.
     *
     * @return array Ex : ['col1' => 'val1', 'col2' => 'val2', ...]
     */
    protected function fetchServiceDataForTab(string $tabName): array
    {
        throw new BadMethodCallException("Méthode non implémentée !");
    }


    protected function computeTempOutputFilePath(): string
    {
        $inputFileName = $this->inputFilePath ?
            basename($this->inputFilePath, '.xlsx') :
            uniqid('xlsx_export_', true);

        return
            sys_get_temp_dir() . '/' .
            $inputFileName . '_' . (new DateTime())->format('Ymd_His') .
            '.xlsx';
    }

    protected function getConfigForTab(string $tabName): array
    {
        $config = $this->dataTabsConfig[$tabName] ?? null;
        Assert::notNull($config, "Tabelau de config introuvable pour l'onglet $tabName");

        return $config;
    }

    protected function getSourceTypeForTab(string $tabName): string
    {
        $tabConfig = $this->getConfigForTab($tabName);
        Assert::keyExists($tabConfig, $k = 'source_type', "Clé %s introuvable dans le tableau de config de l'onglet $tabName");

        return $tabConfig[$k];
    }

    protected function getSourceForTab(string $tabName): string|object
    {
        $tabConfig = $this->getConfigForTab($tabName);
        Assert::keyExists($tabConfig, $k = 'source', "Clé %s introuvable dans le tableau de config de l'onglet $tabName");

        return $tabConfig[$k];
    }

    /**
     * @return array<\Application\Export\Xlsx\XlsxExportDataProcessorInterface>
     */
    protected function getDataProcessorsForTab(string $tabName): array
    {
        $tabConfig = $this->getConfigForTab($tabName);

        return $tabConfig['data_processors'] ?? [];
    }

    /**
     * @return array<\UnicaenDbImport\Filter\ColumnValue\ColumnValueFilterInterface>
     */
    protected function getColumnValueFiltersForTab(string $tabName): array
    {
        $tabConfig = $this->getConfigForTab($tabName);

        return $tabConfig['column_value_filters'] ?? [];
    }

    protected function getColumnsForTab(string $tabName): array
    {
        $tabConfig = $this->getConfigForTab($tabName);

        Assert::keyExists($tabConfig, $k = 'columns', "Clé %s introuvable dans le tableau de config de l'onglet $tabName");
        $viewColumns = $tabConfig[$k];

        if (is_string($viewColumns)) {
            // si les colonnes sont spécifiées via une chaîne de caractères, on transforme en tableau
            $viewColumns = str_replace(',', ' ', $viewColumns); // replacement des virgules éventuelles par des espaces
            $viewColumns = preg_split('/\s+/', $viewColumns); // split selon les espaces/tabulations
        }

        Assert::isArray($viewColumns, "Les colonnes pour l'onglet $tabName doivent être spécifié au format string ou array");

        return $viewColumns;
    }

    private function applyColumnValueFiltersForTab(string $tabName, array &$data): void
    {
        foreach ($data as &$datum) {
            foreach ($this->getColumnValueFiltersForTab($tabName) as $filter) {
                $datum = $filter->filter($datum);
            }
        }
    }
}