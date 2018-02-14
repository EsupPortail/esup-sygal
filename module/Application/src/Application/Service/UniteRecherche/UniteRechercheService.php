<?php

namespace Application\Service\UniteRecherche;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(UniteRecherche::class);
    }

    /**
     * @param Individu       $individu
     * @param UniteRecherche $unite
     * @param Role           $role
     * @return UniteRechercheIndividu
     */
    public function addIndividu(Individu $individu, UniteRecherche $unite, Role $role = null)
    {
        /** @var UniteRechercheIndividu $edi */
        $edi = $this->getEntityManager()->getRepository(UniteRechercheIndividu::class)->findOneBy(array_filter([
            'individu'       => $individu,
            'uniteRecherche' => $unite,
            'role'           => $role,
        ]));
        if (! $edi) {
            $edi = new UniteRechercheIndividu();
            $edi
                ->setIndividu($individu)
                ->setUniteRecherche($unite);

            $this->getEntityManager()->persist($edi);
        }
        if (! $role) {
            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => Role::ROLE_ID_UNITE_RECH]);
        }
        $edi->setRole($role);

        $this->getEntityManager()->flush($edi);

        return $edi;
    }

    /**
     * @param UniteRechercheIndividu|int $edi
     * @return UniteRechercheIndividu|null
     */
    public function removeIndividu($edi)
    {
        if (! $edi instanceof UniteRechercheIndividu) {
            $edi = $this->getEntityManager()->find(UniteRechercheIndividu::class, $edi);
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
     * @param UniteRecherche $ecole
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(UniteRecherche $ecole, Utilisateur $destructeur)
    {
        $ecole->historiser($destructeur);

        $this->getEntityManager()->flush($ecole);
    }

    public function undelete(UniteRecherche $ecole)
    {
        $ecole->dehistoriser();

        $this->getEntityManager()->flush($ecole);
    }

    public function create(UniteRecherche $ecole, Utilisateur $createur)
    {
        $ecole->setHistoCreateur($createur);

        $this->getEntityManager()->persist($ecole);
        $this->getEntityManager()->flush($ecole);

        return $ecole;
    }

    public function update(UniteRecherche $ecole)
    {
        $this->getEntityManager()->flush($ecole);

        return $ecole;
    }

    public function setLogo(UniteRecherche $unite, $cheminLogo)
    {
        $unite->setCheminLogo($cheminLogo);
        $this->getEntityManager()->flush($unite);

        return $unite;
    }

    public function deleteLogo(UniteRecherche $unite)
    {
        $unite->setCheminLogo(null);
        $this->getEntityManager()->flush($unite);

        return $unite;
    }
}