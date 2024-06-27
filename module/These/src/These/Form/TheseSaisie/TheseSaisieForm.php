<?php

namespace These\Form\TheseSaisie;

use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;
use These\Fieldset\Direction\DirectionFieldset;
use These\Fieldset\Encadrement\EncadrementFieldset;
use These\Fieldset\Financement\FinancementFieldset;
use These\Fieldset\Generalites\GeneralitesFieldset;
use These\Fieldset\Structures\StructuresFieldset;
use UnicaenApp\Form\Element\Collection;

class TheseSaisieForm extends Form
{
    use DisciplineServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

//    private string $urlDoctorant;
//    const NBCODIR = 3;
//
//    /**
//     * @param string|null $urlDoctorant
//     */
//    public function setUrlDoctorant(?string $urlDoctorant): void
//    {
//        $this->urlDoctorant = $urlDoctorant;
//    }
//
//    private string $urlDirecteur;
//    private ?array $domainesHal = null;
//
//
//    /**
//     * @param string|null $urlDirecteur
//     */
//    public function setUrlDirecteur(?string $urlDirecteur): void
//    {
//        $this->urlDirecteur = $urlDirecteur;
//    }
//
//    public function init()
//    {
//        $this->add([
//            'type' => Textarea::class,
//            'name' => 'titre',
//            'options' => [
//                'label' => "Titre de la thèse * : ",
//            ],
//        ]);
//        $doctorant = new SearchAndSelect('doctorant', ['label' => "Doctorant·e :"]);
//        $doctorant
//            ->setAutocompleteSource($this->urlDoctorant)
//            ->setAttributes([
//                'id' => 'doctorant',
//                'placeholder' => "Rechercher un·e doctorant·e ...",
//            ]);
//        $this->add($doctorant);
//
//        $this->add([
//            'type' => Select::class,
//            'name' => 'discipline',
//            'options' => [
//                'label' => "Discipline :",
//                'value_options' => $this->getDisciplineService()->getDisciplinesAsOptions(),
//                'empty_option' => "Sélectionner une discipline",
//            ],
//            'attributes' => [
//                'id' => 'discipline',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ]
//        ]);
//
//        $domainesHalFieldset = $this->getFormFactory()->getFormElementManager()->get(DomaineHalFieldset::class);
//        $this->add($domainesHalFieldset, ['name' => 'domaineHal']);
//
//        /**  DIRECTEUR  ***********************************************************************************************/
//        $directeur = new SearchAndSelect('directeur-individu', ['label' => "Directeur·trice de thèse :"]);
//        $directeur
//            ->setAutocompleteSource($this->urlDirecteur)
//            ->setAttributes([
//                'id' => 'directeur-individu',
//                'placeholder' => "Rechercher un·e directeur·trice ...",
//            ]);
//        $this->add($directeur);
//        $this->add([
//            'type' => Select::class,
//            'name' => 'directeur-qualite',
//            'options' => [
//                'label' => "Qualité :",
//                'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
//                'empty_option' => "Sélectionner une qualité",
//            ],
//            'attributes' => [
//                'id' => 'directeur-qualite',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ]
//        ]);
//        $this->add([
//            'type' => Select::class,
//            'name' => 'directeur-etablissement',
//            'options' => [
//                'label' => "Établissement :",
//                'value_options' => $this->getEtablissementsAsOptions(),
//                'empty_option' => "Sélectionner un établissement",
//            ],
//            'attributes' => [
//                'id' => 'directeur-etablissement',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ],
//        ]);
//
//        /** CO-DIRECTION **********************************************************************************************/
//
//        for ($i = 1; $i <= TheseSaisieForm::NBCODIR; $i++) {
//            $codirecteur = new SearchAndSelect('codirecteur' . $i . '-individu', ['label' => "Codirecteur·trice de thèse :"]);
//            $codirecteur
//                ->setAutocompleteSource($this->urlDirecteur)
//                ->setAttributes([
//                    'id' => 'codirecteur' . $i,
//                    'placeholder' => "Rechercher un·e codirecteur·trice ...",
//                ]);
//            $this->add($codirecteur);
//            $this->add([
//                'type' => Select::class,
//                'name' => 'codirecteur' . $i . '-qualite',
//                'options' => [
//                    'label' => "Qualité :",
//                    'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
//                    'empty_option' => "Sélectionner une qualité",
//                ],
//                'attributes' => [
//                    'id' => 'codirecteur' . $i . '-qualite',
//                    'class' => 'selectpicker show-menu-arrow',
//                    'data-live-search' => 'true',
//                    'data-bs-html' => 'true',
//                ]
//            ]);
//            $this->add([
//                'type' => Select::class,
//                'name' => 'codirecteur' . $i . '-etablissement',
//                'options' => [
//                    'label' => "Établissement :",
//                    'value_options' => $this->getEtablissementsAsOptions(),
//                    'empty_option' => "Sélectionner un établissement",
//                ],
//                'attributes' => [
//                    'id' => 'codirecteur' . $i . '-etablissement',
//                    'class' => 'selectpicker show-menu-arrow',
//                    'data-live-search' => 'true',
//                    'data-bs-html' => 'true',
//                ]
//            ]);
//        }
//
//        /** STRUCTURES D'ENCADREMENT **********************************************************************************/
//        $this->add([
//            'type' => Select::class,
//            'name' => 'unite-recherche',
//            'options' => [
//                'label' => "Unité de recherche :",
//                'value_options' => $this->getUnitesRecherchesAsOptions(),
//                'empty_option' => "Sélectionner l'unité de recherche",
//            ],
//            'attributes' => [
//                'id' => 'unite-recherche',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ]
//        ]);
//
//        $this->add([
//            'type' => Select::class,
//            'name' => 'ecole-doctorale',
//            'options' => [
//                'label' => "École doctorale :",
//                'value_options' => $this->getEcolesDoctoralsAsOptions(),
//                'empty_option' => "Sélectionner l'école doctorale",
//            ],
//            'attributes' => [
//                'id' => 'ecole-doctorale',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ]
//        ]);
//
//        $this->add([
//            'type' => Select::class,
//            'name' => 'etablissement',
//            'options' => [
//                'label' => "Établissement :",
//                'value_options' => $this->getEtablissementsInscriptionsAsOptions(),
//                'empty_option' => "Sélectionner l'établissement",
//            ],
//            'attributes' => [
//                'id' => 'etablissement',
//                'class' => 'selectpicker show-menu-arrow',
//                'data-live-search' => 'true',
//                'data-bs-html' => 'true',
//            ]
//        ]);
//
//        $this->add([
//            'type' => Radio::class,
//            'name' => 'confidentialite',
//            'options' => [
//                'label' => "Confidentialité de la thèse :",
//                'value_options' => [
//                    0 => "These non confidentielle ",
//                    1 => "Thèse confidentielle ",
//                ],
//            ],
//            'attributes' => [
//                'id' => 'confidentialite',
//            ],
//        ]);
//
//        $this->add([
//            'type' => Date::class,
//            'name' => 'fin-confidentialite',
//            'options' => [
//                'label' => "Date de fin de confidentialité : ",
//            ],
//            'attributes' => [
//                'id' => 'fin-confidentialite',
//            ],
//        ]);
//
//        //Submit
//        $this->add([
//            'type' => Button::class,
//            'name' => 'bouton',
//            'options' => [
//                'label' => '<i class="fas fa-save"></i> Enregistrer',
//                'label_options' => [
//                    'disable_html_escape' => true,
//                ],
//            ],
//            'attributes' => [
//                'type' => 'submit',
//                'class' => 'btn btn-primary',
//            ],
//        ]);
//
//        $this->setInputFilter((new Factory())->createInputFilter([
//            'titre' => [
//                'name' => 'titre',
//                'required' => true,
//            ],
//            'doctorant' => [
//                'name' => 'doctorant',
//                'required' => true,
//            ],
//            'discipline' => [
//                'name' => 'discipline',
//                'required' => false,
//            ],
//            'domaineHal' => [
//                'name' => 'domaineHal',
//                'required' => false,
//            ],
//            'unite-recherche' => [
//                'name' => 'unite-recherche',
//                'required' => false,
//            ],
//            'ecole-doctorale' => [
//                'name' => 'ecole-doctorale',
//                'required' => false,
//            ],
//            'etablissement' => [
//                'name' => 'etablissement',
//                'required' => false,
//            ],
//            'directeur-individu' => [
//                'name' => 'directeur-individu',
//                'required' => false,
//            ],
//            'directeur-qualite' => [
//                'name' => 'directeur-qualite',
//                'required' => false,
//            ],
//            'directeur-etablissement' => [
//                'name' => 'directeur-etablissement',
//                'required' => false,
//            ],
//            //TODO FOR !!
//            'codirecteur1-individu' => [
//                'name' => 'codirecteur-individu1',
//                'required' => false,
//            ],
//            'codirecteur2-individu' => [
//                'name' => 'codirecteur-individu2',
//                'required' => false,
//            ],
//            'codirecteur3-individu' => [
//                'name' => 'codirecteur-individu3',
//                'required' => false,
//            ],
//            'codirecteur1-qualite' => [
//                'name' => 'codirecteur-qualite1',
//                'required' => false,
//            ],
//            'codirecteur2-qualite' => [
//                'name' => 'codirecteur-qualite2',
//                'required' => false,
//            ],
//            'codirecteur3-qualite' => [
//                'name' => 'codirecteur-qualite3',
//                'required' => false,
//            ],
//            'codirecteur1-etablissement' => [
//                'name' => 'codirecteur-etablissement1',
//                'required' => false,
//            ],
//            'codirecteur2-etablissement' => [
//                'name' => 'codirecteur-etablissement',
//                'required' => false,
//            ],
//            'codirecteur3-etablissement' => [
//                'name' => 'codirecteur3-etablissement',
//                'required' => false,
//            ],
//            'confidentialite' => [
//                'name' => 'confidentialite',
//                'required' => false,
//            ],
//            'fin-confidentialite' => [
//                'name' => 'fin-confidentialite',
//                'required' => false,
//            ],
//        ]));
//    }
//
//    public function getEtablissementsInscriptionsAsOptions() : array
//    {
//        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
//        $result = [];
//        foreach ($etablissements as $etablissement) $result[$etablissement->getId()] = $etablissement->getStructure()->getLibelle();
//        return $result;
//    }
//
//    public function getEtablissementsAsOptions() : array
//    {
//        $etablissements = $this->etablissementService->getRepository()->findAll();
//
//        $options = [];
//        foreach ($etablissements as $etablissement) {
//            $options[$etablissement->getId()] = $etablissement->getStructure()->getLibelle() . " " ."<span class='badge'>".$etablissement->getStructure()->getSigle()."</span>";
//        }
//        return $options;
//    }
//
//    private function getEcolesDoctoralsAsOptions() : array
//    {
//        $ecoles = $this->ecoleDoctoraleService->getRepository()->findAll();
//
//        $options = [];
//        foreach ($ecoles as $ecole) {
//            $options[$ecole->getId()] = $ecole->getStructure()->getLibelle() . " " ."<span class='badge'>".$ecole->getStructure()->getSigle()."</span>";
//        }
//        return $options;
//    }
//
//    public function getUnitesRecherchesAsOptions() : array
//    {
//        $unites = $this->uniteRechercheService->getRepository()->findAll();
//
//        $options = [];
//        foreach ($unites as $unite) {
//            $options[$unite->getId()] = $unite->getStructure()->getLibelle() . " " ."<span class='badge'>".$unite->getStructure()->getSigle()."</span>";
//        }
//        return $options;
//    }

