<?php

namespace Acteur\Service\ActeurThese;

use Acteur\Entity\Db\ActeurThese;
use Acteur\Service\AbstractActeurService;
use Application\Entity\Db\Role;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;

class ActeurTheseService extends AbstractActeurService
{
    protected string $entityClass = ActeurThese::class;

    /**
     * @param ActeurThese[] $acteurs
     * @return ActeurThese[]
     */
    public function filterActeursDirecteurThese(array $acteurs): array
    {
        return array_filter($acteurs, function(ActeurThese $a) {
            return $a->getRole()->getCode() === Role::CODE_DIRECTEUR_THESE;
        });
    }

    /**
     * @param ActeurThese[] $acteurs
     * @return ActeurThese[]
     */
    public function filterActeursCoDirecteurThese(array $acteurs): array
    {
        return array_filter($acteurs, function(ActeurThese $a) {
            return $a->getRole()->getCode() === Role::CODE_CODIRECTEUR_THESE;
        });
    }

    /**
     * @return ActeurThese[]
     */
    public function getRapporteurDansTheseEnCours(Individu $individu): array
    {
        $qb = $this->getRapporteurDansEnCoursQb($individu)
            ->addSelect('these')->join('acteur.these', 'these')
            ->andWhere('these.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS);

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne une nouvelle instance d'{@see \Acteur\Entity\Db\ActeurThese}.
     *
     * @param \These\Entity\Db\These $these
     * @param \Individu\Entity\Db\Individu $individu
     * @param \Application\Entity\Db\Role|string $role
     * @param \Structure\Entity\Db\Etablissement|null $etablissement
     * @return \Acteur\Entity\Db\ActeurThese
     */
    public function newActeurThese(These $these, Individu $individu, Role|string $role, ?Etablissement $etablissement = null): ActeurThese
    {
        /** @var \Acteur\Entity\Db\ActeurThese $acteur */
        $acteur = $this->newActeur($individu, $role, $etablissement);
        $acteur->setThese($these);
        $acteur->setSourceCode($acteur->getSourceCode() . '_' . $these->getId());

        return $acteur;
    }

    public function getRequestedActeur(AbstractActionController $controller, string $param = 'acteur'): ActeurThese
    {
        /** @var \Acteur\Entity\Db\ActeurThese $acteur */
        $acteur = parent::getRequestedActeur($controller, $param);
        return $acteur;
    }
}