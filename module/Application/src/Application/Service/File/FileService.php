<?php

namespace Application\Service\File;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\StructureInterface;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Structure;
use UnicaenApp\Exception\RuntimeException;

class FileService
{
    const DIR_ETAB = 'Etab';
    const DIR_ED = 'ED';
    const DIR_UR = 'UR';

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
        $this->rootDirectoryPathForUploadedLogos = $rootDirectoryPathForUploadedFiles . '/ressources/Logos';
    }

    /**
     * Ajoute devant le chemin relatif spécifié le chemin du répertoire racine de tous les fichiers uploadés.
     *
     * @param string $relativeFilePath
     * @return string
     */
    public function prependUploadRootDirToRelativePath($relativeFilePath)
    {
        return $this->rootDirectoryPathForUploadedFiles . '/' . $relativeFilePath;
    }

    /**
     * Retourne
     *
     * @param StructureInterface $structure
     * @return string
     */
    public function computeLogoFileNameForStructure(StructureInterface $structure)
    {
        if ($structure instanceof Etablissement) {
            $name = $structure->getCode() ?: $structure->generateUniqCode();
        } else {
            $name = $structure->getSourceCode() . "-" . $structure->getSigle();
        }

        return $name . ".png";
    }

    /**
     * Retourne le chemin absolu vers le répertoire de stockage du logo de la structure spécifiée.
     *
     * @param StructureInterface $structure
     * @return string
     */
    public function computeLogoDirectoryPathForStructure(StructureInterface $structure)
    {
        $dir = null;

        // sous-répertoire identifiant le type de structure
        if ($structure instanceof EcoleDoctorale) {
            $dir = self::DIR_ED;
        } elseif ($structure instanceof UniteRecherche) {
            $dir = self::DIR_UR;
        } elseif ($structure instanceof Etablissement) {
            $dir = self::DIR_ETAB;
        } elseif ($structure instanceof Structure) {
            if ($structure->getTypeStructure()->isEtablissement()) {
                $dir = self::DIR_ETAB;
            } elseif ($structure->getTypeStructure()->isEcoleDoctorale()) {
                $dir = self::DIR_ED;
            } elseif ($structure->getTypeStructure()->isUniteRecherche()) {
                $dir = self::DIR_UR;
            }
        }
        if ($dir === null) {
            throw new RuntimeException("Structure spécifiée imprévue.");
        }

        $path = $this->rootDirectoryPathForUploadedLogos . '/' . $dir;

        return $path;
    }

    /**
     * @param StructureInterface $structure
     * @return string
     */
    public function computeLogoFilePathForStructure(StructureInterface $structure)
    {
        $logoDir = $this->computeLogoDirectoryPathForStructure($structure);
        $logoFileName = $structure->getCheminLogo();

        return $logoDir . '/' . $logoFileName;
    }

    /**
     * Création *si besoin* du dossier spécifié par son chemin absolu.
     *
     * @param string $absolutePath
     */
    public function createWritableDirectory($absolutePath)
    {
        $ok = $this->createWritableFolder($absolutePath, 0770);
        if (!$ok) {
            throw new RuntimeException("Le répertoire suivant n'a pas pu être créé : " . $absolutePath);
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
     *
     * @codeCoverageIgnore
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

    public function computeDirectoryPathForInformation() {
        $path = $this->rootDirectoryPathForUploadedFiles . '/' . 'information';
        return $path;
    }

}