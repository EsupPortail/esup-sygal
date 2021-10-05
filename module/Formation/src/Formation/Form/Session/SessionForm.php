<?php

namespace Formation\Form\Session;

use Application\Entity\Db\TypeStructure;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect;
use Zend\Form\Element\Button;
use Zend\Form\Element\Number;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class SessionForm extends Form {
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    /** @var string */
    private $urlResponsable;

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
                'label' => "Libellé de la formation :",
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
                'label' => "Site de la formation :",
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
        $responsable = new SearchAndSelect('responsable', ['label' => "Responsable de la formation :"]);
        $responsable
            ->setAutocompleteSource($this->urlResponsable)
            ->setSelectionRequired(true)
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
                'value_options' => ['P' => 'Présentielle', 'D' => 'Distancielle'],
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
            'site'          => [ 'required' => true, ],
            'responsable'   => [ 'required' => true, ],
            'modalite'      => [ 'required' => true, ],
            'type'          => [ 'required' => true, ],
            'type_structure'=> [ 'required' => false, ],
            'taille_liste_principale'=> [ 'required' => true, ],
            'taille_liste_complementaire'=> [ 'required' => true, ],
        ]));
    }
}