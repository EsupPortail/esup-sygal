<?php
namespace Admission\Fieldset\Inscription;

use Laminas\Filter\Digits;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Radio;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use UnicaenApp\Form\Element\SearchAndSelect;

class SpecifitesEnvisageesFieldset extends Fieldset implements InputFilterProviderInterface
{
    private $dateDuJourFormatee;
    private $dateDans10Ans;

    private ?string $urlPaysNationalite = null;

    public function setUrlPaysNationalite(string $urlPaysNationalite): void
    {
        $this->$urlPaysNationalite = $urlPaysNationalite;
        $this->get('pays_co-tutelle')->setAutocompleteSource($this->$urlPaysNationalite);
    }

    public function __construct($name = null)
    {
        parent::__construct($name);

        // Obtenez la date actuelle au format "YYYY-MM-DD"
        $aujourdHui = new \DateTime();
        $this->dateDuJourFormatee = $aujourdHui->format('Y-m-d');
        // Ajoutez 10 ans à la date actuelle
        $aujourdHui->add(new \DateInterval('P10Y'));

        // Formatez la date résultante au format "YYYY-MM-DD"
        $this->dateDans10Ans = $aujourdHui->format('Y-m-d');
    }

    public function init()
    {
        $this->add(
            (new Radio('confidentialite'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Confidentialité souhaitée")
        );

        $this->add(
            (new Date('date_confidentialité'))
                ->setLabel("Date de fin de confidentialité souhaitée (limitée à 10 ans)")
                ->setAttributes([
                    'min'  => $this->dateDuJourFormatee,
                    'max'  => $this->dateDans10Ans,
                    'step' => '1', // days; default step interval is 1 day
                ])
        );

        $this->add(
            (new Radio('co_tutelle'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Cotutelle envisagée")
        );

        $paysNationalite = new SearchAndSelect('pays_co-tutelle', ['label' => "Pays concerné"]);
        $paysNationalite
            ->setAutocompleteSource($this->urlPaysNationalite)
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'pays_co-tutelle',
            ]);
        $this->add($paysNationalite);

        $this->add(
            (new Radio('co_encadrement'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-encadrement envisagé")
        );

        $this->add(
            (new Radio('co-direction'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-direction demandée")
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'civilite' => [
                'name' => 'civilite',
                'required' => false,
            ],
            'nomUsuel' => [
                'name' => 'nomUsuel',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'nomPatronymique' => [
                'name' => 'nomPatronymique',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'prenom1' => [
                'name' => 'prenom1',
                'required' => true,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'prenom2' => [
                'name' => 'prenom2',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'prenom3' => [
                'name' => 'prenom3',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'ine' => [
                'name' => 'ine',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 11,  // 11 caractères
                            'max' => 11,
                        ],
                    ],
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => '/^\d{10}[A-Z]$/i',  // 10 chiffres et 1 lettre
                        ],
                    ],
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => '/^\d{9}[A-Z]{2}$/i',  // 9 chiffres et 2 lettres
                        ],
                    ],
                ],
            ],
            'email' => [
                'name' => 'email',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'dateNaissance' => [
                'name' => 'dateNaissance',
                'required' => false,
            ],
            'paysNationalite' => [
                'name' => 'paysNationalite',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_ligne1_etage' => [
                'name' => 'adresse_ligne1_etage',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_ligne2_etage' => [
                'name' => 'adresse_ligne2_etage',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_ligne3_batiment' => [
                'name' => 'adresse_ligne3_batiment',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_ligne3_bvoie' => [
                'name' => 'adresse_ligne3_bvoie',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_ligne4_complement' => [
                'name' => 'adresse_ligne4_complement',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_code_postale' => [
                'name' => 'adresse_code_postale',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
                'validators' => [
                    [
                        'name' => GreaterThan::class,
                        'options' => [
                            'min' => 0,
                            'inclusive' => true, // Inclure 0 comme une valeur positive
                            'message' => 'Le code postal doit être une valeur positive.',
                        ],
                    ],
                ],
            ],
            'adresse_code_commune' => [
                'name' => 'adresse_code_commune',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresse_cp_ville_etranger' => [
                'name' => 'adresse_cp_ville_etranger',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
            ],
            'numero_telephone1' => [
                'name' => 'numero_telephone1',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
            ],
            'numero_telephone2' => [
                'name' => 'numero_telephone2',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
            ],
            'situation_handicap' => [
                'name' => 'situation_handicap',
                'required' => false,
            ],
        ];
    }
}