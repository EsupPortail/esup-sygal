<?php
namespace Admission\Form\Fieldset\Inscription;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\SearchAndSelect;

class InscriptionFieldset extends Fieldset implements InputFilterProviderInterface
{
    //Informations Inscription
    private ?string $urlDirecteurThese = null;
    private ?string $urlCoDirecteurThese = null;
    private ?string $urlEtablissement = null;
    /** @var array */
    private $ecolesDoctorales = null;
    /** @var array */
    private $unitesRecherche = null;
    private $specialites = null;


    public function setUrlDirecteurThese(string $urlDirecteurThese): void
    {
        $this->urlDirecteurThese = $urlDirecteurThese;
        $this->get('nomDirecteurThese')->setAutocompleteSource($this->urlDirecteurThese);
    }

    public function setUrlCoDirecteurThese(string $urlCoDirecteurThese): void
    {
        $this->urlCoDirecteurThese = $urlCoDirecteurThese;
        $this->get('nomCodirecteurThese')->setAutocompleteSource($this->urlCoDirecteurThese);
    }

    public function setUrlEtablissement(string $urlEtablissement): void
    {
        $this->urlEtablissement = $urlEtablissement;
        $this->get('composanteDoctorat')->setAutocompleteSource($this->urlEtablissement);
    }

    public function setSpecialites(array $specialites): void
    {
        $this->specialites = $specialites;
        $this->get('specialiteDoctorat')->setValueOptions($this->specialites);
    }

    public function setEcolesDoctorales(array $ecolesDoctorales): void
    {
        $options = [];

        foreach ($ecolesDoctorales as $ecole) {
            $options[$ecole->getId()] = $ecole->getStructure()->getLibelle();
        }
        $this->ecolesDoctorales = $options;
        $this->get('ecoleDoctorale')->setValueOptions($this->ecolesDoctorales);
    }

    public function setUnitesRecherche(array $unitesRecherche): void
    {
        $options = [];

        foreach ($unitesRecherche as $unite) {
            $options[$unite->getId()] = $unite->getStructure()->getLibelle();
        }
        $this->unitesRecherche = $options;
        $this->get('uniteRecherche')->setValueOptions($this->unitesRecherche);
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
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $composanteDoctorat = new SearchAndSelect('composanteDoctorat', ['label' => "Composante de rattachement (U.F.R., instituts…)"]);
        $composanteDoctorat
            ->setAutocompleteSource($this->urlEtablissement )
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'composanteDoctorat',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($composanteDoctorat);

        $this->add(
            (new Select("ecoleDoctorale"))
                ->setLabel("Ecole doctorale")
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $this->add(
            (new Select("uniteRecherche"))
                ->setLabel("Unité de recherche")
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => "uniteRecherche"
                ])
        );

        $this->add(
            (new Hidden('directeur'))
        );

        $nomDirecteurThese = new SearchAndSelect('nomDirecteurThese', ['label' => "Nom du directeur de thèse"]);
        $nomDirecteurThese
            ->setAutocompleteSource($this->urlDirecteurThese)
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nomDirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($nomDirecteurThese);

        $prenomDirecteurThese = new SearchAndSelect('prenomDirecteurThese', ['label' => "Prénom du directeur de thèse"]);
        $prenomDirecteurThese
            ->setAutocompleteSource($this->urlDirecteurThese)
            ->setSelectionRequired()
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
                ->setAttributes([
                    'id' => 'emailDirecteurThese',
                ])
        );

        $this->add(
            (new Hidden('coDirecteur'))
        );

        $nomCodirecteurThese = new SearchAndSelect('nomCodirecteurThese', ['label' => "Nom du co-directeur de thèse"]);
        $nomCodirecteurThese
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nomCodirecteurThese',
                'placeholder' => "Entrez les deux premières lettres...",
            ]);
        $this->add($nomCodirecteurThese);

        $prenomCodirecteurThese = new SearchAndSelect('prenomCodirecteurThese', ['label' => "Prénom du co-directeur de thèse"]);
        $prenomCodirecteurThese
            ->setAutocompleteSource($this->urlDirecteurThese)
            ->setSelectionRequired()
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
                ->setAttributes([
                    'id' => 'emailCodirecteurThese',
                ])
        );

        $this->add(
            (new Textarea('titreThese'))
        );

        //Spécifités envisagées
        $this->add(
            (new Radio('confidentialite'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Confidentialité souhaitée")
        );

        $this->add(
            (new Date('dateConfidentialite'))
                ->setLabel("Date de fin de confidentialité souhaitée (limitée à 10 ans)")
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
        );

        $paysCoTutelle = new SearchAndSelect('paysCoTutelle', ['label' => "Pays concerné"]);
        $paysCoTutelle
            ->setAutocompleteSource($this->urlPaysCoTutelle)
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
        );

        $this->add(
            (new Radio('coDirection'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-direction demandée")
        );

        $this->add(
            (new Textarea('verificationInscription'))
                ->setLabel("Observations (Non enregistrées encore en base de données)")
                ->setAttributes([
                    "class" => "text_observations_gestionnaire"
                ])
        );
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