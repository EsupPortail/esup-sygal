<?php

namespace Application\Service\File;

use UnicaenApp\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FileService
     */
    public function __invoke(ContainerInterface $container)
    {
        $service = new FileService();

        $service->setRootDirectoryPath($this->getRootDirectoryPath($container));

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return string
     */
    private function getRootDirectoryPath(ContainerInterface $container)
    {
        /** @var array $config */
        $config = $container->get('config');

        if (empty($config['fichier']['root_dir_path'])) {
            throw new RuntimeException(
                "Vous devez spécifier dans la config le chemin du répertoire de destination des fichiers (clé fichier.root_dir_path).");
        }

        $path = $config['fichier']['root_dir_path'];

        if (! is_readable($path)) {
            throw new RuntimeException(
                "Le chemin du répertoire de destination des fichiers doit exister et être accessible : " . $path);
        }

        return $path;
    }
}
