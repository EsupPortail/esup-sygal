<?php

namespace These\Fieldset\Financement;

use Application\Entity\Db\Financement;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class FinancementHydrator extends DoctrineObject
{
    /**
     * @param object $object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var Financement $object */
        $data = parent::extract($object);

        $data["origineFinancement"] = $object->getOrigineFinancement()?->getId() ?? null;

        return $data;
    }
}