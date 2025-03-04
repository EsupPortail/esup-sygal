<?php

namespace HDR\Fieldset\Generalites;

use Application\Entity\Db\VersionDiplome;
use Application\Service\VersionDiplome\VersionDiplomeServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\Etablissement;
use Structure\Form\Element\ProvidesEtablissementSelectTrait;
use UnicaenApp\Form\Element\SearchAndSelect;
use UnicaenApp\Form\Element\Select2;
use UnicaenApp\Service\EntityManagerAwareTrait;

class GeneralitesFieldset extends Fieldset implements InputFilterProviderInterface, ElementPrepareAwareInterface
{
    use EntityManagerAwareTrait;
    use VersionDiplomeServiceAwareTrait;

    use ProvidesEtablissementSelectTrait;

    private ?Etablissement $etablissement = null;

    /**
     * Spécifie le seul établissement sélectionnable et pré-sélectionné.
     */
    public function setEtablissement(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    private string $urlAutocompleteIndividu;

    public function setUrlAutocompleteIndividu(string $urlAutocompleteIndividu): void
    {
        $this->urlAutocompleteIndividu = $urlAutocompleteIndividu;
    }

    private string $urlAutocompleteEtablissement;

    public function setUrlAutocompleteEtablissement(string $urlAutocompleteEtablissement): void
    {
        $this->urlAutocompleteEtablissement = $urlAutocompleteEtablissement;
    }

    public function prepareElement(FormInterface $form): void
    {
        /** @var HDR $hdr */
        $hdr = $this->getObject();
        $this->get('candidat')->setAttribute('readonly', $hdr->getCandidat());

        // seul établissement sélectionnable et pré-sélectionné, si fourni.
        if ($this->etablissement !== null) {
            /** @var Select $etablissementSelect */
            $etablissementSelect = $this->get('etablissement');
            $etablissementSelect
                ->setValueOptions([$this->etablissement])
                ->setEmptyOption(null);
        }

        parent::prepareElement($form);
    }

    public function init(): void
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            $this->createEtablissementSelect('etablissement')
                ->setLabel("Établissement d'inscription <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
        );

        $doctorant = new SearchAndSelect('candidat', [
            'label' => "Candidat·e <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
            'label_options' => [ 'disable_html_escape' => true, ],
            ]);
        $doctorant
            ->setAutocompleteSource($this->urlAutocompleteIndividu)
            ->setRequired()
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'candidat',
                'placeholder' => "Rechercher un·e candidat·e ...",
            ]);
        $this->add($doctorant);

        $this->add([
            'type' => Select2::class,
            'name' => 'versionDiplome',
            'options' => [
                'label' => "Version de diplôme :",
                'value_options' => $this->getVersionsDiplomesValueOptions(),
                'empty_option' => "Aucune sélection",
            ],
            'attributes' => [
                'id' => 'versionDiplome',
                'placeholder' => "Sélectionner la version de diplôme...",
            ]
        ]);

        $this->add(
            (new Text('cnu'))
                ->setLabel("CNU : ")
        );

        $this->add([
            'type' => Radio::class,
            'name' => 'confidentialite',
            'options' => [
                'value_options' => [
                    0 => "Non confidentielle ",
                    1 => "Confidentielle ",
                ],
            ],
            'attributes' => [
                'id' => 'confidentialite',
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dateFinConfidentialite',
            'options' => [
                'label' => "Date de fin de confidentialité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> : ",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'fin-confidentialite',
            ],
        ]);

        $this->add(
            (new Date("datePremiereInscription"))
                ->setLabel("Date de première inscription :")
        );

        $this->add([
            'type' => Date::class,
            'name' => 'dateAbandon',
            'options' => [
                'label' => "Date d'abandon : ",
            ],
            'attributes' => [
                'id' => 'date-abandon',
            ],
        ]);

        $this->add(
            (new Select("resultat"))
                ->setEmptyOption("Sélectionnez une option")
                ->setValueOptions([
                    HDR::RESULTAT_AJOURNE => HDR::$resultatsLibellesLongs[HDR::RESULTAT_AJOURNE],
                    HDR::RESULTAT_ADMIS => HDR::$resultatsLibellesLongs[HDR::RESULTAT_ADMIS],
                ])
                ->setLabel("Résultat : ")
                ->setAttributes([
                    'class' => 'selectpicker show-tick',
                    'id' => "resultat"
                ])
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'etablissement' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner un établissement.',
                            ],
                        ],
                    ],
                ],
            ],
            'datePremiereInscription' => [
                'required' => false,
            ],
            // NB : ne pas déclarer le SearchAndSelect ici, sinon pas de validation correcte !
//            'candidat' => [
//                'required' => true,
//            ],
            'versionDiplome' => [
                'required' => false,
            ],
            'cnu' => [
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
                                if ((isset($context['confidentialite']) && $context['confidentialite'] === "1") && empty($context['dateFinConfidentialite'])) {
                                    return false;
                                }
                                return true;
                            },
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
            'dateFinConfidentialite' => [
                'required' => false,
            ],
            'dateAbandon' => [
                'required' => false,
            ],
            'resultat' => [
                'required' => false,
            ],
        ];
    }

    /**
     * @return string[]
     */
    protected function getVersionsDiplomesValueOptions(): array
    {
        if ($this->etablissement !== null) {
            $etablissementsValueOptions = array_map(
                fn(VersionDiplome $vdi) => [
                    'value' => $vdi->getId(),
                    'text' => $vdi->getLibelleLong(),
                    'attributes' => [
                        'class' => 'etablissement ' . $vdi->getEtablissement()->getSourceCode(),
                    ],
                ],
                $this->versionDiplomeService->getRepository()->findForEtablissement($this->etablissement)
            );
        } else {
            $etablissementsValueOptions = array_map(
                fn(VersionDiplome $vdi) => [
                    'value' => $vdi->getId(),
                    'text' => $vdi->getEtablissement()->getSigle() . ' : ' . $vdi->getLibelleLong(),
                    'attributes' => [
                        'class' => 'etablissement ' . $vdi->getEtablissement()->getSourceCode(),
                    ],
                ],
                $this->versionDiplomeService->getRepository()->findAll()
            );
        }
        return $etablissementsValueOptions;
    }
}