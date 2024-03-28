<?php

namespace Individu\Form\IndividuCompl;

use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuCompl;
use Laminas\Hydrator\HydratorInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndividuComplHydrator implements HydratorInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param IndividuCompl $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data  = [
            'individu' => [
                'id' => ($object->getIndividu())?$object->getIndividu()->getId():null,
                'label' => ($object->getIndividu())?$object->getIndividu()->getNomComplet():null,
            ],
            'email' => ($object->getEmailPro())?:null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param IndividuCompl $object
     * @return IndividuCompl
     */
    public function hydrate(array $data, object $object)
    {
        /** @var Individu|null $individu */
        $individuId = (isset($data['individu']) AND isset($data['individu']['id']))?$data['individu']['id']:null;
        $individu = ($individuId)?$this->getEntityManager()->getRepository(Individu::class)->find($individuId):null;
        /** @var string $email */
        $email = (isset($data['email']) AND trim($data['email']) !== '')?trim($data['email']):null;

        if ($individu) $object->setIndividu($individu);
        if ($email) $object->setEmailPro($email);
        return $object;
    }
}