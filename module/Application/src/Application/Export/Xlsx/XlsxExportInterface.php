<?php

namespace Application\Export\Xlsx;

interface XlsxExportInterface
{
    /**
     * Retourne le nom de code unique de cet export.
     */
    public function getCode(): string;

    /**
     * Retourne la description HTML de cet export.
     */
    public function getHtmlDescription(): string;

    /**
     * Transmet le tableau de config de l'export.
     */
    public function setConfig(array $config): void;

    /**
     * Retourne le tableau de config courant de cet export.
     */
    public function getConfig(): array;

    /**
     * Transmet un contexte (i.e. données utiles) à cet export.
     */
    public function setContext(array $context): void;

    /**
     * Génère l'export et renvoie le contenu du fichier résultant.
     */
    public function exportToString(): string;

    /**
     * Génère l'export dans un fichier temporaire quelconque.
     */
    public function exportToTempFile(): string;

    /**
     * Génère l'export dans le fichier spécifié.
     */
    public function exportToFile(string $outputFilePath): void;

    /**
     * Génère l'export et renvoie au navigateur le contenu du fichier résultant.
     */
    public function exportToClient(string|null $filename = null): void;
}