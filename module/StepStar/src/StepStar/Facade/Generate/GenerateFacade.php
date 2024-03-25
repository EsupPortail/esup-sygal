<?php

namespace StepStar\Facade\Generate;

use Exception;
use Generator;
use Laminas\Stdlib\Glob;
use Psr\Log\LoggerAwareTrait;
use StepStar\Entity\Db\Log;
use StepStar\Exception\TefServiceException;
use StepStar\Exception\XmlServiceException;
use StepStar\Facade\TefFileNameParamsAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;
use StepStar\Service\Tef\TefServiceAwareTrait;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xsl\XslServiceAwareTrait;

class GenerateFacade
{
    use LogServiceAwareTrait;
    use XslServiceAwareTrait;
    use XmlServiceAwareTrait;
    use TefServiceAwareTrait;
    use TefFileNameParamsAwareTrait;
    use LoggerAwareTrait;

    const XML_DIR_NAME = 'xml';
    const TEF_DIR_NAME = 'tef';

    /**
     * Le *chemin absolu* du répertoire créé par le module et dans lequel sont générés le fichier
     *  XML intermédiaire (dans un sous répertoire dédié) et les fichiers TEF (idem).
     * @var string
     */
    private string $outputDirPath;

    /**
     * Le *chemin absolu* du répertoire créé par le module et dans lequel sont générés les fichiers TEF.
     * @var string
     */
    private string $tefOutputDirPath;

    /**
     * Répertoire destination dans lequel générer le fichier XML intermédiaire.
     * @var string
     */
    private string $xmlThesesOutputDirPath;

    private bool $success;

    private string $xslFilePath;

    /**
     * Spécifie le *préfixe du chemin* du répertoire qui sera créé par le module et dans lequel seront générés le fichier
     * XML intermédiaire (dans un sous répertoire dédié) et les fichiers TEF (idem).
     *
     * Exemple : '/tmp/sygal_stepstar_' donnera lieu à la création de répertoires comme ça :
     *   - /tmp/sygal_stepstar_660165360b1c3/xml : pour le fichier XML intermédiaire,
     *   - /tmp/sygal_stepstar_660165360b1c3/tef : pour les fichiers TEF.
     */
    public function setOutputDirPathPrefix(string $outputDirPathPrefix): void
    {
        $this->outputDirPath = uniqid($outputDirPathPrefix);
    }

    /**
     * Retourne le *chemin abslou* du répertoire créé par le module et dans lequel ont été générés le fichier
     * XML intermédiaire (dans un sous répertoire dédié) et les fichiers TEF (idem).
     */
    public function getOutputDirPath(): string
    {
        return $this->outputDirPath;
    }

    /**
     * Retourne le *chemin absolu* du répertoire créé par le module et dans lequel ont été générés les fichiers TEF.
     */
    public function getTefOutputDirPath(): string
    {
        return $this->tefOutputDirPath;
    }

    /**
     * Indique si la génération a été couronnée de succès.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Generation du fichier XML intermediaire (1 pour N theses) & des fichiers TEF (1 par these).
     * (Un Log unique est créé pour cette opération.)
     *
     * @param array $theses Thèses concernées
     * @param string $command Commande ayant déclenché la génération
     * @param string|null $tag Eventuel tag commun à l'ensemble des logs qui seront produits
     * @return \Generator
     */
    public function generateFilesForTheses(array $theses, string $command, ?string $tag = null): Generator
    {
        $this->xmlThesesOutputDirPath = $this->generateXmlThesesOutputDirPath();
        $this->tefOutputDirPath = $this->generateTefOutputDirPath();

        $operation = Log::OPERATION__GENERATION_XML;
        $this->newLog($operation, $command, $tag);
        $this->success = true;
        try {
            $this->genererXmlFileForThesesToDir($theses);
            $this->generateTefFilesFromDir();
        } catch (Exception $e) {
            $this->appendExceptionToLog($e);
            $this->success = false;
        }
        $this->log->setSuccess($this->success);
        yield $this->log;
    }

    private function generateXmlThesesOutputDirPath(): string
    {
        return $this->outputDirPath . '/' . self::XML_DIR_NAME;
    }

    private function generateTefOutputDirPath(): string
    {
        return $this->outputDirPath . '/' . self::TEF_DIR_NAME;
    }

    /**
     * @param array $theses Thèses à exporter au format XML.
     * @return string Chemin du fichier XML généré
     * @throws \Exception
     */
    private function genererXmlFileForThesesToDir(array $theses): string
    {
        if (!is_dir($this->xmlThesesOutputDirPath)) {
            $ok = mkdir($this->xmlThesesOutputDirPath, 0777, true);
            if (!$ok) {
                throw new Exception("Impossible de créer le répertoire '$this->xmlThesesOutputDirPath'.");
            }
        }

        $xmlFilePath = $this->xmlThesesOutputDirPath . '/' . uniqid('sygal_stepstar_xml_') . '.xml';

        $this->generateXmlFileForTheses($theses, $xmlFilePath);

        return $xmlFilePath;
    }

    /**
     * @param array $theses Thèses à exporter au format XML.
     * @param string $xmlFilePath Chemin du fichier XML à générer
     * @throws \Exception
     */
    private function generateXmlFileForTheses(array $theses, string $xmlFilePath): void
    {
        $theseIds = implode(',', array_map(fn($these) => $these['id'], $theses));

        $this->appendToLog(sprintf(
            "Generation du fichier XML intermediaire (1 seul pour toutes les theses) pour %d theses (%s) : ",
            count($theses), $theseIds
        ));

        try {
            $this->xmlService->setTheses($theses);
            $this->xmlService->exportThesesToXml($xmlFilePath);
        } catch (XmlServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation du fichier XML.", null, $e);
        }

        if ($rejectedTheses = $this->xmlService->getRejectedTheses()) {
            $this->appendToLog('  :-( Les theses suivantes ont ete ecartees car elles sont invalides :');
            foreach ($rejectedTheses as $array) {
                $this->appendToLog(sprintf('      - %s : %s', $array['these']['id'], $array['reason']));
            }
        }

        if ($exceptions = $this->xmlService->getExceptions()) {
            foreach ($exceptions as $exception) {
                $this->appendToLog('  :-( ' . $exception->getMessage());
            }
        } else {
            $this->appendToLog("> " . realpath($xmlFilePath));
        }
    }

    /**
     * @throws \Exception
     */
    private function generateTefFilesFromDir(): void
    {
        $this->appendToLog(sprintf(
            "Generation des fichiers TEF (1 par these) dans %s, a partir du repertoire %s :",
            $this->tefOutputDirPath, $this->xmlThesesOutputDirPath
        ));

        try {
            $this->xslFilePath = $this->xslService->generateXslFile($this->tefResultDocumentHref);
        } catch (TefServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation du fichier XSL.", null, $e);
        }

        $this->tefService->setOutputDir($this->tefOutputDirPath);
        try {
            $this->tefService->generateTefFilesFromDir($this->xmlThesesOutputDirPath, $this->xslFilePath);
        } catch (TefServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation des fichiers TEF.", null, $e);
        }

        $paths = $this->listTefFilesInDirectory($this->tefOutputDirPath);
        foreach ($paths as $path) {
            $this->appendToLog("> " . realpath($path));
        }
    }

    /**
     * @param string $dir
     * @return array
     */
    private function listTefFilesInDirectory(string $dir): array
    {
        return Glob::glob($dir . '/' . $this->listTefFilesInDirectoryGlobPattern, Glob::GLOB_BRACE);
    }
}