    public function init(): void
    {
        $generalitesFieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $generalitesFieldset->setName("generalites");
        $this->add($generalitesFieldset);

        $structuresFieldset = $this->getFormFactory()->getFormElementManager()->get(StructuresFieldset::class);
        $structuresFieldset->setName("structures");
        $this->add($structuresFieldset);
//
        $directionFieldset = $this->getFormFactory()->getFormElementManager()->get(DirectionFieldset::class);
        $directionFieldset->setName("direction");
        $this->add($directionFieldset);
//
        $encadrementFieldset = $this->getFormFactory()->getFormElementManager()->get(EncadrementFieldset::class);
        $encadrementFieldset->setName("encadrements");
        $this->add($encadrementFieldset);

//        $financementsFieldset = $this->getFormFactory()->getFormElementManager()->get(FinancementFieldset::class);
//        $financementsFieldset->setName("financements");
//        $this->add($financementsFieldset);

//        $financements = new Collection('financements');
//        $financements
//            ->setLabel("Financement")
//            ->setMinElements(0)
//            ->setOptions([
//                'count' => 0,
//                'should_create_template' => true,
//                'allow_add' => true,
//                'allow_remove' => true,
//                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
//                    FinancementFieldset::class
//                ),
//            ])
//            ->setAttributes([
//                'class' => 'collection',
//            ]);
//        $this->add($financements);

        $this
            ->add(new Csrf('security'))
            ->add((new Submit('submit'))->setValue('Enregistrer'));
    }
}