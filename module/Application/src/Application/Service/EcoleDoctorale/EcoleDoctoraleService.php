<?php

namespace Application\Service\EcoleDoctorale;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\EcoleDoctoraleRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method EcoleDoctorale|null findOneBy(array $criteria, array $orderBy = null)
 */
class EcoleDoctoraleService extends BaseService
{
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
     * @param Individu       $individu
     * @param EcoleDoctorale $ecole
     * @param Role           $role
     * @return EcoleDoctoraleIndividu
     */
    public function addIndividu(Individu $individu, EcoleDoctorale $ecole, Role $role = null)
    {
        /** @var EcoleDoctoraleIndividu $edi */
        $edi = $this->getEntityManager()->getRepository(EcoleDoctoraleIndividu::class)->findOneBy(array_filter([
            'individu' => $individu,
            'ecole'    => $ecole,
            'role'     => $role,
        ]));
        if (! $edi) {
            $edi = new EcoleDoctoraleIndividu();
            $edi
                ->setIndividu($individu)
                ->setEcole($ecole);

            $this->getEntityManager()->persist($edi);
        }
        if (! $role) {
            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => Role::ROLE_ID_ECOLE_DOCT]);
        }
        $edi->setRole($role);

        $this->getEntityManager()->flush($edi);

        return $edi;
    }

    /**
     * @param EcoleDoctoraleIndividu|int $edi
     * @return EcoleDoctoraleIndividu|null
     */
    public function removeIndividu($edi)
    {
        if (! $edi instanceof EcoleDoctoraleIndividu) {
            $edi = $this->getEntityManager()->find(EcoleDoctoraleIndividu::class, $edi);
            if (! $edi) {
                return null;
            }
        }

        $this->getEntityManager()->remove($edi);
        $this->getEntityManager()->flush($edi);

        return $edi;
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

        $this->flush($ecole);
    }

    public function undelete(EcoleDoctorale $ecole)
    {
        $ecole->dehistoriser();

        $this->flush($ecole);
    }

    public function create(EcoleDoctorale $ecole, Utilisateur $createur)
    {
        $ecole->setHistoCreateur($createur);

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

    public function getEcoleDoctoraleById($id) {
        $unite = $this->getRepository()->findOneBy(["id" => $id]);
        return $unite;
    }
}