<?php

namespace HDR\Fieldset\Structures;

use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Webmozart\Assert\Assert;

class StructuresFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EntityManagerAwareTrait;

    private array $unitesRecherche;

    public function setUnitesRecherche(array $unitesRecherche): void
    {
        Assert::allIsInstanceOf($unitesRecherche, UniteRecherche::class);
        $this->unitesRecherche = $unitesRecherche;
    }

    public function init(): void
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'uniteRecherche',
            'options' => [
                'label' => "Unité de recherche <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => UniteRecherche::class,
                'value_options' => UniteRecherche::toValueOptions($this->unitesRecherche),
            ],
            'attributes' => [
                'id' => 'unite-recherche',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'unité de recherche",
                'data-live-search' => 'true',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'uniteRecherche' => [
                'required' => true,
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
        ];
    }
}