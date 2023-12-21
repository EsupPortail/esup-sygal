<?php

namespace Soutenance\Form\AdresseSoutenance;

use JetBrains\PhpStorm\Pure;
use Laminas\Hydrator\HydratorInterface;
use Soutenance\Entity\Adresse;

class AdresseSoutenanceHydrator implements HydratorInterface
{

    #[Pure] public function extract(object $object): array
    {
        /** @var Adresse $object */
        $data = [
            'ligne1' => $object->getLigne1(),
            'ligne2' => $object->getLigne2(),
            'ligne3' => $object->getLigne3(),
            'ligne4' => $object->getLigne4(),
        ];
        return $data;
    }

    public function hydrate(array $data,object $object): object
    {
        /** @var Adresse $object */
        $ligne1 = (isset($data['ligne1']) && trim($data['ligne1']))?trim($data['ligne1']):null;
        $ligne2 = (isset($data['ligne2']) && trim($data['ligne2']))?trim($data['ligne2']):null;
        $ligne3 = (isset($data['ligne3']) && trim($data['ligne3']))?trim($data['ligne3']):null;
        $ligne4 = (isset($data['ligne4']) && trim($data['ligne4']))?trim($data['ligne4']):null;

        $object->setLigne1($ligne1);
        $object->setLigne2($ligne2);
        $object->setLigne3($ligne3);
        $object->setLigne4($ligne4);

        return $object;
    }


}