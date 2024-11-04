<?php

namespace These\Fieldset\Encadrement;

use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;

class EncadrementHydrator extends DoctrineObject
{
    use IndividuServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var These $object */
        $data = parent::extract($object);

        $coencadrants = $object->getId() !== null ?
            $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CO_ENCADRANT) :
            $object->getActeursByRoleCode(Role::CODE_CO_ENCADRANT);

        if(is_array($coencadrants)) usort($coencadrants, fn(Acteur $a, Acteur $b) => $a->getIndividu()->getNomComplet() <=> $b->getIndividu()->getNomComplet());
        $i = 1;
        foreach ($coencadrants as $coencadrant) {
            $data['coencadrant' . $i . '-individu'] = [
                'id' => $coencadrant->getIndividu()->getId(),
                'label' => $coencadrant->getIndividu()->getNomComplet()
            ];
            $i++;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        $temoins = [];
        for ($i = 1; $i <= EncadrementFieldset::NB_COENCADRANTS_MAXI; $i++) {
            if ($individuId = $data['coencadrant' . $i . '-individu']['id'] ?? null) {
                /** @var Individu $individu */
                $individu = $this->individuService->getRepository()->find($individuId);
                $acteur = $object->getId() ? $this->acteurService->getRepository()->findActeurByIndividuAndThese($individu, $object) : null;
                $role = $this->roleService->getRepository()->findByCode(Role::CODE_CO_ENCADRANT);
                if ($acteur === null) {
                    $acteur = $this->acteurService->newActeur($object, $individu, $role);
                    $object->addActeur($acteur);
                }
                $acteur->setRole($role);
                $acteur->setEtablissement($object->getEtablissement());

                $temoins[] = $acteur->getId();
            }
        }
        $coencadrants = $object->getId() ? $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CO_ENCADRANT) : [];
        foreach ($coencadrants as $acteur) {
            if (array_search($acteur->getId(), $temoins) === false) {
                $acteur->historiser();
            }
        }

        return parent::hydrate($data,$object);
    }
}