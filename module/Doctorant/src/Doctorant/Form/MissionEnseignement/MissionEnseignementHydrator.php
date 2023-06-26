<?php

namespace Doctorant\Form\MissionEnseignement;

use Doctorant\Entity\Db\MissionEnseignement;
use Laminas\Hydrator\HydratorInterface;

class MissionEnseignementHydrator implements HydratorInterface {

    /** @var MissionEnseignement $object */
    public function extract(object $object): array
    {
        $data = [
            'annee_univ' => $object->getAnneeUniversitaire(),
        ];
        return $data;
    }

    /** @var MissionEnseignement $object */
    public function hydrate(array $data, object $object) : object
    {
        $anneeUniversitaire = $data['annee_univ'];

        $object->setAnneeUniversitaire($anneeUniversitaire);
        return $object;
    }


}