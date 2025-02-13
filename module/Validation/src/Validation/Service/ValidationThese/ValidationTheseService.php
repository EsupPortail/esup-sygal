<?php

namespace Validation\Service\ValidationThese;

use Application\Service\UserContextServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Validation\Entity\Db\ValidationThese;
use Validation\Service\AbstractValidationEntityService;
use Validation\Service\ValidationServiceAwareTrait;

class ValidationTheseService extends AbstractValidationEntityService
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use IndividuServiceAwareTrait;

    protected string $validationEntityClass = ValidationThese::class;
}