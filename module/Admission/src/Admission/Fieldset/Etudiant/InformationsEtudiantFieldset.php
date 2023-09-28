<?php
namespace Admission\Fieldset\Etudiant;

use Individu\Entity\Db\Individu;
use Laminas\Filter\Digits;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Tel;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use UnicaenApp\Form\Element\SearchAndSelect;

class InformationsEtudiantFieldset extends Fieldset implements InputFilterProviderInterface
{
    private ?string $urlPaysNationalite = null;

    private ?string $urlNationalite = null;

    public function setUrlPaysNationalite(string $urlPaysNationalite): void
    {
        $this->$urlPaysNationalite = $urlPaysNationalite;
        $this->get('paysNationalite')->setAutocompleteSource($this->$urlPaysNationalite);
    }

    public function setUrlNationalite(string $urlNationalite): void
    {
        $this->$urlNationalite = $urlNationalite;
        $this->get('nationalite')->setAutocompleteSource($this->$urlNationalite);
    }

    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );

        $this->add(
            (new Radio('civilite'))
                ->setValueOptions([
                    null => "(Aucune)",
                    Individu::CIVILITE_M => Individu::CIVILITE_M,
                    Individu::CIVILITE_MME => Individu::CIVILITE_MME,
                ])
                ->setLabel("Civilité")
        );

        $this->add(
            (new Text('nomUsuel'))
                ->setLabel("Nom d'usage")
        );

        $this->add(
            (new Text('nomPatronymique'))
                ->setLabel("Nom")
        );

        $this->add(
            (new Text('prenom1'))
                ->setLabel("Prénom")
        );

        $this->add(
            (new Text('prenom2'))
                ->setLabel("Prénom 2 :")
        );

        $this->add(
            (new Text('prenom3'))
                ->setLabel("Prénom 3 :")
        );

        $this->add(
            (new Text('ine'))
                ->setLabel("Numéro I.N.E (Numéro inscrit au verso de la carte)")
        );

        $this->add(
            (new Text('numero_etudiant'))
                ->setLabel("Numéro Etudiant (pour les étudiants déjà inscrits à UNICAEN)")
        );

        $this->add(
            (new Email('email'))
                ->setLabel("Mél")
        );

        $this->add(
            (new Date('dateNaissance'))
                ->setLabel("Date de naissance :")
        );

        $paysNationalite = new SearchAndSelect('paysNationalite', ['label' => "Pays de naissance :"]);
        $paysNationalite
            ->setAutocompleteSource($this->urlPaysNationalite)
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'paysNationalite',
            ]);
        $this->add($paysNationalite);

        $this->add(
            (new Text('ville_naissance'))
                ->setLabel("Ville de naissance")
        );

        $this->add(
            (new Text('nationalite'))
                ->setLabel("Nationalité")
        );

        $nationalite = new SearchAndSelect('nationalite', ['label' => "Nationalité"]);
        $nationalite
            ->setAutocompleteSource($this->urlNationalite)
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nationalite',
            ]);
        $this->add($nationalite);

        $this->add(
            (new Hidden('code_pays_nationalite'))
        );

        $this->add(
            (new Hidden('code_nationalite'))
        );

        $this->add(
            (new Text('adresse_ligne1_etage'))
                ->setLabel("Adresse :")
        );

        $this->add(
            (new Text('adresse_ligne2_etage'))
                ->setLabel("Adresse :")
        );

        $this->add(
            (new Text('adresse_ligne3_batiment'))
                ->setLabel("Adresse (Bâtiment) :")
        );

        $this->add(
            (new Text('adresse_ligne3_bvoie'))
                ->setLabel("Adresse")
        );

        $this->add(
            (new Text('adresse_ligne4_complement'))
                ->setLabel("Adresse (complément) :")
        );

        $this->add(
            (new Number('adresse_code_postale'))
                ->setLabel("Code postal")
        );

        $this->add(
            (new Text('adresse_code_commune'))
                ->setLabel("Ville")
        );

        $this->add(
            (new Number('adresse_cp_ville_etranger'))
                ->setLabel("Code postal :")
        );

        $this->add(
            (new Tel('numero_telephone1'))
                ->setLabel("Numéro de téléphone")
        );

        $this->add(
            (new Tel('numero_telephone2'))
                ->setLabel("Numéro de téléphone :")
        );

        $this->add(
            (new Radio('situation_handicap'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non",
                ])
                ->setLabel("Êtes-vous en situation de handicap ?")
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
                'required' => false,
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