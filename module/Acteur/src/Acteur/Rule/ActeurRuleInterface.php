<?php

namespace Acteur\Rule;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Application\Rule\RuleInterface;
use Laminas\Form\Fieldset;

interface ActeurRuleInterface extends RuleInterface
{
    public function setActeur(ActeurThese|ActeurHDR $acteur): void;

    public function execute(): void;

    public function prepareActeurFieldset(Fieldset $fieldset): void;

    public function prepareActeurHydratorData(array $data): array;

    public function prepareActeurInputFilterSpecification(array $spec): array;
}