<?php

namespace These\Fieldset\Generalites;

use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\NotEmpty;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Fieldset\AnneeUnivInscription\AnneeUnivInscriptionFieldset;
use These\Fieldset\TitreAcces\TitreAccesFieldset;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;
use UnicaenApp\Form\Element\SearchAndSelect;

class GeneralitesFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use DisciplineServiceAwareTrait;

    private string $urlDoctorant;
    private string $domainesHal;

    /**
     * @param string|null $urlDoctorant
     */
    public function setUrlDoctorant(?string $urlDoctorant): void
    {
        $this->urlDoctorant = $urlDoctorant;
    }

    // Méthode pour générer les options d'année
    protected function generateYearOptions() : array
    {
        $currentYear = date('Y');
        $options = [];
        for ($year = $currentYear; $year >= $currentYear - 50; $year--) {
            $options[$year] = $year;
        }
        return $options;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $etablissementsOptions = $this->etablissementService->getEtablissementInscriptionAsOption();
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

        $domainesHalFieldset = $this->getFormFactory()->getFormElementManager()->get(DomaineHalFieldset::class);
        $this->add($domainesHalFieldset, ['name' => 'domaineHal']);

        $this->add([
            'type' => Radio::class,
            'name' => 'confidentialite',
            'options' => [
                'label' => "Confidentialité de la thèse :",
                'value_options' => [
                    0 => "These non confidentielle ",
                    1 => "Thèse confidentielle ",
                ],
            ],
            'attributes' => [
                'id' => 'confidentialite',
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'fin-confidentialite',
            'options' => [
                'label' => "Date de fin de confidentialité : ",
            ],
            'attributes' => [
                'id' => 'fin-confidentialite',
            ],
        ]);

        $this->add(
            (new Date("datePremiereInscription"))
                ->setLabel("Date de première inscription")
        );

        $titreAccesTheseFieldset = $this->getFormFactory()->getFormElementManager()->get(TitreAccesFieldset::class);
        $this->add($titreAccesTheseFieldset, ['name' => 'titreAcces']);
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
            'datePremiereInscription' => [
                'required' => false,
            ],
            // NB : ne pas déclarer le SearchAndSelect ici, sinon pas de validation correcte !
//            'doctorant' => [
//                'required' => true,
//            ],
            'discipline' => [
                'required' => false,
            ],
            'domaineHal' => [
                'name' => 'domaineHal',
                'required' => false,
            ],
            'confidentialite' => [
                'name' => 'confidentialite',
                'required' => false,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => "La date de fin de confidentialité est requise",
                            ],
                            'callback' => function ($value, $context = []) {
                                if ((isset($context['confidentialite']) && $context['confidentialite'] === "1") && empty($context['fin-confidentialite'])) {
                                    return false;
                                }
                                return true;
                            },
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
            'fin-confidentialite' => [
                'name' => 'fin-confidentialite',
                'required' => false,
            ],
            'titreAcces' => [
                'name' => 'titreAcces',
                'required' => false,
            ],
        ];
    }
}