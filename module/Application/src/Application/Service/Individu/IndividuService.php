<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Individu;
use Application\Service\BaseService;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use UnicaenImport\Entity\Db\Source;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Individu::class);
    }

    /**
     * @param People $people
     * @return Individu
     */
    public function createFromPeople(People $people)
    {
        $sns = (array)$people->get('sn');

        $entity = new Individu();
        $entity->setNomUsuel(array_pop($sns));
        $entity->setPrenom($people->get('givenName'));
        $entity->setCivilite($people->get('supannCivilite'));
        $entity->setEmail($people->get('mail'));
        $entity->setTel($people->get('telephoneNumber'));

        /** @var Source $source */
        $source = $this->getEntityManager()->getRepository(Source::class)->findOneBy(['code' => 'App']);
        $entity->setSource($source);
        $entity->setSourceCode($people->get('supannEmpId'));

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $entity;
    }
}