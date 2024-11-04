<?php

namespace Application\Service\Variable;

use Application\Entity\Db\Repository\VariableRepository;
use Application\Entity\Db\Variable;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method Variable|null findOneBy(array $criteria, array $orderBy = null)
 */
class VariableService extends BaseService
{
    use SourceServiceAwareTrait;
    /**
     * @return VariableRepository
     */
    public function getRepository()
    {
        /** @var VariableRepository $repo */
        $repo = $this->entityManager->getRepository(Variable::class);

        return $repo;
    }

    /**
     * @param Variable $profil
     * @return  Variable
     */
    public function create($variable)
    {
        $this->getEntityManager()->persist($variable);
        try {
            $this->getEntityManager()->flush($variable);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la création d'une Variable", $e);
        }
        return $variable;
    }

    /**
     * @param Variable $variable
     * @return  Variable
     */
    public function update($variable)
    {
        try {
            $this->getEntityManager()->flush($variable);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour d'une Variable", $e);
        }
        return $variable;
    }

    /**
     * @param Variable $variable
     */
    public function delete($variable)
    {
        $this->getEntityManager()->remove($variable);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la suppression d'une Variable", $e);
        }
    }

    /**
     * @return Variable
     */
    public function newVariable(): Variable
    {
        $variable  = new Variable();
        $variable->setDateDebutValidite(date_create());
        $variable->setDateFinValidite(date_create()->modify('+10 years'));
        $variable->setSource($this->sourceService->fetchApplicationSource());
        $variable->setSourceCode($this->sourceService->genereateSourceCode());

        return $variable;
    }
}