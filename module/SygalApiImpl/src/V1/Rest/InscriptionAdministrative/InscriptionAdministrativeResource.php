<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative;

use Exception;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use RuntimeException;
use SygalApiImpl\V1\Rest\InscriptionAdministrative\Process\InscriptionAdministrativeImportProcessAwareTrait;

class InscriptionAdministrativeResource extends \SygalApi\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeResource
{
    use InscriptionAdministrativeImportProcessAwareTrait;

    /**
     * Réception d'une Inscription Administrative (create <=> POST).
     *
     * Arrivée ici, les données sont valides (d'après la validation OpenAPI),
     * cf. {@see \SygalApi\Validator\OpenApi\OpenApiContentValidationManager}
     *
     * @param  \stdClass $data
     * @return ApiProblem|void
     */
    public function create($data)
    {
        $this->logger->debug(json_encode($data, JSON_PRETTY_PRINT));

        try {
            $this->inscriptionAdministrativeImportProcess->import($data);
        } catch (Exception $e) {
            $exception = new RuntimeException(
                "Une erreur est survenue lors du traitement d'une inscription administrative : " . $e->getMessage(), null, $e
            );
            error_log($exception);

            return new ApiProblem(500, $exception);
        }
    }
}
