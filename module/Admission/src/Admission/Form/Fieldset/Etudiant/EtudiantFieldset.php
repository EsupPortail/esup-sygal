<?php
namespace Admission\Form\Fieldset\Etudiant;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etat;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Individu\Entity\Db\Individu;
use Laminas\Filter\Digits;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Tel;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use UnicaenApp\Form\Element\SearchAndSelect;

class EtudiantFieldset extends Fieldset implements InputFilterProviderInterface
{
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
            (new Radio('civilite'))
                ->setValueOptions([
                    Individu::CIVILITE_M => Individu::CIVILITE_M,
                    Individu::CIVILITE_MME => Individu::CIVILITE_MME,
                ])
                ->setLabel("Civilité")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Hidden('individu'))
        );

        $this->add(
            (new Text('nomUsuel'))
                ->setLabel("Nom d'usage:")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Text('nomFamille'))
                ->setLabel("Nom :")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Text('prenom'))
                ->setLabel("Prénom :")
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
                ->setLabel("Numéro I.N.E (Numéro inscrit au verso de la carte)")
        );

        $this->add(
            (new Text('numeroEtudiant'))
                ->setLabel("Numéro Etudiant (pour les étudiants déjà inscrits à UNICAEN)")
        );

        $this->add(
            (new Email('courriel'))
                ->setLabel("Mail")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Date('dateNaissance'))
                ->setLabel("Date de naissance :")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Hidden('paysNaissanceId'))
        );

        $this->add(
            (new Text('paysNaissance'))
                ->setLabel("Pays de naissance :")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Text('villeNaissance'))
                ->setLabel("Ville de naissance")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Text('codePaysNaissance'))
                ->setLabel("Nationalité")
        );

        $this->add(
            (new Hidden('nationaliteId'))
        );

        $this->add(
            (new Text('nationalite'))
                ->setLabel("Nationalité")
                ->setAttributes(['readonly' => true])
        );

        $this->add(
            (new Hidden('codePaysNaissance'))
        );

        $this->add(
            (new Hidden('codeNationalite'))
        );

        $this->add(
            (new Text('adresseCodePays'))
                ->setLabel("Adresse")
        );

        $this->add(
            (new Text('adresseLigne1Etage'))
                ->setLabel("Adresse")
        );

        $this->add(
            (new Text('adresseLigne2Etage'))
                ->setLabel("Adresse")
        );

        $this->add(
            (new Text('adresseLigne3Batiment'))
                ->setLabel("Adresse (Bâtiment)")
        );

        $this->add(
            (new Text('adresseLigne3Bvoie'))
                ->setLabel("Adresse")
        );

        $this->add(
            (new Text('adresseLigne4Complement'))
                ->setLabel("Adresse (complément)")
        );

        $this->add(
            (new Number('adresseCodePostal'))
                ->setLabel("Code postal")
        );

        $this->add(
            (new Text('adresseCodeCommune'))
                ->setLabel("Ville")
        );

        $this->add(
            (new Number('adresseCpVilleEtrangere'))
                ->setLabel("Code postal")
        );

        $this->add(
            (new Tel('numeroTelephone1'))
                ->setLabel("Numéro de téléphone")
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
        );

        //Niveau d'Étude
        $this->add(
            (new Radio('niveauEtude'))
                ->setValueOptions([
                    1 => "Diplôme national tel que Master",
                    2 => "Autre - à titre dérogatoire (Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire)"
                ])
        );

        $this->add(
            (new Text('intituleDuDiplomeNational'))
                ->setLabel("Intitulé")
        );

        $this->add(
            (new Select("anneeDobtentionDiplomeNational"))
                ->setLabel("Année d'obtention")
                ->setValueOptions($this->generateYearOptions())
        );

        $this->add(
            (new Text("etablissementDobtentionDiplomeNational"))
                ->setLabel("Etablissement d'obtention")
        );

        $this->add(
            (new Radio('typeDiplomeAutre'))
                ->setValueOptions([
                    1 => "Diplôme obtenu à l'étranger",
                    2 => "Diplôme français ne conférant pas le grade de master"
                ])
        );

        $this->add(
            (new Text('intituleDuDiplomeAutre'))
                ->setLabel("Intitulé")
        );

        $this->add(
            (new Select("anneeDobtentionDiplomeAutre"))
                ->setLabel("Année d'obtention")
                ->setValueOptions($this->generateYearOptions())
        );

        $this->add(
            (new Text('etablissementDobtentionDiplomeAutre'))
                ->setLabel("Etablissement")
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationEtudiant");
        $this->add($verificationFieldset);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            //Informations étudiant
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
            'adresseLigne3Bvoie' => [
                'name' => 'adresseLigne3Bvoie',
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