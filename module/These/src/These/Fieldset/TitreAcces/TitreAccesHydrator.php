<?php

namespace These\Fieldset\TitreAcces;

use Application\Entity\Db\TitreAcces;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class TitreAccesHydrator extends DoctrineObject //implements HydratorInterface
{
    use SourceServiceAwareTrait;

    /**
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var TitreAcces $object */
        $data = parent::extract($object);

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return TitreAcces
     */
    public function hydrate(array $data, object $object): TitreAcces
    {
        //a créer dans le service de l'entité
//        if($object->getId() === null){
//            $object->setSource($this->sourceService->fetchApplicationSource());
//            $object->setSourceCode($this->sourceService->genereateSourceCode());
//        }

        return parent::hydrate($data, $object);
    }
}