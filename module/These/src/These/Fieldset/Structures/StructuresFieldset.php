<?php

namespace These\Fieldset\Structures;

use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class StructuresFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EntityManagerAwareTrait;

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'etablissement',
            'options' => [
                'label' => 'Établissement * :',
                'object_manager' => $this->etablissementService->getEntityManager(),
                'target_class' => Etablissement::class,
                'find_method' => [
                    'name' => 'findAll',
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure() && $targetEntity->getStructure()->getSigle() ? " (".$targetEntity->getStructure()->getSigle().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'établissement",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'uniteRecherche',
            'options' => [
                'label' => 'Unité de recherche * :',
                'object_manager' => $this->structureService->getEntityManager(),
                'target_class' => UniteRecherche::class,
                'find_method' => [
                    'name' => 'findAll',
                    'params' => [],
                    'callback' => function() {
                        return $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle', false);
                    },
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure()?->getCode() ? " (".$targetEntity->getStructure()->getCode().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'unite-recherche',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'unité de recherche",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'ecoleDoctorale',
            'options' => [
                'label' => 'École doctorale * :',
                'object_manager' => $this->structureService->getEntityManager(),
                'target_class' => EcoleDoctorale::class,
                'find_method' => [
                    'name' => 'findAll',
                    'params' => [],
                    'callback' => function() {
                        $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
                    },
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure()?->getCode() ? " (".$targetEntity->getStructure()->getCode().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'ecole-doctorale',
                'data-live-search' => 'true',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'école doctorale"
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'uniteRecherche' => [
                'required' => true,
            ],
            'ecoleDoctorale' => [
                'required' => true,
            ],
            'etablissement' => [
                'required' => true,
            ],
        ];
    }
}