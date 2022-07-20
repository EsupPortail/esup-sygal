<?php

namespace Fichier\Command;

use Application\Command\ShellCommandResult;
use Fichier\Command\Exception\TestArchivabiliteCommandException;
use Fichier\Validator\Exception\CinesErrorException;
use DOMDocument;
use UnicaenApp\Exception\RuntimeException;

class TestArchivabiliteShellCommandResult extends ShellCommandResult
{
    const XML_TAG_VALIDATOR = 'validator';

    const XML_TAG_VALID = 'valid';
    const XML_TAG_WELLFORMED = 'wellFormed';
    const XML_TAG_ARCHIVABLE = 'archivable';
    const XML_TAG_MESSAGE = 'message';
    const XML_TAG_SIZE = 'size';
    const XML_TAG_MD5SUM = 'md5sum';
    const XML_TAG_SHA256SUM = 'sha256sum';
    const XML_TAG_FORMAT = 'format';
    const XML_TAG_VERSION = 'version';

    /**
     * @var string XML retourné par le web service.
     */
    protected $xml;

    /**
     * @param array $output
     * @param int $returnCode
     */
    public function __construct(array $output, int $returnCode)
    {
        parent::__construct($output, $returnCode);

        try {
            $this->detectErrorFromCommandExecutionResults();
        } catch (TestArchivabiliteCommandException $cee) {
            throw new RuntimeException(sprintf("La ligne de commande 'XXXX' a échoué. %s", $cee->getMessage()));
        }

        $this->xml = trim(implode(PHP_EOL, $this->output));

        if (!$this->xml) {
            throw new CinesErrorException(
                "Impossible de valider le fichier car le service de validation n'a retourné aucun résultat.");
        }

        if ($this->detectErrorFromXml()) {
            throw new CinesErrorException(
                "Impossible de valider le fichier car le service de validation a semble-t-il rencontré un problème.");
        }
    }

    /**
     * Retourne le résultat de la validation.
     *
     * @return string
     */
    public function getResult(): string
    {
        return $this->xml;
    }

    /**
     * @return array
     */
    public function getArrayResult(): array
    {
        if (!trim($this->xml)) {
            return [];
        }

        // suppression des sauts de ligne sinon loadXML() démissionne au premier rencontré
        $xml = str_replace(PHP_EOL, '', $this->xml);

        try {
            $dom = $this->loadXML($xml);
        } catch (\DOMException $e) {
            throw new RuntimeException("Erreur rencontrée lors du chargement du XML suivant: " . $this->xml, null, $e);
        }

        return [
            $name = self::XML_TAG_WELLFORMED => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_VALID => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_ARCHIVABLE => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_MESSAGE => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_MD5SUM => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_SHA256SUM => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_SIZE => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_FORMAT => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_VERSION => $this->extractStringFromDom($name, $dom),
        ];
    }

    /**
     * @param $xml
     * @return DOMDocument
     * @throws \DOMException
     */
    private function loadXML($xml): DOMDocument
    {
        set_error_handler(function ($errno, $errstr/*, $errfile, $errline*/) {
            if ($errno === E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
                throw new \DOMException($errstr);
            }
            return false;
        });

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        restore_error_handler();

        return $dom;
    }

    /**
     * @return bool
     */
    private function detectErrorFromXml(): bool
    {
        // suppression des sauts de ligne sinon loadXML() démissionne au premier rencontré
        $xml = str_replace(PHP_EOL, '', $this->xml);

        try {
            $dom = $this->loadXML($xml);
        } catch (\DOMException $e) {
            throw new RuntimeException("Erreur rencontrée lors du chargement du XML suivant: " . $this->xml, null, $e);
        }

        if ($dom->getElementsByTagName(self::XML_TAG_VALIDATOR)->length === 0) {
            return true;
        }

        return false;
    }

    private function extractBooleanFromDom($name, DOMDocument $dom): bool
    {
        $value = $dom->getElementsByTagName($name)->item(0)->nodeValue;

        return mb_strtolower($value) === 'true';
    }

    private function extractStringFromDom($name, DOMDocument $dom): ?string
    {
        if (!($element = $dom->getElementsByTagName($name))) {
            return null;
        }
        if (!($item = $element->item(0))) {
            return null;
        }

        return $item->nodeValue;
    }

    private function detectErrorFromCommandExecutionResults()
    {
        if ($this->returnCode !== 0) {
            if ($this->returnCode === CURLE_OPERATION_TIMEOUTED) {
                // curl: (28) Operation timed out after 5001 milliseconds with 0 bytes received
                throw TestArchivabiliteCommandException::operationTimedout();
            }
            if ($this->returnCode === CURLE_GOT_NOTHING) {
                throw TestArchivabiliteCommandException::gotNothing();
            }
            throw TestArchivabiliteCommandException::unknown();
        }

        if (!is_array($this->output) || !$this->output) {
            throw TestArchivabiliteCommandException::emptyResult();
        }
    }
}