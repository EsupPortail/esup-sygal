<?php

namespace Soutenance\Service\Qualite;

use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class QualiteService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function create(Qualite $qualite) : Qualite
    {
        try {
            $this->getEntityManager()->persist($qualite);
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'enregistrement en BD d'une nouvelle qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function update(Qualite $qualite) : Qualite
    {
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function historise(Qualite $qualite) : Qualite
    {
        try {
            $qualite->historiser();
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function restoreQualite(Qualite $qualite) : Qualite
    {
        try {
            $qualite->dehistoriser();
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function delete(Qualite $qualite) : Qualite
    {
        try {
            $this->getEntityManager()->remove($qualite);
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'effacement en BD d'une nouvelle qualité.");
        }
        return $qualite;
    }

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->addSelect('libelleSupplementaire')->leftJoin('qualite.libellesSupplementaires', 'libelleSupplementaire');

        return $qb;
    }

    /**
     * @return Qualite[]
     */
    public function getQualites() : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('qualite.libelle', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Qualite[]
     */
    public function getQualitesForAdmission() : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("qualite.admission = :admission")
            ->setParameter("admission", "O")
            ->orderBy('qualite.libelle', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int|null $id
     * @return Qualite|null
     */
    public function getQualite(?int $id) : ?Qualite
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("qualite.id = :id")
            ->setParameter("id", $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Qualite|null
     */
    public function getRequestedQualite(AbstractActionController $controller, string $paramName = 'qualite') : ?Qualite
    {
        $id = $controller->params()->fromRoute($paramName);
        return $this->getQualite($id);
    }

    public function getQualiteByLibelle($libelle) {
        $qb = $this->createQueryBuilder()
            ->andWhere("qualite.libelle = :libelle OR libelleSupplementaire.libelle = :libelle")
            ->setParameter("libelle", $libelle);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }

    /**
     * @return Qualite[]
     */
    public function findAllQualites() : array
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder('qualite')
            ->orderBy('qualite.rang, qualite.libelle');
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getQualitesAsGroupOptions() : array
    {
        $listings = [];
        $qualites = $this->getQualites();
        foreach ($qualites as $qualite) {
            $listings[$qualite->getRang()][] = $qualite;
        }

        $result = [];
        foreach ($listings as $rang => $qualites) {
            $options = [];
            foreach ($qualites as $qualite) {
                $this_option = [
                    'value' => $qualite->getId(),
                    'label' => $qualite->getLibelle(),
                ];
                $options[] = $this_option;
            }
            $this_group = [
                'label' => $rang,
                'options' => $options,
            ];
            $result[] = $this_group;
        }
        return $result;
    }

    /**
     * @param string $libelle
     * @return Qualite|null
     */
    public function findQualiteByLibelle(string $libelle) : ?Qualite
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('qualite.libelle = :libelle')
            ->setParameter('libelle', $libelle)
        ;
        $result = $qb->getQuery()->getResult();
        if (!empty($result)) return current($result);
        return null;
    }

}