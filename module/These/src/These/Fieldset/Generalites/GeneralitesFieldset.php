<?php

namespace These\Fieldset\Generalites;

use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect;

class GeneralitesFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use DisciplineServiceAwareTrait;

    private string $urlDoctorant;

    /**
     * @param string|null $urlDoctorant
     */
    public function setUrlDoctorant(?string $urlDoctorant): void
    {
        $this->urlDoctorant = $urlDoctorant;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $etablissementsOptions = $this->etablissementService->getEtablissementsInscriptionsAsOptions();
        $this->add([
            'type' => Select::class,
            'name' => 'etablissement',
            'options' => [
                'label' => "Établissement d'inscription : *",
                'value_options' => $etablissementsOptions,
                'empty_option' => "Sélectionner l'établissement",
            ],
            'attributes' => [
                'id' => 'etablissement',
                'value' => count($etablissementsOptions) === 1 ? key($etablissementsOptions) : null,
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'titre',
            'options' => [
                'label' => "Titre de la thèse : *",
            ],
        ]);

        $doctorant = new SearchAndSelect('doctorant', ['label' => "Doctorant·e : *"]);
        $doctorant
            ->setAutocompleteSource($this->urlDoctorant)
            ->setRequired(true)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'doctorant',
                'placeholder' => "Rechercher un·e doctorant·e ...",
            ]);
        $this->add($doctorant);

        $this->add([
            'type' => Select::class,
            'name' => 'discipline',
            'options' => [
                'label' => "Discipline :",
                'value_options' => $this->disciplineService->getDisciplinesAsOptions(),
                'empty_option' => "Sélectionner une discipline",
            ],
            'attributes' => [
                'id' => 'discipline',
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
            'etablissement' => [
                'required' => true,
            ],
            'titre' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 4096
                        ],
                    ],
                ],
            ],
            // NB : ne pas déclarer le SearchAndSelect ici, sinon pas de validation correcte !
//            'doctorant' => [
//                'required' => true,
//            ],
            'discipline' => [
                'required' => false,
            ],
        ];
    }
}