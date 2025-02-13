<?php

namespace ComiteSuiviIndividuel\Service\Membre;

use ComiteSuiviIndividuel\Entity\Db\Membre;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Laminas\Mvc\Controller\AbstractActionController;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MembreService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->persist($membre);
            $this->getEntityManager()->flush($membre);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $membre;
    }

    public function update(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->flush($membre);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $membre;
    }

    public function historise(Membre $membre) : Membre
    {
        $membre->historiser();
        try {
            $this->getEntityManager()->flush($membre);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $membre;
    }

    public function restore(Membre $membre) : Membre
    {
        $membre->dehistoriser();
        try {
            $this->getEntityManager()->flush($membre);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $membre;
    }

    public function delete(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->remove($membre);
            $this->getEntityManager()->flush($membre);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $membre;
    }

    /** REQUETAGE *****************************************************************************************************/

    private function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder('membre')
//            ->leftJoin('membre.acteur', 'acteur')->addSelect('acteur') // n'existe plus
            ->leftJoin('membre.qualite', 'qualite')->addSelect('qualite')
            ->leftJoin('membre.these', 'these')->addSelect('these')
        ;
        return $qb;
    }

    public function getMembre(?int $id) : ?Membre
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.id = :id')->setParameter('id', $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Membre partagent le même id [".$id."]");
        }
        return $result;
    }

    public function getRequestedMembre(AbstractActionController $controller, string $param='membre') : ?Membre
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getMembre($id);
        return $result;
    }

    /**
     * @param These $these
     * @return Membre[]
     */
    public function getMembresbyThese(These $these) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.these = :these')->setParameter('these', $these);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /** FACADE ********************************************************************************************************/
}