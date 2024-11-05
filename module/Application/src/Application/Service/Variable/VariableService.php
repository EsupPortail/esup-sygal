<?php

namespace Application\Service\Variable;

use Application\Entity\Db\Repository\VariableRepository;
use Application\Entity\Db\Variable;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method Variable|null findOneBy(array $criteria, array $orderBy = null)
 */
class VariableService extends BaseService
{
    use SourceServiceAwareTrait;

    public function getRepository(): VariableRepository
    {
        /** @var VariableRepository $repo */
        $repo = $this->entityManager->getRepository(Variable::class);

        return $repo;
    }

    public function create(Variable $variable): Variable
    {
        try {
            $this->getEntityManager()->persist($variable);
            $this->getEntityManager()->flush($variable);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la création de la Variable", $e);
        }

        return $variable;
    }

    public function update(Variable $variable): Variable
    {
        try {
            $this->getEntityManager()->flush($variable);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour de la Variable", $e);
        }

        return $variable;
    }

    public function delete(Variable $variable): void
    {
        try {
            $this->getEntityManager()->remove($variable);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la suppression de la Variable", null, $e);
        }
    }

    public function newVariable(Etablissement $etablissement): Variable
    {
        $variable  = new Variable();
        $variable->setDateDebutValidite(date_create());
        $variable->setDateFinValidite(date_create()->modify('+10 years'));
        $variable->setSource($this->sourceService->fetchApplicationSource());
        $variable->setSourceCode($this->sourceService->genereateSourceCode());
        $variable->setEtablissement($etablissement);

        return $variable;
    }
}