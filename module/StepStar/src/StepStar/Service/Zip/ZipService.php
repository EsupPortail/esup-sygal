<?php

namespace StepStar\Service\Zip;

use StepStar\Exception\ZipServiceException;
use UnicaenApp\Util;

class ZipService
{
    /**
     * Crée une archive ZIP contenant les fichiers spécifiés.
     *
     * @param string[] $inputFilePaths Chemins des fichiers à inclure
     * @return string Chemin du fichier .zip créé
     * @throws ZipServiceException
     */
    public function compresserFichiersForThese(array $inputFilePaths): string
    {
        $tmpDirPath = sys_get_temp_dir();

        // Création d'un répertoire temporaire contenant les fichiers à compresser.
        $dirName = uniqid('sygal_');
        $dirPath = $tmpDirPath . '/' . $dirName;
        if (! mkdir($dirPath)) {
            throw new ZipServiceException("Impossible de créer le répertoire temporaire " . $dirPath);
        }

        foreach ($inputFilePaths as $inputFilePath) {
            $filename = basename($inputFilePath);
            $destFilePath = $dirPath . '/' . $filename;
            $success = copy($inputFilePath, $destFilePath);
            if (!$success) {
                throw new ZipServiceException("Echec de la copie du fichier $inputFilePath vers $destFilePath");
            }
        }

        // Compression du répertoire.
        $zipFileName = $dirName . '.zip';
        $zipFilePath = $tmpDirPath . '/' . $zipFileName;
        Util::zip($dirPath, $zipFilePath);

        return $zipFilePath;
    }

    /**
     * @param string $zipFilePath
     * @param string|null $attachmentFilename
     */
    public function sendZipToClient(string $zipFilePath, string $attachmentFilename = null): void
    {
        $attachmentFilename = $attachmentFilename ?: basename($zipFilePath);

        header("Pragma: public");
        header("Expires: 0");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=$attachmentFilename");
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: " . filesize($zipFilePath));
        ob_end_flush();
        readfile($zipFilePath);
        unlink($zipFilePath);
        exit;
    }
}