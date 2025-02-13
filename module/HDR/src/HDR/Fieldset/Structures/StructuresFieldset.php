<?php

namespace HDR\Fieldset\Structures;

use DoctrineModule\Form\Element\ObjectSelect;
use HDR\Entity\Db\HDR;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
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

    private array $etablissements;
    private array $ecolesDoctorales;
    private array $unitesRecherche;

    public function setEtablissements(array $etablissements): void
    {
        $options = [];
        foreach ($etablissements as $etablissement) {
            $sigle = $etablissement->getStructure()?->getSigle() ? " (".$etablissement->getStructure()->getSigle().")" : null;
            $options[$etablissement->getId()] = $etablissement->getStructure()?->getLibelle() . $sigle;
        }
        $this->etablissements = $options;
    }

    public function setEcolesDoctorales(array $ecolesDoctorales): void
    {
        $options = [];

        foreach ($ecolesDoctorales as $ecole) {
            $sigle = $ecole->getStructure()?->getCode() ? " (".$ecole->getStructure()->getCode().")" : null;
            $options[$ecole->getId()] = $ecole->getStructure()?->getLibelle() . $sigle;
        }
        $this->ecolesDoctorales = $options;
    }

    public function setUnitesRecherche(array $unitesRecherche): void
    {
        Assert::allIsInstanceOf($unitesRecherche, UniteRecherche::class);
        $this->unitesRecherche = $unitesRecherche;
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
                'label' => "Établissement <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => Etablissement::class,
                'value_options' => $this->etablissements,
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

//        $this->add([
//            'type' => ObjectSelect::class,
//            'name' => 'ecoleDoctorale',
//            'options' => [
//                'label' => "École doctorale <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> : ",
//                'label_options' => [ 'disable_html_escape' => true, ],
//                'target_class' => EcoleDoctorale::class,
//                'value_options' => $this->ecolesDoctorales,
//            ],
//            'attributes' => [
//                'id' => 'ecole-doctorale',
//                'data-live-search' => 'true',
//                'class' => 'selectpicker show-menu-arrow',
//                'title' => "Sélectionner l'école doctorale"
//            ],
//        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
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
//            'ecoleDoctorale' => [
//                'required' => true,
//                'validators' => [
//                    [
//                        'name' => NotEmpty::class,
//                        'options' => [
//                            'messages' => [
//                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner une école doctorale.',
//                            ],
//                        ],
//                    ],
//                ],
//            ],
            'etablissement' => [
                'required' => true,
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