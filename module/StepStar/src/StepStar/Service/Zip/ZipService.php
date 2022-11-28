<?php


namespace StepStar\Service\Zip;


use These\Entity\Db\FichierThese;
use These\Entity\Db\These;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use StepStar\Exception\ZipServiceException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class ZipService
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;

    /**
     * Crée une archive ZIP contenant tous les fichiers associés à une thèse.
     *
     * @param These $these Thèse concernée
     * @return string Chemin du fichier .zip créé
     * @throws ZipServiceException
     */
    public function compresserFichiersForThese(These $these): string
    {
        $tmpDirPath = sys_get_temp_dir();

        /**
         * Création d'un répertoire temporaire contenant les fichiers à compresser.
         */
        $dirName = uniqid('sygal_');
        $dirPath = $tmpDirPath . '/' . $dirName;
        if (! mkdir($dirPath)) {
            throw new ZipServiceException("Impossible de créer le répertoire temporaire " . $dirPath);
        }
        /** @var FichierThese $fichierThese */
        foreach ($these->getFichierTheses() as $fichierThese) {
            $fichier = $fichierThese->getFichier();
            $filename = $fichier->getNom();
//            $srceFilepath = $this->fichierService->computeFilePathForFichier($fichier);
            try {
                $srceFilepath = $this->fileService->createFileCopyForFichier($fichier);
            } catch (StorageAdapterException $e) {
                throw new RuntimeException(
                    "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $fichier, null, $e);
            }
            $destFilepath = $dirPath . '/' . $filename;
            $success = copy($srceFilepath, $destFilepath);
            if (!$success) {
                throw new ZipServiceException("Echec de la copie du fichier $srceFilepath vers $destFilepath");
            }
        }

        /**
         * Compression du répertoire.
         */
        $zipFileName = $dirName . '.zip';
        $zipFilePath = $tmpDirPath . '/' . $zipFileName;
        Util::zip($dirPath, $zipFilePath);

        return $zipFilePath;
    }

    /**
     * @param string $zipFilePath
     * @param string|null $attachmentFilename
     */
    public function sendZipToClient(string $zipFilePath, string $attachmentFilename = null)
    {
        $attachmentFilename = $attachmentFilename ?: 'sygal_theses.zip';

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