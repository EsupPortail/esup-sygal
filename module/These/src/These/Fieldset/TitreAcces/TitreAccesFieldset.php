<?php

namespace These\Fieldset\TitreAcces;

use Application\Entity\Db\Pays;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class TitreAccesFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use EntityManagerAwareTrait;

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Select("titreAccesInterneExterne"))
                ->setEmptyOption("Sélectionnez une option")
                ->setValueOptions([
                    'E' => 'Externe',
                    'I' => 'Interne',
                ])
                ->setLabel("Accès : * ")
                ->setAttributes([
                    'class' => 'selectpicker show-tick',
                    'id' => "titreAccesInterneExterne"
                ])
        );

        $this->add(
            (new Text("libelleTitreAcces"))
                ->setLabel("Libellé : * ")
        );

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'pays',
            'options' => [
                'label' => "Pays d'obtention * : ",
                'object_manager' => $this->getEntityManager(),
                'target_class' => Pays::class,
                'find_method' => [
                    'name' => 'findAll',
                ],
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
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'titreAccesInterneExterne' => [
                'required' => true,
            ],
            'libelleTitreAcces' => [
                'required' => true,
            ],
            'pays' => [
                'required' => true,
            ],
            'etablissement' => [
                'required' => true,
            ],
        ];
    }
}