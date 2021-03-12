<?php

namespace Soutenance\Service\Intervention;

use Application\Entity\DateTimeAwareTrait;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Intervention;
use UnicaenApp\Entity\UserInterface;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InterventionService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use DateTimeAwareTrait;

    /** GESTION DES ENTITÉS *******************************************************************************************/

    /**
     * @param Intervention $intervention
     * @return Intervention
     */
    public function create(Intervention $intervention) : Intervention
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $intervention->setHistoCreation($date);
        $intervention->setHistoCreateur($user);
        $intervention->setHistoModification($date);
        $intervention->setHistoModificateur($user);

        try {
            $this->getEntityManager()->persist($intervention);
            $this->getEntityManager()->flush($intervention);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de la création d'une intervention.", $e);
        }

        return $intervention;
    }

    /**
     * @param Intervention $intervention
     * @return Intervention
     */
    public function update(Intervention $intervention) : Intervention
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $intervention->setHistoModification($date);
        $intervention->setHistoModificateur($user);

        try {
            $this->getEntityManager()->flush($intervention);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de la mise à jour d'une intervention.", $e);
        }

        return $intervention;
    }

    /**
     * @param Intervention $intervention
     * @return Intervention
     */
    public function delete(Intervention $intervention) : Intervention
    {
        try {
            $this->getEntityManager()->remove($intervention);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'effacement d'une intervention", $e);
        }
        return $intervention;
    }

    /**
     * @param Intervention $intervention
     * @return Intervention
     */
    public function historiser(Intervention $intervention) : Intervention
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();
        $intervention->setHistoDestruction($date);
        $intervention->setHistoDestructeur($user);

        try {
            $this->getEntityManager()->flush($intervention);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'historisation d'une intervention.");
        }

        return $intervention;
    }

    /**
     * @param Intervention $intervention
     * @return Intervention
     */
    public function restiore(Intervention $intervention) : Intervention
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $intervention->setHistoDestruction(null);
        $intervention->setHistoDestructeur(null);

        try {
            $this->getEntityManager()->flush($intervention);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la restauration d'une intervention.");
        }

        return $intervention;
    }

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Intervention::class)->createQueryBuilder('intervention')
            ->addSelect('these')->join('intervention.these', 'these');
        return $qb;
    }

    /**
     * @var string $champ
     * @var string $ordre
     * @return Intervention[]
     */
    public function getInterventions($champ = 'id', $ordre = 'ASC') : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('intervention.' . $champ, $ordre);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param These $these
     * @param int $type
     * @return Intervention[]
     */
    public function getInterventionByTheseAndType(These $these, int $type) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('intervention.these = :these')
            ->setParameter('these', $these)
            ->andWhere('intervention.type = :type')
            ->setParameter('type', $type)
            ->andWhere('intervention.histoDestruction IS NULL')
            ->orderby('intervention.histoCreation', 'DESC');
        $result = $qb->getQuery()->getResult();
        return $result;
    }

}