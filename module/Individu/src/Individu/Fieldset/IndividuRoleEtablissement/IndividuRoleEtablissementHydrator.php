<?php

namespace Individu\Fieldset\IndividuRoleEtablissement;

use Individu\Entity\Db\IndividuRoleEtablissement;
use Laminas\Hydrator\HydratorInterface;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class IndividuRoleEtablissementHydrator implements HydratorInterface
{
    use EtablissementServiceAwareTrait;

    public function extract(object $object): array
    {
        /* @var IndividuRoleEtablissement $object */

        $data = [];

        if ($object->getEtablissement() !== null) {
            $data['etablissement'] = [
                'id' => $object->getEtablissement()->getId(),
                'label' => $object->getEtablissement()->getStructure()->getLibelle(),
            ];
        }

        return $data;
    }

    public function hydrate(array $data, object $object): IndividuRoleEtablissement
    {
        /* @var IndividuRoleEtablissement $object */

        if ($data['etablissement']['id'] ?? null) {
            $etablissement = $this->etablissementService->getRepository()->find($data['etablissement']['id']);
            $object->setEtablissement($etablissement);
        }

        return $object;
    }
}