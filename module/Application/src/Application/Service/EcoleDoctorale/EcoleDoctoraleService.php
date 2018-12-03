<?php

namespace Application\Service\EcoleDoctorale;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\EcoleDoctoraleRepository;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method EcoleDoctorale|null findOneBy(array $criteria, array $orderBy = null)
 */
class EcoleDoctoraleService extends BaseService implements RoleServiceAwareInterface
{
    use RoleServiceAwareTrait;

    /**
     * @return EcoleDoctoraleRepository
     */
    public function getRepository()
    {
        /** @var EcoleDoctoraleRepository $repo */
        $repo = $this->entityManager->getRepository(EcoleDoctorale::class);

        return $repo;
    }


    /**
     * @param int $id
     * @return Individu[]
     */
    public function getIndividuByEcoleDoctoraleId($id)
    {
        $ecole = $this->getRepository()->findOneBy(['id' => $id]);
        $individus = $this->roleService->getIndividuByStructure($ecole->getStructure());

        return $individus;
    }


    /**
     * Historise une ED.
     *
     * @param EcoleDoctorale $ecole
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(EcoleDoctorale $ecole, Utilisateur $destructeur)
    {
        $ecole->historiser($destructeur);
        $ecole->getStructure()->historiser($destructeur);

        $this->flush($ecole);
    }

    public function undelete(EcoleDoctorale $ecole)
    {
        $ecole->dehistoriser();
        $ecole->getStructure()->dehistoriser();

        $this->flush($ecole);
    }

    public function create(EcoleDoctorale $ecole, Utilisateur $createur)
    {
        $ecole->setHistoCreateur($createur);
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'ecole-doctorale']);
        $ecole->getStructure()->setTypeStructure($typeStructure);

        $this->persist($ecole);
        $this->flush($ecole);

        return $ecole;
    }

    public function update(EcoleDoctorale $ecole)
    {
        $this->flush($ecole);

        return $ecole;
    }

    public function setLogo(EcoleDoctorale $ecole, $cheminLogo)
    {
        $ecole->setCheminLogo($cheminLogo);
        $this->flush($ecole);

        return $ecole;
    }

    public function deleteLogo(EcoleDoctorale $ecole)
    {
        $ecole->setCheminLogo(null);
        $this->flush($ecole);

        return $ecole;
    }

    private function persist(EcoleDoctorale $ecole)
    {
        $this->getEntityManager()->persist($ecole);
        $this->getEntityManager()->persist($ecole->getStructure());
    }

    private function flush(EcoleDoctorale $ecole)
    {
        try {
            $this->getEntityManager()->flush($ecole);
            $this->getEntityManager()->flush($ecole->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'ED", null, $e);
        }
    }
}