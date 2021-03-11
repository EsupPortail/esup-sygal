<?php

namespace Import\Exception;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Zend\Http\Response;

class CallException extends RuntimeException
{
    static public function unexpectedError($uri, \Exception $previous = null)
    {
        return new self("Erreur inattendue rencontrée lors de l'envoi de la requête au WS (URI '$uri')", null, $previous);
    }

    static public function badConfig(\Exception $previous = null)
    {
        return new self("La config fournie est incorrecte.", null, $previous);
    }

    static public function unexpectedResponse($uri, Response $response)
    {
        return new self("Mauvaise réponse du WS (URI '$uri') ! Status : " . $response->getStatusCode());
    }

    static public function invalidJSONResponse($uri, $responseBody)
    {
        return new self(sprintf(
            "Erreur rencontrée lors du décodage de la réponse JSON du WS (URI '%s') : %s. Début de la réponse reçue : %s",
            $uri, json_last_error_msg(), Util::truncatedString($responseBody, 100)
        ));
    }

    static public function clientError(ClientException $error)
    {
        return new self("Erreur ClientException rencontrée lors de l'envoi de la requête au WS", $error->getCode(), $error);
    }

    static public function serverError(ServerException $error)
    {
        $message = "Erreur distante rencontrée par le serveur du WS";
        $previous = $error;
        if ($error->hasResponse() && $error->getResponse()->getBody()->getSize() > 0) {
            $previous = new RuntimeException($error->getResponse()->getBody(), null, $error);
        }

        return new self($message, null, $previous);
    }

    static public function networkError(RequestException $error)
    {
        $message = "Erreur réseau rencontrée lors de l'envoi de la requête au WS";
        if ($uri = $error->getRequest()->getUri()) {
            $message .= " (" . $uri->getHost() . ")";
        }
        if ($error->hasResponse()) {
            $message .= " : " . Psr7\str($error->getResponse());
        }

        return new self($message, null, $error);
    }
}