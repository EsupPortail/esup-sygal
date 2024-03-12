<?php
namespace Admission\Form\Fieldset\Inscription;

use Admission\Form\Fieldset\AdmissionBaseFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\SearchAndSelect;

class InscriptionFieldset extends AdmissionBaseFieldset implements InputFilterProviderInterface
{
    //Informations Inscription
    private ?string $urlIndividuThese = null;

    /** @var array */
    private $composantesEnseignement = null;

    /** @var array */
    private $ecolesDoctorales = null;

    /** @var array */
    private $unitesRecherche = null;

    /** @var array */
    private $etablissementInscription = null;
    private $specialites = null;

    /** @var array $qualites  */
    private $qualites = null;


    public function setUrlIndividuThese(string $urlIndividuThese): void
    {
        $this->urlIndividuThese = $urlIndividuThese;
        $this->get('nomDirecteurThese')->setAutocompleteSource($this->urlIndividuThese);
        $this->get('prenomDirecteurThese')->setAutocompleteSource($this->urlIndividuThese);

        $this->get('nomCodirecteurThese')->setAutocompleteSource($this->urlIndividuThese);
        $this->get('prenomCodirecteurThese')->setAutocompleteSource($this->urlIndividuThese);
    }

    public function setSpecialites(array $specialites): void
    {
        $this->specialites = $specialites;
        $this->get('specialiteDoctorat')->setEmptyOption('Sélectionnez une option');
        $this->get('specialiteDoctorat')->setValueOptions($this->specialites);
    }

    public function setEcolesDoctorales(array $ecolesDoctorales): void
    {
        $options = [];

        foreach ($ecolesDoctorales as $ecole) {
            $options[$ecole->getId()] = $ecole->getStructure()->getLibelle();
        }
        $this->ecolesDoctorales = $options;
        $this->get('ecoleDoctorale')->setEmptyOption('Sélectionnez une option');
        $this->get('ecoleDoctorale')->setValueOptions($this->ecolesDoctorales);
    }

    public function setComposantesEnseignement(array $composantesEnseignement): void
    {
        $options = [];

        foreach ($composantesEnseignement as $composanteEnseignement) {
            $options[$composanteEnseignement->getId()] = $composanteEnseignement->getStructure()->getLibelle();
        }
        $this->composantesEnseignement = $options;
        $this->get('composanteDoctorat')->setEmptyOption('Sélectionnez une option');
        $this->get('composanteDoctorat')->setValueOptions($this->composantesEnseignement);
    }

    public function setUnitesRecherche(array $unitesRecherche): void
    {
        $options = [];

        foreach ($unitesRecherche as $unite) {
            $options[$unite->getId()] = $unite->getStructure()->getLibelle();
        }
        $this->unitesRecherche = $options;
        $this->get('uniteRecherche')->setEmptyOption('Sélectionnez une option');
        $this->get('uniteRecherche')->setValueOptions($this->unitesRecherche);

        $this->get('uniteRechercheCoDirecteur')->setEmptyOption('Sélectionnez une option');
        $this->get('uniteRechercheCoDirecteur')->setValueOptions($this->unitesRecherche);
    }

    public function setEtablissementsInscription(array $etablissementsInscription): void
    {
        $options = [];

        foreach ($etablissementsInscription as $etablissementInscription) {
            $options[$etablissementInscription->getId()] = $etablissementInscription->getStructure()->getLibelle();
        }
        $this->etablissementInscription = $options;
        $this->get('etablissementInscription')->setEmptyOption('Sélectionnez une option');
        $this->get('etablissementInscription')->setValueOptions($this->etablissementInscription);

        $this->get('etablissementRattachementCoDirecteur')->setEmptyOption('Sélectionnez une option');
        $this->get('etablissementRattachementCoDirecteur')->setValueOptions($this->etablissementInscription);
    }

    public function setQualites(array $qualites): void
    {
        $options = [];

        foreach ($qualites as $qualite) {
            $options[$qualite->getId()] = $qualite->getLibelle();
        }
        $this->qualites = $options;
        $this->get('fonctionDirecteurThese')->setEmptyOption('Sélectionnez une option');
        $this->get('fonctionDirecteurThese')->setValueOptions($this->qualites);

        $this->get('fonctionCoDirecteurThese')->setEmptyOption('Sélectionnez une option');
        $this->get('fonctionCoDirecteurThese')->setValueOptions($this->qualites);
    }

    //Spécifités envisagées
    private $dateDuJourFormatee;
    private $dateDans10Ans;

