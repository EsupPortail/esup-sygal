<?php

namespace Formation\Form\Session;

use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Laminas\Form\Element\DateTime;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Form\Element\Date;
use UnicaenApp\Form\Element\SearchAndSelect;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class SessionForm extends Form {
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    private ?string $urlResponsable = null;

    /**
     * @param string $urlResponsable
     * @return SessionForm
     */
    public function setUrlResponsable(string $urlResponsable): SessionForm
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
                'label' => "Libellé de la formation <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'libelle',
                'class' => 'required',
                'readonly' => true,
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

        //site
        $this->add([
            'type' => Select::class,
            'name' => 'site',
            'options' => [
                'label' => "Site de la formation <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'empty_option' => "Aucun établissement",
                'value_options' => $this->getEtablissementService()->getEtablissementInscriptionAsOption(),
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //responsable
        $responsable = new SearchAndSelect('responsable', ['label' => "Responsable de la formation <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :"]);
        $responsable
            ->setAutocompleteSource($this->urlResponsable)
            ->setSelectionRequired()
            ->setAttributes([
                'id' => 'responsable',
                'placeholder' => "Nom du responsable ...",
            ]);
        $responsable->setLabelOption('disable_html_escape', true);
        $this->add($responsable);
        //modalité
        $this->add([
            'type' => Select::class,
            'name' => 'modalite',
            'options' => [
                'label' => "Modalité <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'empty_option' => "Non précisée",
                'value_options' => HasModaliteInterface::MODALITES,
            ],
            'attributes' => [
                'id' => 'modalite',
                'class' => 'show-tick',
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
                'class' => 'show-tick',
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
                'class' => 'show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //liste principale
        $this->add([
            'type' => Number::class,
            'name' => 'taille_liste_principale',
            'options' => [
                'label' => "Effectif de la liste principale <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
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
                'label' => "Effectif de la liste complémentaire <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'taille_liste_complementaire',
            ],
        ]);

        //jour
        $this->add([
            'name' => 'date_fermeture_inscription',
            'type' => Date::class,
            'options' => [
                'label' => "Date de fermeture des inscriptions <span class='icon icon-info text-info'  title='La date est seulement informative'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'd/m/Y',
            ],
            'attributes' => [
                'id' => 'date_fermeture_inscription',
                'class' => 'form-control',
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
            'site'          => [ 'required' => true, ],
            'responsable'   => [ 'required' => true, ],
            'modalite'      => [ 'required' => true, ],
            'type'          => [ 'required' => true, ],
            'type_structure'=> [ 'required' => false, ],
            'taille_liste_principale'=> [ 'required' => true, ],
            'taille_liste_complementaire'=> [ 'required' => true, ],
            'date_fermeture_inscription'=> [ 'required' => false, ],
        ]));
    }
}