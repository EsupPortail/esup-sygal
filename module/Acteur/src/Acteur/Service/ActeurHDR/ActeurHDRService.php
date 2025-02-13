<?php

namespace Acteur\Service\ActeurHDR;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Service\AbstractActeurService;
use Application\Entity\Db\Role;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\Etablissement;

class ActeurHDRService extends AbstractActeurService
{
    protected string $entityClass = ActeurHDR::class;

    /**
     * @param ActeurHDR[] $acteurs
     * @return ActeurHDR[]
     */
    public function filterActeursGarantHDR(array $acteurs): array
    {
        return array_filter($acteurs, function(ActeurHDR $a) {
            return $a->getRole()->getCode() === Role::CODE_HDR_GARANT;
        });
    }

    /**
     * @return ActeurHDR[]
     */
    public function getRapporteurDansHDREnCours(Individu $individu): array
    {
        $qb = $this->getRapporteurDansEnCoursQb($individu)
            ->addSelect('hdr')->join('acteur.hdr', 'hdr')
            ->andWhere('hdr.etat = :encours')
            ->setParameter('encours', HDR::ETAT_EN_COURS);

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne une nouvelle instance d'{@see ActeurHDR}.
     *
     * @param HDR $hdr
     * @param Individu $individu
     * @param Role|string $role
     * @param Etablissement|null $etablissement
     * @return ActeurHDR
     */
    public function newActeurHDR(HDR $hdr, Individu $individu, Role|string $role, ?Etablissement $etablissement = null): ActeurHDR
    {
        /** @var ActeurHDR $acteur */
        $acteur = $this->newActeur($individu, $role, $etablissement);
        $acteur->setHDR($hdr);
        $acteur->setSourceCode($acteur->getSourceCode() . '_' . $hdr->getId());

        return $acteur;
    }

    public function getRequestedActeur(AbstractActionController $controller, string $param = 'acteur'): ActeurHDR
    {
        /** @var ActeurHDR $acteur */
        $acteur = parent::getRequestedActeur($controller, $param);
        return $acteur;
    }
}