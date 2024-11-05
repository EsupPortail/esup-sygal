<?php

namespace Structure\Form\Hydrator;

use Application\SourceCodeStringHelper;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class VariableHydrator extends DoctrineObject
{
    public function hydrate(array $data, object $object): object
    {
        /** @var \Application\Entity\Db\Variable $object */
        parent::hydrate($data, $object);

        $sourceCodeHelper = new SourceCodeStringHelper();
        $sourceCode = $sourceCodeHelper->addPrefixTo($object->getCode(), $object->getEtablissement()->getSourceCode());
        $object->setSourceCode($sourceCode);

        return $object;
    }
}