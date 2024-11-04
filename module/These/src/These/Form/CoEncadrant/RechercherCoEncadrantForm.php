<?php

namespace These\Form\CoEncadrant;

use Application\Utils\FormUtils;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use UnicaenApp\Form\Element\SearchAndSelect;

class RechercherCoEncadrantForm extends Form
{

    private ?string $urlCoEncadrant = null;
    private ?string $urlEtablissement = null;

    public function setUrlCoEncadrant(string $urlCoEncadrant): void
    {
        $this->urlCoEncadrant = $urlCoEncadrant;
        $this->get('co-encadrant')->setAutocompleteSource($this->urlCoEncadrant);
    }

    public function setUrlEtablisssement(string $urlEtablissement): void
    {
        $this->urlEtablissement = $urlEtablissement;
        $this->get('etablissement')->setAutocompleteSource($this->urlEtablissement);
    }

    public function init(): void
    {
        /**
         * SearchAndSelect sur les Individus de la structure fictives
         */
        $coEncadrant = new SearchAndSelect('co-encadrant', [
            'label' => "Co-encadrant·e <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
            'label_options' => [ 'disable_html_escape' => true, ]
        ]);
        $coEncadrant
            ->setAutocompleteSource($this->urlCoEncadrant)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'co-encadrant',
                'placeholder' => "Sélectionner un·e co-encadrant·e ... ",
            ]);
        $this->add($coEncadrant);

        $coEncadrant = new SearchAndSelect('etablissement', ['label' => "Établissement (laisser vide si établissement d'inscription) :"]);
        $coEncadrant
            ->setAutocompleteSource($this->urlEtablissement)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'etablissement',
                'placeholder' => "Sélectionner un établissement ... ",
            ]);
        $this->add($coEncadrant);

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'co-encadrant' => ['required' => true],
            'etablissement' => ['required' => false],
        ]));
    }
}