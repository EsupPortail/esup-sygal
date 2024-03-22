<?php

namespace These\Form\TheseSaisie;

use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;
use UnicaenApp\Form\Element\SearchAndSelect;

class TheseSaisieForm extends Form
{
    use DisciplineServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    private string $urlDoctorant;
    const NBCODIR = 3;

    /**
     * @param string|null $urlDoctorant
     */
    public function setUrlDoctorant(?string $urlDoctorant): void
    {
        $this->urlDoctorant = $urlDoctorant;
    }

    private string $urlDirecteur;
    private ?array $domainesHal = null;


    /**
     * @param string|null $urlDirecteur
     */
    public function setUrlDirecteur(?string $urlDirecteur): void
    {
        $this->urlDirecteur = $urlDirecteur;
    }

    public function init()
    {
        $this->add([
            'type' => Textarea::class,
            'name' => 'titre',
            'options' => [
                'label' => "Titre de la thèse * : ",
            ],
        ]);
        $doctorant = new SearchAndSelect('doctorant', ['label' => "Doctorant·e :"]);
        $doctorant
            ->setAutocompleteSource($this->urlDoctorant)
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
                'value_options' => $this->getDisciplineService()->getDisciplinesAsOptions(),
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

        /**  DIRECTEUR  ***********************************************************************************************/
        $directeur = new SearchAndSelect('directeur-individu', ['label' => "Directeur·trice de thèse :"]);
        $directeur
            ->setAutocompleteSource($this->urlDirecteur)
            ->setAttributes([
                'id' => 'directeur-individu',
                'placeholder' => "Rechercher un·e directeur·trice ...",
            ]);
        $this->add($directeur);
        $this->add([
            'type' => Select::class,
            'name' => 'directeur-qualite',
            'options' => [
                'label' => "Qualité :",
                'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
                'empty_option' => "Sélectionner une qualité",
            ],
            'attributes' => [
                'id' => 'directeur-qualite',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);
        $this->add([
            'type' => Select::class,
            'name' => 'directeur-etablissement',
            'options' => [
                'label' => "Établissement :",
                'value_options' => $this->getEtablissementService()->getEtablissementsAsOptions(),
                'empty_option' => "Sélectionner un établissement",
            ],
            'attributes' => [
                'id' => 'directeur-etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ],
        ]);

        /** CO-DIRECTION **********************************************************************************************/

        for ($i = 1; $i <= TheseSaisieForm::NBCODIR; $i++) {
            $codirecteur = new SearchAndSelect('codirecteur' . $i . '-individu', ['label' => "Codirecteur·trice de thèse :"]);
            $codirecteur
                ->setAutocompleteSource($this->urlDirecteur)
                ->setAttributes([
                    'id' => 'codirecteur' . $i,
                    'placeholder' => "Rechercher un·e codirecteur·trice ...",
                ]);
            $this->add($codirecteur);
            $this->add([
                'type' => Select::class,
                'name' => 'codirecteur' . $i . '-qualite',
                'options' => [
                    'label' => "Qualité :",
                    'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
                    'empty_option' => "Sélectionner une qualité",
                ],
                'attributes' => [
                    'id' => 'codirecteur' . $i . '-qualite',
                    'class' => 'selectpicker show-menu-arrow',
                    'data-live-search' => 'true',
                    'data-bs-html' => 'true',
                ]
            ]);
            $this->add([
                'type' => Select::class,
                'name' => 'codirecteur' . $i . '-etablissement',
                'options' => [
                    'label' => "Établissement :",
                    'value_options' => $this->getEtablissementService()->getEtablissementsAsOptions(),
                    'empty_option' => "Sélectionner un établissement",
                ],
                'attributes' => [
                    'id' => 'codirecteur' . $i . '-etablissement',
                    'class' => 'selectpicker show-menu-arrow',
                    'data-live-search' => 'true',
                    'data-bs-html' => 'true',
                ]
            ]);
        }

        /** STRUCTURES D'ENCADREMENT **********************************************************************************/
        $this->add([
            'type' => Select::class,
            'name' => 'unite-recherche',
            'options' => [
                'label' => "Unité de recherche :",
                'value_options' => $this->getUniteRechercheService()->getUnitesRecherchesAsOptions(),
                'empty_option' => "Sélectionner l'unité de recherche",
            ],
            'attributes' => [
                'id' => 'unite-recherche',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'ecole-doctorale',
            'options' => [
                'label' => "École doctorale :",
                'value_options' => $this->getEcoleDoctoraleService()->getEcolesDoctoralsAsOptions(),
                'empty_option' => "Sélectionner l'école doctorale",
            ],
            'attributes' => [
                'id' => 'ecole-doctorale',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'etablissement',
            'options' => [
                'label' => "Établissement :",
                'value_options' => $this->getEtablissementService()->getEtablissementsInscriptionsAsOptions(),
                'empty_option' => "Sélectionner l'établissement",
            ],
            'attributes' => [
                'id' => 'etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ]
        ]);

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

        //Submit
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'titre' => [
                'name' => 'titre',
                'required' => true,
            ],
            'doctorant' => [
                'name' => 'doctorant',
                'required' => true,
            ],
            'discipline' => [
                'name' => 'discipline',
                'required' => false,
            ],
            'domaineHal' => [
                'name' => 'domaineHal',
                'required' => false,
            ],
            'unite-recherche' => [
                'name' => 'unite-recherche',
                'required' => false,
            ],
            'ecole-doctorale' => [
                'name' => 'ecole-doctorale',
                'required' => false,
            ],
            'etablissement' => [
                'name' => 'etablissement',
                'required' => false,
            ],
            'directeur-individu' => [
                'name' => 'directeur-individu',
                'required' => false,
            ],
            'directeur-qualite' => [
                'name' => 'directeur-qualite',
                'required' => false,
            ],
            'directeur-etablissement' => [
                'name' => 'directeur-etablissement',
                'required' => false,
            ],
            //TODO FOR !!
            'codirecteur1-individu' => [
                'name' => 'codirecteur-individu1',
                'required' => false,
            ],
            'codirecteur2-individu' => [
                'name' => 'codirecteur-individu2',
                'required' => false,
            ],
            'codirecteur3-individu' => [
                'name' => 'codirecteur-individu3',
                'required' => false,
            ],
            'codirecteur1-qualite' => [
                'name' => 'codirecteur-qualite1',
                'required' => false,
            ],
            'codirecteur2-qualite' => [
                'name' => 'codirecteur-qualite2',
                'required' => false,
            ],
            'codirecteur3-qualite' => [
                'name' => 'codirecteur-qualite3',
                'required' => false,
            ],
            'codirecteur1-etablissement' => [
                'name' => 'codirecteur-etablissement1',
                'required' => false,
            ],
            'codirecteur2-etablissement' => [
                'name' => 'codirecteur-etablissement',
                'required' => false,
            ],
            'codirecteur3-etablissement' => [
                'name' => 'codirecteur3-etablissement',
                'required' => false,
            ],
            'confidentialite' => [
                'name' => 'confidentialite',
                'required' => false,
            ],
            'fin-confidentialite' => [
                'name' => 'fin-confidentialite',
                'required' => false,
            ],
        ]));
    }
}