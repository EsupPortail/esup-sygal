<?php
namespace Admission\Form\Fieldset\Inscription;

use Admission\Form\Fieldset\AdmissionBaseFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
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
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use UnicaenApp\Form\Element\SearchAndSelect;

class InscriptionFieldset extends AdmissionBaseFieldset implements InputFilterProviderInterface
{
    use AdmissionServiceAwareTrait;
    //Informations Inscription
    private string $urlIndividuThese;
    private array $composantesEnseignement;
    private array $ecolesDoctorales;
    private array $unitesRecherche;
    private array $etablissementInscription;
    private array $specialites;
    private array $qualites;

    //Spécifités envisagées
    private string $dateDuJourFormatee;
    private string $dateDans10Ans;
    private string $urlPaysCoTutelle;

    public function setUrlIndividuThese(string $urlIndividuThese): void
    {
        $this->urlIndividuThese = $urlIndividuThese;
    }

    public function setUrlPaysCoTutelle(string $urlPaysCoTutelle): void
    {
        $this->urlPaysCoTutelle = $urlPaysCoTutelle;
    }

    public function setSpecialites(array $specialites): void
    {
        $this->specialites = $specialites;
    }

    public function setEcolesDoctorales(array $ecolesDoctorales): void
    {
        $options = [];
        foreach ($ecolesDoctorales as $ecole) {
            $options[$ecole->getId()] = $ecole->getStructure()->getLibelle();
        }
        $this->ecolesDoctorales = $options;
    }

    public function setComposantesEnseignement(array $composantesEnseignement): void
    {
        $options = [];
        foreach ($composantesEnseignement as $composanteEnseignement) {
            $options[$composanteEnseignement->getId()] = $composanteEnseignement->getStructure()->getLibelle();
        }
        $this->composantesEnseignement = $options;
    }

    public function setUnitesRecherche(array $unitesRecherche): void
    {
        $options = [];
        foreach ($unitesRecherche as $unite) {
            $options[$unite->getId()] = $unite->getStructure()->getLibelle();
        }
        $this->unitesRecherche = $options;
    }

    public function setEtablissementsInscription(array $etablissementsInscription): void
    {
        $options = [];
        foreach ($etablissementsInscription as $etablissementInscription) {
            if($this->admissionService->canEtabAccessModuleAdmission($etablissementInscription)){
                $options[$etablissementInscription->getId()] = $etablissementInscription->getStructure()->getLibelle();
            }
        }
        $this->etablissementInscription = $options;
    }

    public function setQualites(array $qualites): void
    {
        $options = [];

        foreach ($qualites as $qualite) {
            $options[$qualite->getId()] = $qualite->getLibelle();
        }
        $this->qualites = $options;
    }

    public function prepareElement(FormInterface $form): void
    {
        $this->get('nomDirecteurThese')->setAutocompleteSource($this->urlIndividuThese);
        $this->get('prenomDirecteurThese')->setAutocompleteSource($this->urlIndividuThese);

        $this->get('nomCodirecteurThese')->setAutocompleteSource($this->urlIndividuThese);
        $this->get('prenomCodirecteurThese')->setAutocompleteSource($this->urlIndividuThese);

        $this->get('specialiteDoctorat')->setValueOptions($this->specialites);
        $this->get('composanteDoctorat')->setValueOptions($this->composantesEnseignement);
        $this->get('ecoleDoctorale')->setValueOptions($this->ecolesDoctorales);
        $this->get('uniteRecherche')->setValueOptions($this->unitesRecherche);
        $this->get('uniteRechercheCoDirecteur')->setValueOptions($this->unitesRecherche);
        $this->get('etablissementInscription')->setValueOptions($this->etablissementInscription);
        $this->get('etablissementRattachementCoDirecteur')->setValueOptions($this->etablissementInscription);
        $this->get('fonctionDirecteurThese')->setValueOptions($this->qualites);
        $this->get('fonctionCoDirecteurThese')->setValueOptions($this->qualites);
        $this->get('paysCoTutelle')->setAutocompleteSource($this->urlPaysCoTutelle);

        parent::prepareElement($form); // TODO: Change the autogenerated stub
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
                ->setLabel("Spécialité d'inscription en doctorat souhaitée")
                ->setLabelAttributes(['data-after' => " / Preferred doctoral specialization"])
                ->setEmptyOption('Sélectionnez une option')
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

//        $this->add(
//            (new Select("disciplineDoctorat"))
//                ->setLabel("Code et libellé de la discipline d'inscription en doctorat souhaitée")
//                ->setLabelAttributes(['data-after' => " / Discipline code"])
//                ->setEmptyOption('Sélectionnez une option')
//                ->setAttributes([
//                    'class' => 'bootstrap-selectpicker show-tick',
//                    'data-live-search' => 'true',
//                ])
//        );

        $this->add(
            (new Text('disciplineDoctorat'))
                ->setLabel("Discipline d'inscription en doctorat souhaitée")
                ->setLabelAttributes(['data-after' => " / Preferred doctoral discipline"])
        );

        $this->add(
            (new Select("composanteDoctorat"))
                ->setLabel("Composante de rattachement (U.F.R., instituts…)")
                ->setLabelAttributes(['data-after' => " / Component of attachment"])
                ->setEmptyOption('Sélectionnez une option')
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
            (new Text('etablissementLaboratoireRecherche'))
                ->setLabel("Établissement hébergeant l’unité de recherche")
                ->setLabelAttributes(['data-after' => " / Establishment hosting laboratory"])
        );

        $this->add(
            (new Select("ecoleDoctorale"))
                ->setLabel("École doctorale <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>")
                ->setLabelOptions(['disable_html_escape' => true])
                ->setLabelAttributes(['data-after' => " / Doctoral school"])
                ->setEmptyOption('Sélectionnez une option')
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ])
        );

        $this->add(
            (new Select("uniteRecherche"))
                ->setLabel("Unité de recherche")
                ->setLabelAttributes(['data-after' => " /   Laboratory"])
                ->setEmptyOption('Sélectionnez une option')
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
                ->setEmptyOption('Sélectionnez une option')
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
                ->setEmptyOption('Sélectionnez une option')
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
                ->setEmptyOption('Sélectionnez une option')
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
                ->setEmptyOption('Sélectionnez une option')
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
                ->setEmptyOption('Sélectionnez une option')
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
                'placeholder' => "Entrez les deux premières lettres...",
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
                'required' => true,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => "Merci de renseigner votre école doctorale afin d'associer un gestionnaire à ce dossier",
                            ],
                        ],
                    ],
                ],
            ],
            'uniteRecherche' => [
                'name' => 'uniteRecherche',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'etablissementLaboratoireRecherche' => [
                'name' => 'etablissementLaboratoireRecherche',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
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