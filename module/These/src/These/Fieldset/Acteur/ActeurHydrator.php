<?php

namespace These\Fieldset\Acteur;

use Application\Entity\Db\Role;
use Doctrine\Inflector\Inflector;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Persistence\ObjectManager;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use These\Entity\Db\Acteur;
use These\Rule\ActeurRule;

class ActeurHydrator extends DoctrineObject
{
    private ActeurRule $acteurRule;

    public function __construct(ObjectManager $objectManager, bool $byValue = true, ?Inflector $inflector = null)
    {
        parent::__construct($objectManager, $byValue, $inflector);

        $this->acteurRule = new ActeurRule();
    }

    public function extract(object $object): array
    {
        /** @var Acteur $object */

        $data = parent::extract($object);

        // SearchAndSelect
        if ($data['individu'] instanceof Individu) {
            $data['individu'] = [
                'id' => $data['individu']->getId(),
                'label' => $data['individu']->getNomComplet(),
            ];
        }

        // Select
        $data['role'] = $data['role'] instanceof Role ? $data['role']->getId() : null;
        $data['uniteRecherche'] = $data['uniteRecherche'] instanceof UniteRecherche ? $data['uniteRecherche']->getId() : null;
        $data['qualite']  = $data['qualite'] instanceof Qualite ? $data['qualite']->getId():null;

        return $data;
    }

    public function hydrate(array $data, object $object): Acteur
    {
        /** @var Acteur $object */

        $data['etablissement'] = $data['etablissement']['id'] ?? null;
        $data['etablissementForce'] = $data['etablissementForce']['id'] ?? null;
        $data['individu'] = $data['individu']['id'] ?? null;

        $this->acteurRule->setActeur($object);
        $data = $this->acteurRule->prepareActeurHydratorData($data);

        return parent::hydrate($data, $object);
    }
}