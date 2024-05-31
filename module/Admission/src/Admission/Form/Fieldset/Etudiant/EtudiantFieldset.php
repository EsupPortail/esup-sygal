<?php
namespace Admission\Form\Fieldset\Etudiant;

use Admission\Form\Fieldset\AdmissionBaseFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
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
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Tel;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use UnicaenApp\Form\Element\SearchAndSelect;

class EtudiantFieldset extends AdmissionBaseFieldset implements InputFilterProviderInterface
{
    private ?array $pays = null;
    private ?array $nationalites = null;

    public function setPays(array $paysAsOptions): void
    {
        $this->pays = $paysAsOptions;
        $this->get('paysNaissance')->setEmptyOption('Sélectionnez une option');
        $this->get('paysNaissance')->setValueOptions($this->pays);

        $this->get('adresseCodePays')->setEmptyOption('Sélectionnez une option');
        $this->get('adresseCodePays')->setValueOptions($this->pays);
    }

    public function setNationalites(array $nationalitesAsOptions): void
    {
        $this->nationalites = $nationalitesAsOptions;
        $this->get('nationalite')->setEmptyOption('Sélectionnez une option');
        $this->get('nationalite')->setValueOptions($this->nationalites);
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
        //Informations sur l'étudiant
        $this->add(
            (new Hidden('id'))
        );

        $this->add(
            (new Radio('sexe'))
                ->setValueOptions([
                    Individu::CIVILITE_M => Individu::CIVILITE_M,
                    Individu::CIVILITE_MME => Individu::CIVILITE_MME,
                ])
                ->setLabel("Civilité")
                ->setLabelAttributes(['data-after' => " / Civility"])
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Hidden('individu'))
        );

        $this->add(
            (new Text('nomUsuel'))
                ->setLabel("Nom d'usage")
                ->setAttributes(['readonly' => true])
                ->setLabelAttributes(['data-after' => " / Usage name"])
        );

