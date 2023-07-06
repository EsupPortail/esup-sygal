<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative;

use Exception;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use RuntimeException;
use SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade\ImportFacadeAwareTrait;

class InscriptionAdministrativeResource extends \SygalApi\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeResource
{
    use ImportFacadeAwareTrait;

    /**
     * Create a resource
     *
     * @param  \stdClass $data
     * @return ApiProblem|void
     */
    public function create($data)
    {
        $this->logger->debug(json_encode($data, JSON_PRETTY_PRINT));

        try {
            $this->importFacade->import($data);
        } catch (Exception $e) {
            error_log($e);
            return new ApiProblem(500, new RuntimeException(
                "Une erreur est survenue lors de l'import de l'inscription administrative : " . $e->getMessage(), null, $e
            ));
        }
    }
}
