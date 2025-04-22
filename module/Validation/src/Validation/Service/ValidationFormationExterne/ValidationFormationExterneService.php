<?php

namespace Validation\Service\ValidationFormationExterne;

use Application\Service\UserContextServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Validation\Entity\Db\ValidationFormationExterne;
use Validation\Service\AbstractValidationEntityService;
use Validation\Service\ValidationServiceAwareTrait;

class ValidationFormationExterneService extends AbstractValidationEntityService
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use IndividuServiceAwareTrait;

    protected string $validationEntityClass = ValidationFormationExterne::class;
}