        $this->add(
            (new Text('nomFamille'))
                ->setLabel("Nom")
                ->setLabelAttributes(['data-after' => " / Name"])
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Text('prenom'))
                ->setLabel("Prénom")
                ->setLabelAttributes(['data-after' => " / Firstname"])
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Hidden('prenom2'))
        );

        $this->add(
            (new Hidden('prenom3'))
        );

        $this->add(
            (new Text('ine'))
                ->setLabel("Numéro I.N.E (Numéro inscrit sur un relevé de notes de l'enseignement supérieur français)")
                ->setLabelAttributes(['data-after' => " / I.N.E number (number appearing on a French higher education transcript)"
                ])
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Email('courriel'))
                ->setLabel("Mail")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Date('dateNaissance'))
                ->setLabel("Date de naissance")
                ->setLabelAttributes(['data-after' => " / Birth date"])
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Select("paysNaissance"))
                ->setLabel("Pays de naissance")
                ->setLabelAttributes(['data-after' => " /   Country of origin"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "paysNaissance"
                ])
        );

        $this->add(
            (new Text('libelleCommuneNaissance'))
                ->setAttributes([
                    'id' => "libelleCommuneNaissance",
                    'placeholder' => "Entrez les deux premières lettres...",
                    'class' => 'selectpicker show-tick',
                ])
        );

        $this->add(
            (new Hidden('codeCommuneNaissance'))
                ->setAttributes([
                    'id' => "codeCommuneNaissance"
                ])
        );

        $this->add(
            (new Select("nationalite"))
                ->setLabel("Nationalité")
                ->setLabelAttributes(['data-after' => " /   Nationality"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "nationalite"
                ])
        );

        $this->add(
            (new Select("adresseCodePays"))
                ->setLabel("Pays")
                ->setLabelAttributes(['data-after' => " /   Country"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "adresseCodePays"
                ])
        );

        $this->add(
            (new Text('adresseLigne1Etage'))
                ->setLabel("Adresse (Étage)")
                ->setLabelAttributes(['data-after' => " / Address (Floor)"])
        );

        $this->add(
            (new Text('adresseLigne2Batiment'))
                ->setLabel("Adresse (Entrée/Bâtiment/Immeuble)")
                ->setLabelAttributes(['data-after' => " / Address (Entrance/Building/Block)"])
        );

        $this->add(
            (new Text('adresseLigne3voie'))
                ->setLabel("Adresse (Numéro – Libellé de la voie)")
                ->setLabelAttributes(['data-after' => " / Number - Street name"])
        );

        $this->add(
            (new Text('adresseLigne4Complement'))
                ->setLabel("Adresse (Complément)")
                ->setLabelAttributes(['data-after' => " / Address (Complement)"])
        );

        $this->add(
            (new Number('adresseCodePostal'))
                ->setLabel("Code postal")
                ->setLabelAttributes(['data-after' => " / Postal code"])
                ->setAttributes([
                    'id' => "adresseCodePostal",
                    'readonly' => true
                ])
        );

        $this->add(
            (new Text('adresseNomCommune'))
                ->setAttributes([
                    'id' => "adresseNomCommune",
                    'placeholder' => "Entrez les deux premières lettres...",
                ])
        );

        $this->add(
            (new Hidden('adresseCodeCommune'))
                ->setAttributes([
                    'id' => "adresseCodeCommune"
                ])
        );

        $this->add(
            (new Text('adresseCpVilleEtrangere'))
                ->setLabel("Ville étrangère (avec code postal)")
                ->setLabelAttributes(['data-after' => " / Foreign city (with postal code)"])
        );

        $this->add(
            (new Tel('numeroTelephone1'))
                ->setLabel("Numéro de téléphone")
                ->setLabelAttributes(['data-after' => " / Phone"])
        );

        $this->add(
            (new Tel('numeroTelephone2'))
                ->setLabel("Numéro de téléphone")
        );

        $this->add(
            (new Radio('situationHandicap'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non",
                ])
                ->setLabel("Êtes-vous en situation de handicap ?")
                ->setLabelAttributes(['data-after' => " / Have you a disability recognition"])
        );

        //Niveau d'Étude
        $this->add(
            (new Radio('niveauEtude'))
                ->setValueOptions([
                    1 => "Diplôme national tel que Master /  National degree such as Master",
                    2 => "Autre - à titre dérogatoire (Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire)
                    / Other - by way of derogation (Argument of the thesis director for the council of the doctoral school required)"
                ])
        );

        $this->add(
            (new Text('intituleDuDiplomeNational'))
                ->setLabel("Intitulé")
                ->setLabelAttributes(['data-after' => " / Title"])
        );

        $this->add(
            (new Select("anneeDobtentionDiplomeNational"))
                ->setLabel("Année d'obtention")
                ->setLabelAttributes(['data-after' => " / Year of graduation"])
                ->setValueOptions($this->generateYearOptions())
                ->setEmptyOption("Sélectionner une année")
        );

        $this->add(
            (new Text("etablissementDobtentionDiplomeNational"))
                ->setLabel("Etablissement d'obtention")
                ->setLabelAttributes(['data-after' => " / Awarding institution"])
        );

        $this->add(
            (new Radio('typeDiplomeAutre'))
                ->setValueOptions([
                    1 => "Diplôme obtenu à l'étranger / Diploma from abroad",
                    2 => "Diplôme français ne conférant pas le grade de master"
                ])
        );

        $this->add(
            (new Text('intituleDuDiplomeAutre'))
                ->setLabel("Intitulé")
                ->setLabelAttributes(['data-after' => " / Title"])
        );

        $this->add(
            (new Select("anneeDobtentionDiplomeAutre"))
                ->setLabel("Année d'obtention ")
                ->setLabelAttributes(['data-after' => "/ Year of graduation"])
                ->setValueOptions($this->generateYearOptions())
                ->setEmptyOption("Sélectionner une année")
        );

        $this->add(
            (new Text('etablissementDobtentionDiplomeAutre'))
                ->setLabel("Etablissement")
                ->setLabelAttributes(['data-after' => " / Awarding institution"])
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationEtudiant");
        $this->add($verificationFieldset);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            //Informations étudiant
            'sexe' => [
                'name' => 'sexe',
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
            'nomFamille' => [
                'name' => 'nomFamille',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'prenom' => [
                'name' => 'prenom',
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
                            'pattern' => '/^\d{10}[A-Z]$|^\d{9}[A-Z]{2}$/i',  // 10 chiffres et 1 lettre ou 9 chiffres et 2 lettres
                            'messages' => [
                                Regex::NOT_MATCH => "L'INE doit-être composé de 11 caractères, soit 10 chiffres et 1 lettre, soit 9 chiffres et 2 lettres",
                            ],
                        ],
                    ],
                ],
            ],
            'courriel' => [
                'name' => 'courriel',
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
            'paysNaissance' => [
                'name' => 'paysNaissance',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'nationalite' => [
                'name' => 'nationalite',
                'required' => false,
            ],
            'adresseLigne1Etage' => [
                'name' => 'adresseLigne1Etage',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseLigne2Etage' => [
                'name' => 'adresseLigne2Etage',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseLigne3Batiment' => [
                'name' => 'adresseLigne3Batiment',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseLigne3voie' => [
                'name' => 'adresseLigne3voie',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseLigne4Complement' => [
                'name' => 'adresseLigne4Complement',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseCodePostal' => [
                'name' => 'adresseCodePostal',
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
            'adresseCodeCommune' => [
                'name' => 'adresseCodeCommune',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseCodePays' => [
                'name' => 'adresseCodePays',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'adresseCpVilleEtrangere' => [
                'name' => 'adresseCpVilleEtrangere',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
            ],
            'numeroTelephone1' => [
                'name' => 'numeroTelephone1',
                'required' => false,
                'validators' => [
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^\d+$/',
                            'messages' => [
                                Regex::NOT_MATCH => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
                            ],
                        ],
                    ],
                ],
            ],
            'numeroTelephone2' => [
                'name' => 'numeroTelephone2',
                'required' => false,
                'filters' => [
                    ['name' => Digits::class],
                ],
            ],
            'situationHandicap' => [
                'name' => 'situationHandicap',
                'required' => false,
            ],
            //Niveau étude
            'niveauEtude' => [
                'name' => 'niveauEtude',
                'required' => false,
            ],
            'intituleDuDiplomeNational' => [
                'name' => 'intituleDuDiplomeNational',
                'required' => false,
            ],
            'anneeDobtentionDiplomeNational' => [
                'name' => 'anneeDobtentionDiplomeNational',
                'required' => false,
            ],
            'etablissementDobtentionDiplomeNational' => [
                'name' => 'etablissementDobtentionDiplomeNational',
                'required' => false,
            ],
            'typeDiplomeAutre' => [
                'name' => 'typeDiplomeAutre',
                'required' => false,
            ],
            'intituleDuDiplomeAutre' => [
                'name' => 'intituleDuDiplomeAutre',
                'required' => false,
            ],
            'anneeDobtentionDiplomeAutre' => [
                'name' => 'anneeDobtentionDiplomeAutre',
                'required' => false,
            ],
            'etablissementDobtentionDiplomeAutre' => [
                'name' => 'etablissementDobtentionDiplomeAutre',
                'required' => false,
            ],
        ];
    }
}