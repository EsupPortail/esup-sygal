<?php

namespace StepStar\Service\Api;

use DOMDocument;
use DOMXPath;
use SoapFault;
use StepStar\Exception\ApiServiceException;
use StepStar\Service\Soap\SoapClientAwareTrait;
use StepStar\Service\Xslt\XsltServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class ApiService
{
    use SoapClientAwareTrait;
    use XsltServiceAwareTrait;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param array $params
     * @return self
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     *
     * @param string $tefFilePath
     * @param string|null $zipFilePath
     * @return string
     * @throws ApiServiceException
     */
    public function deposer(string $tefFilePath, string $zipFilePath = null): string
    {
        if (!file_exists($tefFilePath)) {
            throw new ApiServiceException("Le fichier $tefFilePath n'existe pas.");
        }
        if ($zipFilePath && !file_exists($zipFilePath)) {
            throw new ApiServiceException("Le fichier $zipFilePath n'existe pas.");
        }
        if (!isset($this->params[$k = 'idEtablissement'])) {
            throw new ApiServiceException("La requête 'deposer' nécessite le paramètre '$k'.");
        }

        $operation = 'deposer';
        $params = $this->params;
        $params['tef'] = base64_encode(file_get_contents($tefFilePath));
//        $params['tef'] = file_get_contents('/tmp/tef_base64.txt');
//        $params['tef'] = file_get_contents('/tmp/tef_base64_dumontier.txt');
        if ($zipFilePath !== null) {
            $operation = 'deposerAvecZip';
            $params['zip'] = base64_encode(file_get_contents($zipFilePath));
        }
        try {
            $response = $this->soapClient->call($operation, [$params]); // NB : tableau de paramètres DANS UN TABLEAU
        } catch (SoapFault $e) {
            throw new ApiServiceException("Erreur rencontrée lors de la requête '$operation'.", null, $e);
        }

        if ($error = $this->detectErrorInResponse($response)) {
            throw (new ApiServiceException("La réponse du web service signale l'erreur suivante : " . trim($error)))
                ->setResponse($response);
        }

        return $response;
    }

    /**
     * @param string $response
     * @return string|null
     */
    public function detectErrorInResponse(string $response): ?string
    {
        $result = new DOMDocument();
        $result->loadXML($response);
        $xpath = new DOMXPath($result);
        $xpath->registerNamespace("svrl", "http://purl.oclc.org/dsdl/svrl");
        $resultValue = $xpath->query("/svrl:schematron-output/svrl:failed-assert/svrl:text");

        if (!$resultValue || $resultValue->count() === 0) {
            return null;
        }

        return $resultValue->item(0)->nodeValue;
    }
}