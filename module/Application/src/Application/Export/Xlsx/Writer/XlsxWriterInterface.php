<?php

namespace Application\Export\Xlsx\Writer;

interface XlsxWriterInterface
{
    /**
     * Spécifie le nom du fichier xlsx source.
     */
    public function setInputFilePath(string $inputFilePath): void;

    /**
     * Transmet les données brutes à écrire, par onglet.
     *
     * @param array $data Ex : 'tab1' => ['col1' => 'val1, 'col2' => 'val2', ...], 'tab2' => [...], ...
     */
    public function setData(array $data): void;

    /**
     * Ecrit les données courantes dans le fichier spécifié.
     */
    public function writeToFile(string $outpuFilePath): void;

    /**
     * Ecrit dans une chaîne de caractères.
     */
    public function writeToString(): string;
}