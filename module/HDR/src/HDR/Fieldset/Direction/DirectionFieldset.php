<?php

namespace HDR\Fieldset\Direction;

use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Filter\SearchAndSelectFilter;
use UnicaenApp\Form\Element\SearchAndSelect;
use Webmozart\Assert\Assert;

class DirectionFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use StructureServiceAwareTrait;

    private string $urlAutocompleteIndividu;
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

    /**
     * @param array $unitesRecherche
     */
    public function setUnitesRecherche(array $unitesRecherche): void
    {
        Assert::allIsInstanceOf($unitesRecherche, UniteRecherche::class);
        $this->unitesRecherche = $unitesRecherche;
    }

    public function setUrlAutocompleteIndividu(string $urlAutocompleteIndividu): void
    {
        $this->urlAutocompleteIndividu = $urlAutocompleteIndividu;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        /**  DIRECTION  ***********************************************************************************************/
        $individu = new SearchAndSelect('garant-individu', [
            'label' => "Individu <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
            'label_options' => [ 'disable_html_escape' => true, ]
        ]);
        $individu
            ->setAutocompleteSource($this->urlAutocompleteIndividu)
            ->setSelectionRequired()
            ->setAttributes([
                'id' => 'garant-individu',
                'placeholder' => "Recherchez l'individu...",
            ]);
        $this->add($individu);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'garant-etablissement',
            'options' => [
                'label' => "Établissement <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => Etablissement::class,
                'value_options' => $this->etablissements,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'garant-etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'établissement",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'garant-uniteRecherche',
            'options' => [
                'label' => "Unité de recherche <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => UniteRecherche::class,
                'value_options' => UniteRecherche::toValueOptions($this->unitesRecherche),
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'garant-uniteRecherche',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'unité de recherche",
                'data-live-search' => 'true',
            ],
        ]);

//        $this->add([
//            'type' => ObjectSelect::class,
//            'name' => 'garant-ecoleDoctorale',
//            'options' => [
//                'label' => "École doctorale <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
//                'label_options' => [ 'disable_html_escape' => true, ],
//                'target_class' => EcoleDoctorale::class,
//                'value_options' => $this->ecolesDoctorales,
//                'disable_inarray_validator' => true,
//            ],
//            'attributes' => [
//                'id' => 'garant-ecoleDoctorale',
//                'class' => 'selectpicker show-menu-arrow',
//                'title' => "Sélectionner l'école doctorale",
//                'data-live-search' => 'true',
//            ],
//        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'garant-qualite',
            'options' => [
                'label' => "Qualité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'value_options' => $this->qualiteService->getQualitesAsGroupOptions(),
                'empty_option' => "Sélectionner une qualité...",
            ],
            'attributes' => [
                'id' => 'garant-qualite',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            $name = 'garant-individu' => [
                'name' => $name,
                'required' => true,
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                ],
            ],
            $name = 'garant-etablissement' => [
                'name' => $name,
                'required' => true,
            ],
//            $name = 'garant-ecoleDoctorale' => [
//                'name' => $name,
//                'required' => true,
//            ],
            $name = 'garant-uniteRecherche' => [
                'name' => $name,
                'required' => true,
            ],
            $name = 'garant-qualite' => [
                'name' => $name,
                'required' => true,
            ],
        ];
    }
}