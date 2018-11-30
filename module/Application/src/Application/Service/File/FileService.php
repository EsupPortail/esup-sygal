<?php

namespace Application\Service\File;

use Application\Entity\Db\Fichier;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

class FileService
{
    /**
     * @var string
     */
    private $rootDirectoryPath;

    /**
     * @param string $rootDirectoryPath
     */
    public function setRootDirectoryPath($rootDirectoryPath)
    {
        $this->rootDirectoryPath = $rootDirectoryPath;
    }

    /**
     * @return string
     */
    public function getRootDirectoryPath()
    {
        return $this->rootDirectoryPath;
    }

    public function prependRootDirToPath($relativeFilepath)
    {
        return $this->rootDirectoryPath . '/' . $relativeFilepath;
    }

    /**
     * Création si besoin du dossier spécifié par son chemin absolu.
     *
     * @param string $absolutePath
     */
    public function createWritableDirectory($absolutePath)
    {
        $ok = $this->createWritableFolder($absolutePath, 0770);
        if (!$ok) {
            throw new RuntimeException("Le répertoire suivant n'a pas pu être créé sur le serveur : " . $absolutePath);
        }
    }

    /**
     * Create correctly writable folder.
     *
     * Check if folder exist and writable.
     * If not exist try to create it one writable.
     *
     * @param string $folder
     * @param int    $mode
     * @return bool true: folder has been created or exist and is writable.
     *              false: folder does not exist and cannot be created.
     */
    private function createWritableFolder($folder, $mode = 0700)
    {
        if($folder !== '.' && $folder !== '/' ) {
            $this->createWritableFolder(dirname($folder));
        }
        if (file_exists($folder)) {
            return is_writable($folder);
        }

        return mkdir($folder, $mode, true) && is_writable($folder);
    }

    /**
     * Génère un fichier PNG temporaire pour aperçu de la première page d'un fichier PDF,
     * et retourne son contenu binaire.
     *
     * @param string $inputFilePath
     * @return string Contenu binaire du fichier PNG généré
     * @throws LogicException Format de fichier incorrect
     */
    public function generateFirstPagePreview($inputFilePath)
    {
        if (mime_content_type($inputFilePath) !== Fichier::MIME_TYPE_PDF) {
            return \UnicaenApp\Util::createImageWithText("Erreur: Seul le format |de fichier PDF est accepté", 200, 100);
        }

        if (! extension_loaded('imagick')) {
            return \UnicaenApp\Util::createImageWithText("Erreur: extension PHP |'imagick' non chargée", 170, 100);
        }

        $outputFilePath = sys_get_temp_dir() . '/sygal_preview_' . uniqid() . '.png';

        try {
            $im = new \Imagick();
            $im->setResolution(300, 300);
            $im->readImage($inputFilePath . '[0]'); // 1ere page seulement
            $im->setImageFormat('png');
            $im->writeImage($outputFilePath);
            $im->clear();
            $im->destroy();
        } catch (\ImagickException $ie) {
            throw new RuntimeException(
                "Erreur rencontrée lors de la création de l'aperçu", null, $ie);
        }

        $content = file_get_contents($outputFilePath);

        return $content;
    }
}