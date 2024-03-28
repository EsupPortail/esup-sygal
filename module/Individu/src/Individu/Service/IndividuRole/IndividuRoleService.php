<?php

namespace Individu\Service\IndividuRole;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Individu\Entity\Db\IndividuRole;

class IndividuRoleService extends BaseService
{
    public function getRepository(): DefaultEntityRepository
    {
        /** @var \Individu\Entity\Db\Repository\IndividuRoleRepository $repo */
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        return $repo;
    }

    /**
     * @throws \Exception Erreur rencontrée lors de l'enregistrement d'un IndividuRole
     */
    public function save(IndividuRole $individuRole): void
    {
        try {
            // NB : <cascade-all> + remove-orphan="true" sont nécessaires sur la relation 'individuRoleEtablissement'
            $this->entityManager->persist($individuRole);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $message = "Erreur rencontrée lors de l'enregistrement d'un IndividuRole";
            error_log($message);
            throw new Exception($message, null, $e);
        }
    }

    /*****************************************************************************
     *
     * TODO : les méthodes de {@see \Application\Service\Role\RoleService}
     *        qui manipule des IndividuRole devrait être déplacées ici !
     *
     *****************************************************************************/

}