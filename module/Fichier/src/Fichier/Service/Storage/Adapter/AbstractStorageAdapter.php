<?php

namespace Fichier\Service\Storage\Adapter;

use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;

abstract class AbstractStorageAdapter implements StorageAdapterInterface
{
    /**
     * @var array Paramètres de configuration.
     */
    protected array $config;

    /**
     * @var string Séparateur pour les éléments de chemin.
     */
    protected string $pathSeparator;

    /**
     * @var string Emplacement racine.
     */
    protected string $rootPath;

    /**
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        if (empty($this->config[$k = 'root_path'])) {
            throw new StorageAdapterException(
                "Vous devez spécifier dans la config le chemin du répertoire de destination des fichiers (clé '$k').");
        }

        $this->rootPath = $this->config[$k];
    }

    /**
     * Calcule le chemin absolu de l'emplacement spécifié par ses éléments, en ajoutant devant l'emplacement racine.
     *
     * @param string ...$pathParts
     * @return string
     */
    public function computeDirectoryPath(string ...$pathParts): string
    {
        return $this->assemblePath($this->rootPath, ...$pathParts);
    }

    /**
     * Assemble les éléments de chemin spécifiés en utilisant le séparateur de chemin.
     *
     * @param string ...$pathParts
     * @return string
     */
    protected function assemblePath(string ...$pathParts): string
    {
        return implode($this->pathSeparator, $pathParts);
    }
}