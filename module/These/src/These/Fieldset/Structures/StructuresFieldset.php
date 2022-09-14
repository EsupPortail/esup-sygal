<?php

namespace These\Fieldset\Structures;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class StructuresFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'etablissement',
            'options' => [
                'label' => "Établissement :",
                'value_options' => $this->etablissementService->getEtablissementsInscriptionsAsOptions(),
                'empty_option' => "Sélectionner l'établissement",
            ],
            'attributes' => [
                'id' => 'etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'unite-recherche',
            'options' => [
                'label' => "Unité de recherche :",
                'value_options' => $this->uniteRechercheService->getUnitesRecherchesAsOptions(),
                'empty_option' => "Sélectionner l'unité de recherche",
            ],
            'attributes' => [
                'id' => 'unite-recherche',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'ecole-doctorale',
            'options' => [
                'label' => "École doctorale :",
                'value_options' => $this->ecoleDoctoraleService->getEcolesDoctoralsAsOptions(),
                'empty_option' => "Sélectionner l'école doctorale",
            ],
            'attributes' => [
                'id' => 'ecole-doctorale',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'unite-recherche' => [
                'name' => 'unite-recherche',
                'required' => false,
            ],
            'ecole-doctorale' => [
                'name' => 'ecole-doctorale',
                'required' => false,
            ],
            'etablissement' => [
                'name' => 'etablissement',
                'required' => false,
            ],
        ];
    }
}