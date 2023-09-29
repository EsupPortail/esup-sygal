<?php

namespace Formation\Form\Module;

use Formation\Entity\Db\Module;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class ModuleHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function extract(object $object) : array
    {
        /** @var Module $object */
        $data = [
            'libelle' => $object->getLibelle(),
            'description' => $object->getDescription(),
            'lien' => $object->getLien(),
            'mission_enseignement' => $object->isRequireMissionEnseignement()?"1":null,
        ];
        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        /** @var Module $object */
        $libelle = (isset($data['libelle']) AND trim($data['libelle']) !== '')?trim($data['libelle']):null;
        $description = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;
        $lien = (isset($data['lien']) AND trim($data['lien']) !== '')?trim($data['lien']):null;
        $mission = (isset($data['mission_enseignement']) AND $data['mission_enseignement'] === "1")?true:false;

        $object->setLibelle($libelle);
        $object->setDescription($description);
        $object->setLien($lien);
        $object->setRequireMissionEnseignement($mission);
        return $object;
    }


}