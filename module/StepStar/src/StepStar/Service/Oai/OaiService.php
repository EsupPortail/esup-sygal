<?php

namespace StepStar\Service\Oai;

use Laminas\Config\Writer\PhpArray;

class OaiService
{
    private string $siseOaiSetXmlFilePath;
    private string $oaiSetsXmlFilePath;

    /**
     * @param string $siseOaiSetXmlFilePath
     */
    public function setSiseOaiSetXmlFilePath(string $siseOaiSetXmlFilePath): void
    {
        $this->siseOaiSetXmlFilePath = $siseOaiSetXmlFilePath;
    }

    /**
     * @param string $oaiSetsXmlFilePath
     */
    public function setOaiSetsXmlFilePath(string $oaiSetsXmlFilePath): void
    {
        $this->oaiSetsXmlFilePath = $oaiSetsXmlFilePath;
    }

    /**
     * @return string
     */
    public function getSiseOaiSetXmlFilePath(): string
    {
        return $this->siseOaiSetXmlFilePath;
    }

    /**
     * @return string
     */
    public function getOaiSetsXmlFilePath(): string
    {
        return $this->oaiSetsXmlFilePath;
    }

    /**
     * Génère le fichier de config à partir du fichier XML 'siseOaiSet.xml'
     * (le chemin de ce dernier est spécifié en config).
     *
     * @param string $outputDirPath Chemin vers le répertoire de destination
     * @return string Chemin du fichier généré
     */
    public function generateConfigFileFromSiseOaiSetXmlFile(string $outputDirPath): string
    {
        $configFilePath = $outputDirPath . '/sise_oai_set.config.php';

        $array = $this->loadSiseOaiSetXmlFileToArray($this->siseOaiSetXmlFilePath);

        $writer = new PhpArray();
        $writer->setUseBracketArraySyntax(true)->toFile($configFilePath, [
            'step_star' => [
                'oai' => [
                    'sise_oai_set' => $array,
                ],
            ],
        ]);

        return $configFilePath;
    }

    /**
     * Génère le fichier de config à partir du fichier XML 'oai_sets.config.php'
     * (le chemin de ce dernier est spécifié en config).
     *
     * @param string $outputDirPath Chemin vers le répertoire de destination
     * @return string Chemin du fichier généré
     */
    public function generateConfigFileFromOaiSetsXmlFile(string $outputDirPath): string
    {
        $configFilePath = $outputDirPath . '/oai_sets.config.php';

        $array = $this->loadOaiSetsXmlFileToArray($this->oaiSetsXmlFilePath);

        $writer = new PhpArray();
        $writer->setUseBracketArraySyntax(true)->toFile($configFilePath, [
            'step_star' => [
                'oai' => [
                    'oai_sets' => $array,
                ],
            ],
        ]);

        return $configFilePath;
    }

    /**
     * @param string $xmlFilePath
     * @return array[] ['codeSise' => ['codeOaiSet', 'codeOaiSet', ...]]
     */
    private function loadSiseOaiSetXmlFileToArray(string $xmlFilePath): array
    {
        $array = $this->loadXmlToArray($xmlFilePath);

        if (!isset($array[$k = 'elem'])) {
            throw new \InvalidArgumentException("Clé '$k' introuvable");
        }
        $array = $array[$k];

        $result = [];
        foreach ($array as $row) {
            $result[$row['sise']] = (array) $row['oaiset'];
        }

        return $result;
    }

    private function loadOaiSetsXmlFileToArray(string $xmlFilePath): array
    {
        $array = $this->loadXmlToArray($xmlFilePath);

        if (!isset($array[$k = 'set'])) {
            throw new \InvalidArgumentException("Clé '$k' introuvable");
        }
        $array = $array[$k];

        $result = [];
        foreach ($array as $row) {
            $result[$row['setSpec']] = $row['setName'];
        }

        return $result;
    }

    private function loadXmlToArray(string $xmlFilePath): array
    {
        $xml = simplexml_load_file($xmlFilePath, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);

        return json_decode($json, TRUE);
    }
}