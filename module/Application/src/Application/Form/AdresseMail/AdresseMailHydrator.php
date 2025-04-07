<?php

namespace Application\Form\AdresseMail;

use Laminas\Hydrator\HydratorInterface;

class AdresseMailHydrator implements HydratorInterface {

    public function extract($object): array
    {
        return [
            'email' => $object->getEmail(),
        ];
    }

    public function hydrate(array $data, $object)
    {
        $email = (isset($data['email']) AND trim($data['email']) !== "")?trim($data['email']):null;
        $object->setEmail($email);
        return $object;
    }


}