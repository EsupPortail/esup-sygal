<?php

namespace These\Fieldset\Financement;

use Application\Entity\Db\Financement;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class FinancementHydrator extends DoctrineObject
{
    use EtablissementServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use SourceServiceAwareTrait;

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

    /**
     * @param array $data
     * @param object $object
     * @return Financement
     */
    public function hydrate(array $data, object $object): Financement
    {
        return parent::hydrate($data,$object);
    }
}