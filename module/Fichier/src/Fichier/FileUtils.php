<?php

namespace Fichier;

use Application\Command\ShellCommandRunner;
use Fichier\Command\ConvertShellCommand;
use Laminas\Http\Response;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class FileUtils
{
    const MIME_TYPE_PDF = 'application/pdf';

    /**
     * Génère un fichier PNG temporaire pour aperçu de la première page d'un fichier PDF,
     * et retourne son contenu binaire.
     *
     * @param string $inputFilePath
     * @return string Contenu binaire du fichier PNG généré
     */
    static public function generateFirstPagePreviewPngImageFromPdf(string $inputFilePath): string
    {
        if (mime_content_type($inputFilePath) !== self::MIME_TYPE_PDF) {
            return Util::createImageWithText("Erreur: Seul le format |de fichier PDF est accepté", 200, 100);
        }

        if (! extension_loaded('imagick')) {
            return Util::createImageWithText("Erreur: extension PHP |'imagick' non chargée", 170, 100);
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

        return file_get_contents($outputFilePath);
    }

    /**
     * @param Response $response
     * @param string $fileContent
     * @param string $mimeType
     * @param int|null $cacheMaxAge En secondes, ex: 60*60*24 = 86400 s = 1 jour
     * @return Response
     */
    static public function createResponseForFileContent(Response $response, string $fileContent, string $mimeType, ?int $cacheMaxAge = null): Response
    {
        $response->setContent($fileContent);

        $headers = $response->getHeaders();
        $headers
            ->addHeaderLine('Content-Transfer-Encoding', "binary")
            ->addHeaderLine('Content-Type', $mimeType)
            ->addHeaderLine('Content-length', strlen($fileContent));

        if ($cacheMaxAge === null) {
            $headers
                ->addHeaderLine('Cache-Control', "no-cache, no-store, must-revalidate")
                ->addHeaderLine('Pragma', 'no-cache');
        }
        else {
            // autorisation de la mise en cache de l'image par le client
            $headers
                ->addHeaderLine('Cache-Control', "private, max-age=$cacheMaxAge")
                ->addHeaderLine('Pragma', 'private')// tout sauf 'no-cache'
                ->addHeaderLine('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheMaxAge));
        }

        return $response;
    }

    /**
     * Crée la réponse permettant au client de télécharger un fichier quelconque.
     *
     * @param string $filepath Chemin vers le fichier à envoyer au client
     */
    static public function downloadFile(string $filepath)
    {
        $content = file_get_contents($filepath);
        $contentType = mime_content_type($filepath) ?: 'application/octet-stream';

        static::downloadFileFromContent($content, $filepath, $contentType);
    }

    /**
     * Crée la réponse permettant au client de télécharger un fichier dont on fournit le contenu brut.
     *
     * @param string $content Contenu brut à envoyer au client sous la forme d'un fichier
     * @param string $filename Nom proposé au client sous lequel enregistrer le fichier
     * @param string $contentType Type MIME du fichier retourné au client, 'text/plain' par défaut
     */
    static public function downloadFileFromContent(string $content, string $filename, string $contentType = 'text/plain')
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $content;
        exit;
    }

    static public function convertLogoFileToPNG(string $uploadedFilePath): string
    {
        $logoFilepath = tempnam(sys_get_temp_dir(), '') . '.png';

        $command = new ConvertShellCommand();
        $command->setOutputFilePath($logoFilepath);
        $command->setInputFilePath($uploadedFilePath);
        $command->generateCommandLine();

        $runner = new ShellCommandRunner();
        $runner->setCommand($command);
        try {
            $result = $runner->runCommand();

            if (!$result->isSuccessfull()) {
                $message = sprintf("La commande '%s' a échoué (code retour = %s). ",
                    $command->getName(),
                    $result->getReturnCode()
                );
                if ($output = $result->getOutput()) {
                    $message .= "Voici le log d'exécution : " . implode(PHP_EOL, $output);
                }
                throw new RuntimeException($message);
            }
        } catch (RuntimeException $rte) {
            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande " . $command->getName(),
                0,
                $rte);
        }

        return $logoFilepath;
    }
}