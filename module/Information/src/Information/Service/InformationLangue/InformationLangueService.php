<?php

namespace Information\Service\InformationLangue;

use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Information\Entity\Db\InformationLangue;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Webmozart\Assert\Assert;

class InformationLangueService extends BaseService
{
    use EntityManagerAwareTrait;

    public function getRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(InformationLangue::class);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('langue');
    }

    public function getLangueParDefaut(): InformationLangue
    {
        $lanque = $this->getRepository()->find(InformationLangue::ID_LANGUE_PAR_DEFAUT);
        Assert::notNull($lanque, "Anomalie : aucune langue par défaut n'a été trouvée !");

        return $lanque;
    }

    /**
     * @param string $id
     * @return InformationLangue
     */
    public function getLangue(string $id) : InformationLangue
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('langue.id = :id')
            ->setParameter('id',$id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs InformationLangue partagent le même id [".$id."]");
        }
        return $result;
    }

    /**
     * @return InformationLangue[]
     */
    public function getLangues() : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('langue.libelle', 'ASC');
        return $qb->getQuery()->getResult();
    }
}