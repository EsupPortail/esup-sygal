<?php

namespace Soutenance\Service\Adresse;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Laminas\Mvc\Controller\AbstractActionController;
use RuntimeException;
use Soutenance\Entity\Adresse;
use Soutenance\Entity\Proposition;

class AdresseService {
    use ProvidesObjectManager;

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(Adresse $adresse): Adresse
    {
        $this->getObjectManager()->persist($adresse);
        $this->getObjectManager()->flush($adresse);
        return $adresse;
    }

    public function update(Adresse $adresse): Adresse
    {
        $this->getObjectManager()->flush($adresse);
        return $adresse;
    }

    public function historise(Adresse $adresse): Adresse
    {
        $adresse->historiser();
        $this->getObjectManager()->flush($adresse);
        return $adresse;
    }

    public function restore(Adresse $adresse): Adresse
    {
        $adresse->dehistoriser();
        $this->getObjectManager()->flush($adresse);
        return $adresse;
    }

    public function delete(Adresse $adresse): Adresse
    {
        $this->getObjectManager()->remove($adresse);
        $this->getObjectManager()->flush($adresse);
        return $adresse;
    }

    /** REQUETAGE *****************************************************************************************************/

    public function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->getObjectManager()->getRepository(Adresse::class)->createQueryBuilder('adresse')
            ->join('adresse.proposition', 'proposition')->addSelect('proposition');
        return $qb;
    }

    public function getAdresse(?int $id): ?Adresse
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('adresse.id = :id')->setParameter('id', $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs [".Adresse::class."] partagent le même id [".$id."]",0,$e);
        }
        return $result;
    }

    public function getRequestedAdresse(AbstractActionController $controller, string $param='adresse'): ?Adresse
    {
        $id = $controller->params()->fromRoute($param);
        return $this->getAdresse($id);
    }

    public function getAdresseByProposition(Proposition $proposition): ?Adresse
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('adresse.proposition = :proposition')->setParameter('proposition', $proposition)
            ->andWhere('adresse.histoDestruction IS NULL')
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs [".Adresse::class."] actives partagent le même proposition [".$proposition->getId()."]",0,$e);
        }
        return $result;
    }

    /** FACADE ********************************************************************************************************/
}