<?php

namespace These\Fieldset\Structures;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class StructuresFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function getUnitesRecherchesAsOptions() : array
    {
        $unites = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle', false);

        $options = [];
        foreach ($unites as $unite) {
            $sigle = $unite->getStructure() && $unite->getStructure()->getSigle() ? " (".$unite->getStructure()->getSigle().")" : null;
            $options[$unite->getId()] = $unite->getStructure()->getLibelle() . $sigle;
        }
        return $options;
    }

    private function getEcolesDoctoralsAsOptions() : array
    {
        $ecoles = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);

        $options = [];
        foreach ($ecoles as $ecole) {
            $sigle = $ecole->getStructure() && $ecole->getStructure()->getSigle() ? " (".$ecole->getStructure()->getSigle().")" : null;
            $options[$ecole->getId()] = $ecole->getStructure()->getLibelle() . $sigle;
        }
        return $options;
    }

    public function getEtablissementsAsOptions() : array
    {
        $etablissements = $this->etablissementService->getRepository()->findAll();

        $options = [];
        foreach ($etablissements as $etablissement) {
            $sigle = $etablissement->getStructure() && $etablissement->getStructure()->getSigle() ? " (".$etablissement->getStructure()->getSigle().")" : null;
            $options[$etablissement->getId()] = $etablissement->getStructure()->getLibelle() . $sigle;
        }
        return $options;
    }

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
                'value_options' => $this->getEtablissementsAsOptions(),
                'empty_option' => "Sélectionner l'établissement",
            ],
            'attributes' => [
                'id' => 'etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
                'disable_html_escape' => false,
            ]
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'unite-recherche',
            'options' => [
                'label' => "Unité de recherche :",
                'value_options' => $this->getUnitesRecherchesAsOptions(),
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
                'value_options' => $this->getEcolesDoctoralsAsOptions(),
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