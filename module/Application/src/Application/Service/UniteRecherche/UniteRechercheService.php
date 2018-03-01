<?php

namespace Application\Service\UniteRecherche;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\UniteRechercheRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService
{
    /**
     * @return UniteRechercheRepository
     */
    public function getRepository()
    {
        /** @var UniteRechercheRepository $repo */
        $repo = $this->entityManager->getRepository(UniteRecherche::class);

        return $repo;
    }

    /**
     * @param Individu       $individu
     * @param UniteRecherche $unite
     * @param Role           $role
     * @return UniteRechercheIndividu
     */
    public function addIndividu(Individu $individu, UniteRecherche $unite, Role $role = null)
    {
        /** @var UniteRechercheIndividu $uri */
        $uri = $this->getEntityManager()->getRepository(UniteRechercheIndividu::class)->findOneBy(array_filter([
            'individu'       => $individu,
            'uniteRecherche' => $unite,
            'role'           => $role,
        ]));
        if (! $uri) {
            $uri = new UniteRechercheIndividu();
            $uri
                ->setIndividu($individu)
                ->setUniteRecherche($unite);

            $this->getEntityManager()->persist($uri);
        }
        if (! $role) {
            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => Role::ROLE_ID_UNITE_RECH]);
        }
        $uri->setRole($role);

        $this->getEntityManager()->flush($uri);

        return $uri;
    }

    /**
     * @param UniteRechercheIndividu|int $uri
     * @return UniteRechercheIndividu|null
     */
    public function removeIndividu($uri)
    {
        if (! $uri instanceof UniteRechercheIndividu) {
            $uri = $this->getEntityManager()->find(UniteRechercheIndividu::class, $uri);
            if (! $uri) {
                return null;
            }
        }

        $this->getEntityManager()->remove($uri);
        $this->getEntityManager()->flush($uri);

        return $uri;
    }

    /**
     * Historise une ED.
     *
     * @param UniteRecherche $ur
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(UniteRecherche $ur, Utilisateur $destructeur)
    {
        $ur->historiser($destructeur);

        $this->flush($ur);
    }

    public function undelete(UniteRecherche $ur)
    {
        $ur->dehistoriser();

        $this->flush($ur);
    }

    public function create(UniteRecherche $ur, Utilisateur $createur)
    {
        $ur->setHistoCreateur($createur);

        $this->persist($ur);
        $this->flush($ur);

        return $ur;
    }

    public function update(UniteRecherche $ur)
    {
        $this->flush($ur);

        return $ur;
    }

    public function setLogo(UniteRecherche $unite, $cheminLogo)
    {
        $unite->setCheminLogo($cheminLogo);
        $this->flush($unite);

        return $unite;
    }

    public function deleteLogo(UniteRecherche $unite)
    {
        $unite->setCheminLogo(null);
        $this->flush($unite);

        return $unite;
    }

    private function persist(UniteRecherche $ur)
    {
        $this->getEntityManager()->persist($ur);
        $this->getEntityManager()->persist($ur->getStructure());
    }

    private function flush(UniteRecherche $ur)
    {
        try {
            $this->getEntityManager()->flush($ur);
            $this->getEntityManager()->flush($ur->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }
}