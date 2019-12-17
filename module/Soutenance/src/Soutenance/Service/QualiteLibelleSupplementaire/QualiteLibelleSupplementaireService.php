<?php

namespace Soutenance\Service\QualiteLibelleSupplementaire;

use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Soutenance\Entity\QualiteLibelleSupplementaire;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class QualiteLibelleSupplementaireService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param QualiteLibelleSupplementaire $libelleSupplementaire
     * @return QualiteLibelleSupplementaire
     */
    public function create(QualiteLibelleSupplementaire $libelleSupplementaire)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données d'historisation", 0, $e);
        }

        $libelleSupplementaire->setHistoCreateur($user);
        $libelleSupplementaire->setHistoCreation($date);
        $libelleSupplementaire->setHistoModificateur($user);
        $libelleSupplementaire->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($libelleSupplementaire);
            $this->getEntityManager()->flush($libelleSupplementaire);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD.", 0, $e);
        }

        return $libelleSupplementaire;
    }

    /**
     * @param QualiteLibelleSupplementaire $libelleSupplementaire
     * @return QualiteLibelleSupplementaire
     */
    public function update(QualiteLibelleSupplementaire $libelleSupplementaire)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données d'historisation", 0, $e);
        }

        $libelleSupplementaire->setHistoModificateur($user);
        $libelleSupplementaire->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($libelleSupplementaire);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD.", 0, $e);
        }

        return $libelleSupplementaire;
    }

    /**
     * @param QualiteLibelleSupplementaire $libelleSupplementaire
     * @return QualiteLibelleSupplementaire
     */
    public function delete(QualiteLibelleSupplementaire $libelleSupplementaire)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données d'historisation", 0, $e);
        }

        $libelleSupplementaire->setHistoDestructeur($user);
        $libelleSupplementaire->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($libelleSupplementaire);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD.", 0, $e);
        }

        return $libelleSupplementaire;
    }

    /** REQUETE *******************************************************************************************************/

    /**
     * @param integer $id
     * @return QualiteLibelleSupplementaire
     */
    public function getQualiteLibelleSupplementaire($id)
    {
        $qb = $this->getEntityManager()->getRepository(QualiteLibelleSupplementaire::class)->createQueryBuilder('libelle')
            ->addSelect('qualite')->join('libelle.qualite', 'qualite')
            ->andWhere('libelle.id = :id')
            ->setParameter('id', $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs QualiteLibelleSupplementaire partagent le même id [".$id."].", 0, $e);
        }
        return $result;
    }


    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return QualiteLibelleSupplementaire
     */
    public function getRequestedLibelle($controller, $param = 'libelle')
    {
        $id = $controller->params()->fromRoute($param);
        $libelle = $this->getQualiteLibelleSupplementaire($id);
        return $libelle;
    }
}