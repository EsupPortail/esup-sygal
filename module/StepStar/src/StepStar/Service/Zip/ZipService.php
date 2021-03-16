<?php

namespace StepStar\Service\Zip;

use Application\Entity\Db\These;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use StepStar\Exception\ZipServiceException;

class ZipService
{
    use TheseServiceAwareTrait;
    use FichierTheseServiceAwareTrait;

    /**
     * Crée une archive ZIP contenant tous les fichiers associés à une thèse.
     *
     * @param int $theseId
     * @return string Chemin du fichier .zip créé
     * @throws ZipServiceException
     */
    public function compresserFichiersThese(int $theseId): string
    {
        /** @var These $these */
        $these = $this->theseService->getRepository()->find($theseId);
        if ($these === null) {
            throw new ZipServiceException("Thèse introuvable avec cet id : $theseId.");
        }

        $fichierThesePdf = $this->fichierTheseService->getRepository()->fetchFichierThesePdfArchivable($these);
        if ($fichierThesePdf === null) {
            throw new ZipServiceException("La thèse spécifié n'a pas de fichier PDF archivable.");
        }

        $fichiersTheses = array_merge(
            [$fichierThesePdf],
            $this->fichierTheseService->getRepository()->fetchFichiersTheseNonPdfArchivables($these)
        );
        $fichierZip = $this->fichierTheseService->compresserFichiersTheses($fichiersTheses, 'stepstar_these.zip', false);

        return $fichierZip->getPath();

//        /**
//         * Création d'un répertoire temporaire contenant les fichiers à compresser.
//         */
//        $dirName = uniqid('sygal_');
//        $dirPath = $outputDir . '/' . $dirName;
//        if (! mkdir($dirPath)) {
//            throw new ZipServiceException("Impossible de créer le répertoire temporaire " . $dirPath);
//        }
//        /** @var FichierThese $fichierThese */
//        foreach ($these->getFichierTheses() as $fichierThese) {
//            $fichier = $fichierThese->getFichier();
//            $filename = $fichier->getNom();
//            $srceFilepath = $this->fichierService->computeDestinationFilePathForFichier($fichier);
//            $destFilepath = $dirPath . '/' . $filename;
//            $success = copy($srceFilepath, $destFilepath);
//            if (!$success) {
//                throw new ZipServiceException("Echec de la copie du fichier $srceFilepath vers $destFilepath");
//            }
//        }
//
//        /**
//         * Compression du répertoire.
//         */
//        $zipFileName = $dirName . '.zip';
//        $zipFilePath = $outputDir . '/' . $zipFileName;
//        Util::zip($dirPath, $zipFilePath);
//
//        return $zipFilePath;
    }

    /**
     * @param string $zipFilePath
     * @param string|null $attachmentFilename
     */
    public function sendZipToUserAgent(string $zipFilePath, string $attachmentFilename = null)
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