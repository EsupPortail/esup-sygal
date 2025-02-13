<?php

namespace Validation\Service\ValidationHDR;

use Application\Service\UserContextServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Validation\Entity\Db\ValidationHDR;
use Validation\Service\AbstractValidationEntityService;
use Validation\Service\ValidationServiceAwareTrait;

class ValidationHDRService extends AbstractValidationEntityService
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use IndividuServiceAwareTrait;

    protected string $validationEntityClass = ValidationHDR::class;
}