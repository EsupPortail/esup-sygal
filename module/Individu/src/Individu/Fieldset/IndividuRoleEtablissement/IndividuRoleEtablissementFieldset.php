<?php

namespace Individu\Fieldset\IndividuRoleEtablissement;

use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\SearchAndSelect;

class IndividuRoleEtablissementFieldset extends Fieldset implements InputFilterProviderInterface
{
    private string $urlEtablissement;

    public function setUrlEtablissement(string $urlEtablissement): void
    {
        $this->urlEtablissement = $urlEtablissement;
    }

    public function init(): void
    {
        $etablissement = new SearchAndSelect('etablissement', ['label' => "Établissement d'inscription :"]);
        $etablissement
            ->setAutocompleteSource($this->urlEtablissement)
            ->setRequired()
            ->setSelectionRequired()
            ->setAttributes([
                'id' => uniqid('etablissement-'),
                'placeholder' => "Entrez 2 caractères au moins...",
            ]);
        $this->add($etablissement);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'etablissement' => [
                'required' => true,
            ],
        ];
    }
}