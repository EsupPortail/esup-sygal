<?php

namespace These\Fieldset\Direction;

use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Hydrator\ActeurHydratorAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;

class DirectionHydrator implements HydratorInterface
{
    use IndividuServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RoleServiceAwareTrait;

    use ActeurHydratorAwareTrait;

    /**
     * @param \These\Entity\Db\These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        return array_merge(
            $this->extractDirecteur($object),
            $this->extractCodirecteurs($object)
        );
    }

    /**
     * @param array $data
     * @param \These\Entity\Db\These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        $this->hydrateDirecteur($data, $object);
        $this->hydrateCodirecteurs($data, $object);

        return $object;
    }

    /**
     * @param \These\Entity\Db\These $object
     * @return array
     */
    private function extractDirecteur(These $object): array
    {
        /** @var Acteur $directeur */
        if ($object->getId() !== null) {
            $directeur = current($this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_DIRECTEUR_THESE)) ?: null;
        } else {
            $directeur = $object->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE)->first() ?: null;
        }
        if ($directeur === null) {
            return [];
        }

        return $this->acteurHydrator->setKeyPrefix('directeur-')->extract($directeur);
    }

    /**
     * @param \These\Entity\Db\These $object
     * @return array
     */
    private function extractCodirecteurs(These $object): array
    {
        $data = [];

        /** @var Acteur[] $codirecteurs */
        if ($object->getId() !== null) {
            $codirecteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CODIRECTEUR_THESE);
        } else {
            $codirecteurs = $object->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        }
        usort($codirecteurs, fn(Acteur $a, Acteur $b) => $a->getIndividu()->getNomComplet() <=> $b->getIndividu()->getNomComplet());

        $i = 1;
        foreach ($codirecteurs as $codirecteur) {
            $data = array_merge($data, $this->acteurHydrator->setKeyPrefix('codirecteur' . $i . '-')->extract($codirecteur));
            $i++;
        }

        return $data;
    }

    private function hydrateDirecteur(array $data, These $these)
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->find($data['directeur-individu']['id']);
        $role = $this->roleService->getRepository()->findByCode(Role::CODE_DIRECTEUR_THESE);
        $acteur = $this->acteurService->getRepository()->findActeurByIndividuAndThese($individu, $these);
        if ($acteur === null) {
            $acteur = $this->acteurService->newActeur($these, $individu, $role);
        } else {
            $acteur->setRole($role);
        }
        $this->acteurHydrator->setKeyPrefix('directeur-')->hydrate($data, $acteur);
    }

    private function hydrateCodirecteurs(array $data, These $these)
    {
        $temoins = [];

        for ($i = 1; $i <= DirectionFieldset::NBCODIR; $i++) {
            if (isset($data['codirecteur' . $i . '-individu']['id'])/* and $data['codirecteur' . $i . '-qualite'] and $data['codirecteur' . $i . '-etablissement']*/) {
                /** @var Individu $individu */
                $individu = $this->individuService->getRepository()->find($data['codirecteur' . $i . '-individu']['id']);
                $role = $this->roleService->getRepository()->findByCode(Role::CODE_CODIRECTEUR_THESE);
                $acteur = $this->acteurService->getRepository()->findActeurByIndividuAndThese($individu, $these);
                if ($acteur === null) {
                    $acteur = $this->acteurService->newActeur($these, $individu, $role);
                } else {
                    $acteur->setRole($role);
                }
                $this->acteurHydrator->setKeyPrefix('codirecteur' . $i . '-')->hydrate($data, $acteur);

                $temoins[] = $acteur->getId();
            }
        }

        $existingCodirecteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);
        foreach ($existingCodirecteurs as $codirecteur) {
            if (array_search($codirecteur->getId(), $temoins) === false) {
                $codirecteur->historiser();
            }
        }
    }
}