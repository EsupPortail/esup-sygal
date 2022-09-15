<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Entity\Db\Repository;

use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;
use Formation\Entity\Db\SessionStructureValide;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class SessionStructureValideRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".session", "session")->addSelect("session")
            ->join($alias.".structure", "structure")->addSelect("structure")
        ;
        return $qb;
    }

    /**
     * @param int|null $id
     * @return SessionStructureValide|null
     */
    public function getSessionStructureValide(?int $id) : ?SessionStructureValide
    {
        $qb = $this->createQB('structureComplementaire')
            ->andWhere("structureComplementaire.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs SessionStructureValide partagent le même id [".$id."]", 0 , $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return SessionStructureValide|null
     */
    public function getRequestedSessionStructureValide(AbstractActionController $controller, string $param = 'structure-complementaire') : ?SessionStructureValide
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getSessionStructureValide($id);
        return $result;
    }
}