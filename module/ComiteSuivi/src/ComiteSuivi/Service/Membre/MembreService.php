<?php

namespace ComiteSuivi\Service\Membre;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Source;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Entity\Db\Membre;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class MembreService {
    use EntityManagerAwareTrait;
    use DateTimeTrait;
    use SourceServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function create(Membre $membre) : Membre
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $membre->setHistoCreateur($user);
        $membre->setHistoCreation($date);
        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($membre);
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function update(Membre $membre) : Membre
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function historise(Membre $membre) : Membre
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $membre->setHistoDestructeur($user);
        $membre->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function restore(Membre $membre) : Membre
    {
        $membre->setHistoDestructeur(null);
        $membre->setHistoDestruction(null);

        try {
            $this->getEntityManager()->persist($membre);
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function delete(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->remove($membre);
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $membre;
    }

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder('membre')
            ->addSelect('comite')->join('membre.comite', 'comite')
            ->addSelect('role')->join('membre.role', 'role')
            ->addSelect('individu')->leftJoin('membre.individu', 'individu')
        ;
        return $qb;
    }

    /**
     * @param string $champ
     * @param string $ordre
     * @return Membre[]
     */
    public function getMembres(string $champ = 'id', string $ordre = 'ASC') : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('membre.' . $champ, $ordre)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int|null $id
     * @return Membre|null
     */
    public function getMembre(?int $id) : ?Membre
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs Membre patagent le même id [".$id."]', 0, $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Membre|null
     */
    public function getRequestedMembre(AbstractActionController $controller, string $param = 'membre') : ?Membre
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getMembre($id);
        return $result;
    }

    /**
     * @param ComiteSuivi $comite
     * @return Membre[]
     */
    public function getExaminateurs(ComiteSuivi $comite) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.comite = :comite')
            ->setParameter('comite', $comite)
            ->andWhere('role.code = :examinateur')
            ->setParameter('examinateur', Membre::ROLE_EXAMINATEUR_CODE)
            ->andWhere('membre.histoDestruction IS NULL')
            ->orderBy('membre.nom, membre.prenom', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Membre $membre
     * @return Individu $individu
     */
    public function createIndividuFromMembre(Membre $membre) : Individu
    {
        $individu = new Individu();
        $individu->setPrenom($membre->getPrenom());
        $individu->setNomUsuel($membre->getNom());
        $individu->setEmail($membre->getEmail());


        /** @var Source $sygal */
        $sygal = $this->sourceService->getRepository()->find(6);
        $code = "SyGAL::" . uniqid();

        $individu->setSource($sygal);
        $individu->setSourceCode($code);

        return $individu;
    }
}