    private ?string $urlPaysCoTutelle = null;

    public function setUrlPaysCoTutelle(string $urlPaysCoTutelle): void
    {
        $this->urlPaysCoTutelle = $urlPaysCoTutelle;
        $this->get('paysCoTutelle')->setAutocompleteSource($this->urlPaysCoTutelle);
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
        //Informations Inscription
        $this->add(
            (new Select("specialiteDoctorat"))
                ->setLabel("Code et libellé de la spécialité d'inscription en doctorat souhaitée")
                ->setLabelAttributes(['data-after' => ""])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        //Disciplines à récupérer -> liste en attente de récupération (demandée à Emilie)
        $this->add(
            (new Select("disciplineDoctorat"))
                ->setLabel("Code et libellé de la discipline d'inscription en doctorat souhaitée")
                ->setLabelAttributes(['data-after' => " / Discipline code"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $this->add(
            (new Select("composanteDoctorat"))
                ->setLabel("Composante de rattachement (U.F.R., instituts…)")
                ->setLabelAttributes(['data-after' => " / Component of attachment"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $this->add(
            (new Text('composanteDoctoratLibelle'))
                ->setLabel("Composante de rattachement (U.F.R., instituts…)")
                ->setLabelAttributes(['data-after' => " / Component of attachment"])
        );

        $this->add(
            (new Select("ecoleDoctorale"))
                ->setLabel("Ecole doctorale")
                ->setLabelAttributes(['data-after' => " / Doctoral school"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $this->add(
            (new Select("uniteRecherche"))
                ->setLabel("Unité de recherche")
                ->setLabelAttributes(['data-after' => " /   Laboratory"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "uniteRecherche"
                ])
        );

        $this->add(
            (new Select("etablissementInscription"))
                ->setLabel("Établissement d'inscription")
                ->setLabelAttributes(['data-after' => " /   Registering establishment"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "etablissementInscription"
                ])
        );

        $this->add(
            (new Hidden('directeur'))
        );

        $nomDirecteurThese = new SearchAndSelect('nomDirecteurThese', []);
        $nomDirecteurThese
            ->setSelectionRequired()
            ->setLabelAttributes(['data-after' => " / Name of thesis supervisor"])
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nomDirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($nomDirecteurThese);

        $prenomDirecteurThese = new SearchAndSelect('prenomDirecteurThese', ['label' => "Prénom du directeur de thèse"]);
        $prenomDirecteurThese
            ->setSelectionRequired()
            ->setLabelAttributes(['data-after' => " / First name of thesis supervisor"])
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'prenomDirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($prenomDirecteurThese);

        $this->add(
            (new Email('emailDirecteurThese'))
                ->setLabel("Mail du directeur de thèse")
                ->setLabelAttributes(['data-after' => " / Email of thesis supervisor"])
                ->setAttributes([
                    'id' => 'emailDirecteurThese',
                ])
        );

        $this->add(
            (new Select("fonctionDirecteurThese"))
                ->setLabel("Fonction")
                ->setLabelAttributes(['data-after' => " /   Role"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "fonctionDirecteurThese"
                ])
        );

        $this->add(
            (new Hidden('coDirecteur'))
        );

        $nomCodirecteurThese = new SearchAndSelect('nomCodirecteurThese', []);
        $nomCodirecteurThese
            ->setSelectionRequired()
            ->setLabelAttributes(['data-after' => " / Name of thesis co-supervisor"])
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nomCodirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($nomCodirecteurThese);

        $prenomCodirecteurThese = new SearchAndSelect('prenomCodirecteurThese', ['label' => "Prénom du co-directeur de thèse"]);
        $prenomCodirecteurThese
            ->setSelectionRequired()
            ->setLabelAttributes(['data-after' => " / First name of thesis co-supervisor"])
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'prenomCodirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($prenomCodirecteurThese);

        $this->add(
            (new Email('emailCodirecteurThese'))
                ->setLabel("Mail du co-directeur de thèse")
                ->setLabelAttributes(['data-after' => " / Email of thesis supervisor"])
                ->setAttributes([
                    'id' => 'emailCodirecteurThese',
                ])
        );

        $this->add(
            (new Select("uniteRechercheCoDirecteur"))
                ->setLabel("Unité de recherche")
                ->setLabelAttributes(['data-after' => " /   Laboratory"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "uniteRechercheCoDirecteur"
                ])
        );

        $this->add(
            (new Select("etablissementRattachementCoDirecteur"))
                ->setLabel("Établissement de rattachement")
                ->setLabelAttributes(['data-after' => " /   Registering establishment"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "etablissementRattachementCoDirecteur"
                ])
        );

        $this->add(
            (new Select("fonctionCoDirecteurThese"))
                ->setLabel("Fonction")
                ->setLabelAttributes(['data-after' => " /   Role"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "fonctionCoDirecteurThese"
                ])
        );

        $this->add(
            (new Textarea('titreThese'))
                ->setLabelAttributes(['data-after' => " / Temporary Phd Title"])
        );

        //Spécifités envisagées
        $this->add(
            (new Radio('confidentialite'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Confidentialité souhaitée")
                ->setLabelAttributes(['data-after' => " / Privacy desired"])
        );

        $this->add(
            (new Date('dateConfidentialite'))
                ->setLabel("Date de fin de confidentialité souhaitée (limitée à 10 ans)")
                ->setLabelAttributes(['data-after' => " / Desired end date of confidentiality (Limited to 10 years)"])
                ->setAttributes([
                    'min'  => $this->dateDuJourFormatee,
                    'max'  => $this->dateDans10Ans,
                    'step' => '1',
                ])
        );

        $this->add(
            (new Radio('coTutelle'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Cotutelle envisagée")
                ->setLabelAttributes(['data-after' => " / Planned cotutelle"])
        );

        $paysCoTutelle = new SearchAndSelect('paysCoTutelle', ['label' => "Pays concerné"]);
        $paysCoTutelle
            ->setAutocompleteSource($this->urlPaysCoTutelle)
            ->setLabelAttributes(['data-after' => " / Country"])
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'paysCoTutelle',
            ]);
        $this->add($paysCoTutelle);

        $this->add(
            (new Radio('coEncadrement'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-encadrement envisagé")
                ->setLabelAttributes(['data-after' => " / Co-supervision envisaged"])
        );

        $this->add(
            (new Radio('coDirection'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-direction demandée")
                ->setLabelAttributes(['data-after' => " / Co-direction asked"])
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationInscription");
        $this->add($verificationFieldset);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            //Informations Inscription
            'specialiteDoctorat' => [
                'name' => 'specialiteDoctorat',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class],
                ],
            ],
            'disciplineDoctorat' => [
                'name' => 'disciplineDoctorat',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'composanteDoctorat' => [
                'name' => 'composanteDoctorat',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'ecoleDoctorale' => [
                'name' => 'ecoleDoctorale',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'uniteRecherche' => [
                'name' => 'uniteRecherche',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'etablissementInscription' => [
                'name' => 'etablissementInscription',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'prenomDirecteurThese' => [
                'name' => 'prenomDirecteurThese',
                'required' => false,
            ],
            'nomDirecteurThese' => [
                'name' => 'nomDirecteurThese',
                'required' => false,
            ],
            'emailDirecteurThese' => [
                'name' => 'emailDirecteurThese',
                'required' => false,
            ],
            'fonctionDirecteurThese' => [
                'name' => 'fonctionDirecteurThese',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'prenomCodirecteurThese' => [
                'name' => 'prenomCodirecteurThese',
                'required' => false,
            ],
            'nomCodirecteurThese' => [
                'name' => 'nomCodirecteurThese',
                'required' => false,
            ],
            'emailCodirecteurThese' => [
                'name' => 'emailCodirecteurThese',
                'required' => false,
            ],
            'uniteRechercheCoDirecteur' => [
                'name' => 'uniteRechercheCoDirecteur',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'etablissementRattachementCoDirecteur' => [
                'name' => 'etablissementRattachementCoDirecteur',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'fonctionCoDirecteurThese' => [
                'name' => 'fonctionCoDirecteurThese',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'titreThese' => [
                'name' => 'titreThese',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                    ['name' => ToNull::class],
                ],
            ],
            //Spécifités envisagées
            'confidentialite' => [
                'name' => 'confidentialite',
                'required' => false,
            ],
            'dateConfidentialite' => [
                'name' => 'dateConfidentialite',
                'required' => false,
            ],
            'coTutelle' => [
                'name' => 'coTutelle',
                'required' => false,
            ],
            'paysCo-tutelle' => [
                'name' => 'paysCoTutelle',
                'required' => false ,
            ],
            'coEncadrement' => [
                'name' => 'coEncadrement',
                'required' => false,
            ],
            'coDirection' => [
                'name' => 'coDirection',
                'required' => false,
            ],
        ];
    }
}