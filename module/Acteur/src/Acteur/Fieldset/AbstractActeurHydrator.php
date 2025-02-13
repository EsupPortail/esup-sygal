<?php

namespace Acteur\Fieldset;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Rule\ActeurRuleInterface;
use Application\Entity\Db\Role;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\UniteRecherche;

class AbstractActeurHydrator extends DoctrineObject
{
    protected ActeurRuleInterface $acteurRule;

//    public function __construct(ObjectManager $objectManager, bool $byValue = true, ?Inflector $inflector = null)
//    {
//        parent::__construct($objectManager, $byValue, $inflector);
//
//        $this->acteurRule = new ActeurTheseRule();
//    }

    public function extract(object $object): array
    {
        /** @var ActeurThese|ActeurHDR $object */

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

    public function hydrate(array $data, object $object): ActeurThese|ActeurHDR
    {
        /** @var ActeurThese|ActeurHDR $object */

        $data['etablissement'] = $data['etablissement']['id'] ?? null;
        $data['uniteRecherche'] = $data['uniteRecherche'] != "" ? $data['uniteRecherche'] : null;
        $data['individu'] = $data['individu']['id'] ?? null;

        $this->acteurRule->setActeur($object);
        $data = $this->acteurRule->prepareActeurHydratorData($data);

        return parent::hydrate($data, $object);
    }
}