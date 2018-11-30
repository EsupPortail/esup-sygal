<?php

namespace Application\Service\File;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\RuntimeException;

class FileService
{
    /**
     * @var string
     */
    private $rootDirectoryPathForUploadedFiles;

    /**
     * @var string
     */
    private $rootDirectoryPathForUploadedLogos;

    /**
     * @param string $rootDirectoryPathForUploadedFiles
     */
    public function setRootDirectoryPathForUploadedFiles(string $rootDirectoryPathForUploadedFiles)
    {
        $this->rootDirectoryPathForUploadedFiles = $rootDirectoryPathForUploadedFiles;
        $this->rootDirectoryPathForUploadedLogos = $rootDirectoryPathForUploadedFiles . '/' . $this->getLogosSubDirectoryRelativePath();
    }

    /**
     * Ajoute devant le chemin relatif spécifié le chemin du répertoire racine des logos de structures uploadés.
     *
     * @param string $relativeFilepath
     * @return string
     */
    public function prependLogosRootDirectoryToRelativePath($relativeFilepath)
    {
        return $this->rootDirectoryPathForUploadedLogos . '/' . $relativeFilepath;
    }

    /**
     * Ajoute devant le chemin relatif spécifié le chemin du répertoire racine de tous les fichiers uploadés.
     *
     * @param string $relativeFilepath
     * @return string
     */
    public function prependUploadRootDirToRelativePath($relativeFilepath)
    {
        return $this->rootDirectoryPathForUploadedFiles . '/' . $relativeFilepath;
    }

    /**
     * @return string
     */
    public function getLogosSubDirectoryRelativePath()
    {
        return 'ressources/Logos';
    }

    /**
     * @param StructureConcreteInterface $structure
     * @return string
     */
    public function computeLogoFilenameForStructure(StructureConcreteInterface $structure)
    {
        if ($structure instanceof EcoleDoctorale || $structure instanceof UniteRecherche) {
            if ($sigle = $structure->getSourceCode() . "-" . $structure->getSigle() . ".png") {
                return $sigle;
            } else {
                return uniqid() . ".png";
            }
        } elseif ($structure instanceof Etablissement) {
            return $structure->getStructure()->getCode() . ".png";
        } else {
            throw new RuntimeException("Structure spécifiée imprévue.");
        }
    }

    /**
     * @param StructureConcreteInterface $structure
     * @return string
     */
    public function computeLogoPathForStructure(StructureConcreteInterface $structure)
    {
        // sous-répertoire identifiant le type de structure
        if ($structure instanceof EcoleDoctorale) {
            $dir = 'ED';
        } elseif ($structure instanceof UniteRecherche) {
            $dir = 'UR';
        } elseif ($structure instanceof Etablissement) {
            $dir = 'Etab';
        } else {
            throw new RuntimeException("Structure spécifiée imprévue.");
        }

        $logoFilename = $this->computeLogoFilenameForStructure($structure);
        $filepath = $this->prependLogosRootDirectoryToRelativePath($dir . '/' . $logoFilename);

        return $filepath;
    }

    /**
     * @param UniteRecherche $unite
     * @return string
     */
    public function computeLogoAbsolutePathForUniteRecherche(UniteRecherche $unite)
    {
        $logoFilename = $this->computeLogoFilenameForStructure($unite);
        $filepath = $this->fileService->prependLogosRootDirToRelativePath('ED/' . $logoFilename);

        return $filepath;
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