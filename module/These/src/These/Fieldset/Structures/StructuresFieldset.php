<?php

namespace These\Fieldset\Structures;

use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Service\EntityManagerAwareTrait;

class StructuresFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EntityManagerAwareTrait;

    public function prepareElement(FormInterface $form): void
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();

        $this->get('etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('uniteRecherche')->setAttribute('disabled', !$estModifiable);
        $this->get('ecoleDoctorale')->setAttribute('disabled', !$estModifiable);



        parent::prepareElement($form); // TODO: Change the autogenerated stub
    }

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
                'label' => 'Établissement : *',
                'object_manager' => $this->etablissementService->getEntityManager(),
                'target_class' => Etablissement::class,
                'find_method' => [
                    'name' => 'findAll',
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure() && $targetEntity->getStructure()->getSigle() ? " (".$targetEntity->getStructure()->getSigle().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
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
                'label' => 'Unité de recherche : *',
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
                'label' => 'École doctorale : *',
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
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();
        return [
            'uniteRecherche' => [
                'required' => $estModifiable,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner une unité de recherche.',
                            ],
                        ],
                    ],
                ],
            ],
            'ecoleDoctorale' => [
                'required' => $estModifiable,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner une école doctorale.',
                            ],
                        ],
                    ],
                ],
            ],
            'etablissement' => [
                'required' => $estModifiable,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner un établissement.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}