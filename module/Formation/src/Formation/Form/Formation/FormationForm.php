<?php

namespace Formation\Form\Formation;

use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Formation\Service\Module\ModuleServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class FormationForm extends Form {
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;
    use ModuleServiceAwareTrait;

    private string $urlResponsable;

    /**
     * @param string $urlResponsable
     * @return FormationForm
     */
    public function setUrlResponsable(string $urlResponsable): FormationForm
    {
        $this->urlResponsable = $urlResponsable;
        return $this;
    }

    public function init()
    {
        //titre
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé de la formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'libelle',
                'class' => 'required',
            ],
        ]);
        //description
        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Complément d'information à propos de la séance :",
            ],
            'attributes' => [
                'id' => 'description',
                'class' => 'tinymce',
            ],
        ]);
        //objectif
        $this->add([
            'type' => Textarea::class,
            'name' => 'objectif',
            'options' => [
                'label' => "Objectif de la formation :",
            ],
            'attributes' => [
                'id' => 'objectif',
                'class' => 'tinymce',
            ],
        ]);
        //programme
        $this->add([
            'type' => Textarea::class,
            'name' => 'programme',
            'options' => [
                'label' => "Programme de la formation :",
            ],
            'attributes' => [
                'id' => 'programme',
                'class' => 'tinymce',
            ],
        ]);
        //lien
        $this->add([
            'type' => Text::class,
            'name' => 'lien',
            'options' => [
                'label' => "Lien vers la fiche de la formation :",
            ],
            'attributes' => [
                'id' => 'lien',
            ],
        ]);
        //site
        $this->add([
            'type' => Select::class,
            'name' => 'module',
            'options' => [
                'label' => "Module associé à la formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'empty_option' => "Aucun module",
                'value_options' => $this->getModuleService()->getModulesAsOptions(),
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);

        //site
        $this->add([
            'type' => Select::class,
            'name' => 'site',
            'options' => [
                'label' => "Établissement organisateur :",
                'empty_option' => "Aucun établissement",
                'value_options' => $this->getEtablissementService()->getEtablissementInscriptionAsOption(),
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //responsable
        $responsable = new SearchAndSelect('responsable', ['label' => "Responsable du module de formation :"]);
        $responsable
            ->setAutocompleteSource($this->urlResponsable)
            ->setSelectionRequired()
            ->setAttributes([
                'id' => 'responsable',
                'placeholder' => "Nom du responsable ...",
            ]);
        $this->add($responsable);
        //modalité
        $this->add([
            'type' => Select::class,
            'name' => 'modalite',
            'options' => [
                'label' => "Modalité :",
                'empty_option' => "Non précisée",
                'value_options' =>
                    HasModaliteInterface::MODALITES,
            ],
            'attributes' => [
                'id' => 'modalite',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //type
        $this->add([
            'type' => Select::class,
            'name' => 'type',
            'options' => [
                'label' => "Type de formation :",
                'empty_option' => "Non précisée",
                'value_options' => ['T' => 'Transversale', 'S' => 'Spécifique'],
            ],
            'attributes' => [
                'id' => 'type',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //type
        $this->add([
            'type' => Select::class,
            'name' => 'type_structure',
            'options' => [
                'label' => "Structure associé à la formation :",
                'empty_option' => "Non précisée",
                'value_options' => $this->getStructureService()->getStructuresFormationsAsOptions(),
            ],
            'attributes' => [
                'id' => 'type_structure',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //liste principale
        $this->add([
            'type' => Number::class,
            'name' => 'taille_liste_principale',
            'options' => [
                'label' => "Effectif de la liste principale :",
            ],
            'attributes' => [
                'id' => 'taille_liste_principale',
            ],
        ]);
        //liste complementaire
        $this->add([
            'type' => Number::class,
            'name' => 'taille_liste_complementaire',
            'options' => [
                'label' => "Effectif de la liste complémentaire :",
            ],
            'attributes' => [
                'id' => 'taille_liste_complementaire',
            ],
        ]);

        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',

            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'libelle'       => [ 'required' => true, ],
            'description'   => [ 'required' => false, ],
            'lien'          => [ 'required' => false, ],
            'module'        => [ 'required' => true, ],
            'site'          => [ 'required' => false, ],
            'responsable'   => [ 'required' => false, ],
            'modalite'      => [ 'required' => false, ],
            'type'          => [ 'required' => false, ],
            'type_structure'=> [ 'required' => false, ],
            'taille_liste_principale'=> [ 'required' => false, ],
            'taille_liste_complementaire'=> [ 'required' => false, ],
            'objectif'=> [ 'required' => false, ],
            'programme'=> [ 'required' => false, ],
        ]));
    }
}