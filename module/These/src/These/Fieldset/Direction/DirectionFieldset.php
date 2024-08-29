<?php

namespace These\Fieldset\Direction;

use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Form\Element\SearchAndSelect;
use UnicaenApp\Form\Element\SearchAndSelect2;

class DirectionFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use StructureServiceAwareTrait;

    const NBCODIR = 2;

    private string $urlAutocompleteIndividu;
    private string $urlAutocompleteEtablissement;
    private string $urlAutocompleteEcoleDoctorale;
    private string $urlAutocompleteUniteRecherche;
    
    public function prepareElement(FormInterface $form): void
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();

        $this->get('directeur-individu')->setAttribute('disabled', !$estModifiable);
        $this->get('directeur-etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('directeur-ecoleDoctorale')->setAttribute('disabled', !$estModifiable);
        $this->get('directeur-uniteRecherche')->setAttribute('disabled', !$estModifiable);
        $this->get('directeur-qualite')->setAttribute('disabled', !$estModifiable);

        for ($i = 1; $i <= DirectionFieldset::NBCODIR; $i++) {
            $this->get('codirecteur' . $i . '-enabled')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-individu')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-etablissement')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-ecoleDoctorale')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-uniteRecherche')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-qualite')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur' . $i . '-principal')->setAttribute('disabled', !$estModifiable);
            $this->get('codirecteur2-exterieur')->setAttribute('disabled', !$estModifiable);
        }

        parent::prepareElement($form); // TODO: Change the autogenerated stub
    }

    public function setUrlAutocompleteIndividu(string $urlAutocompleteIndividu): void
    {
        $this->urlAutocompleteIndividu = $urlAutocompleteIndividu;
    }
    public function setUrlAutocompleteEtablissement(string $urlAutocompleteEtablissement): void
    {
        $this->urlAutocompleteEtablissement = $urlAutocompleteEtablissement;
    }
    public function setUrlAutocompleteEcoleDoctorale(string $urlAutocompleteEcoleDoctorale): void
    {
        $this->urlAutocompleteEcoleDoctorale = $urlAutocompleteEcoleDoctorale;
    }
    public function setUrlAutocompleteUniteRecherche(string $urlAutocompleteUniteRecherche): void
    {
        $this->urlAutocompleteUniteRecherche = $urlAutocompleteUniteRecherche;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        /**  DIRECTION  ***********************************************************************************************/

        $this->_addCommuns('directeur');

        /** CODIRECTION **********************************************************************************************/

        for ($i = 1; $i <= self::NBCODIR; $i++) {
            $this->_addCommuns('codirecteur' . $i);

            $this->add([
                'type' => Checkbox::class,
                'name' => $name = 'codirecteur' . $i . '-enabled',
                'options' => [
                    'label' => "Inclure ce·tte codirecteur·trice",
                ],
                'attributes' => [
                    'id' => $name,
                    'class' => 'codirecteur-enabler',
                    'data-codirecteur-id' => $i,
                    'title' => "Cochez cette case pour déclarer le·la codirecteur·trice n°$i"
                ]
            ]);

            $this->add([
                'type' => Checkbox::class,
                'name' => $name = 'codirecteur' . $i . '-principal',
                'options' => [
                    'label' => "Principal",
                ],
                'attributes' => [
                    'id' => $name,
                    'title' => "Principal ?"
                ]
            ]);

            if ($i >= 2) {
                $this->add([
                    'type' => Checkbox::class,
                    'name' => $name = 'codirecteur' . $i . '-exterieur',
                    'options' => [
                        'label' => "Extérieur",
                    ],
                    'attributes' => [
                        'id' => $name,
                        'title' => "Extérieur ?"
                    ]
                ]);
            }
        }
    }

    private function _addCommuns(string $prefixe)
    {
        $individu = new SearchAndSelect($prefixe . '-individu', ['label' => "Individu * :"]);
        $individu
            ->setAutocompleteSource($this->urlAutocompleteIndividu)
            ->setAttributes([
                'id' => $prefixe . '-individu',
                'placeholder' => "Recherchez l'individu...",
            ]);
        $this->add($individu);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => $prefixe . '-etablissement',
            'options' => [
                'label' => 'Établissement * :',
                'target_class' => Etablissement::class,
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure() && $targetEntity->getStructure()->getSigle() ? " (".$targetEntity->getStructure()->getSigle().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => $prefixe . '-etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'établissement",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => $prefixe . '-uniteRecherche',
            'options' => [
                'label' => 'Unité de recherche * :',
                'object_manager' => $this->structureService->getEntityManager(),
                'target_class' => UniteRecherche::class,
                'find_method' => [
                    'name' => 'findAll',
                    'params' => [],
                    'callback' => function() {
                        return $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle', false);
                    },
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure()?->getCode() ? " (".$targetEntity->getStructure()->getCode().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => $prefixe . '-uniteRecherche',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'unité de recherche",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => ObjectSelect::class,
            'name' => $prefixe . '-ecoleDoctorale',
            'options' => [
                'label' => 'École doctorale * :',
                'target_class' => EcoleDoctorale::class,
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure()?->getCode() ? " (".$targetEntity->getStructure()->getCode().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => $prefixe . '-ecoleDoctorale',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'école doctorale",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add([
            'type' => Select::class,
            'name' => $prefixe . '-qualite',
            'options' => [
                'label' => "Qualité * :",
                'value_options' => $this->qualiteService->getQualitesAsGroupOptions(),
                'empty_option' => "Sélectionner une qualité...",
            ],
            'attributes' => [
                'id' => $prefixe . '-qualite',
                'class' => 'select2',
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();
        $spec = [
            $name = 'directeur-individu' => [
                'name' => $name,
                'required' => $estModifiable,
            ],
            $name = 'directeur-etablissement' => [
                'name' => $name,
                'required' => $estModifiable,
            ],
            $name = 'directeur-ecoleDoctorale' => [
                'name' => $name,
                'required' => $estModifiable,
            ],
            $name = 'directeur-uniteRecherche' => [
                'name' => $name,
                'required' => $estModifiable,
            ],
            $name = 'directeur-qualite' => [
                'name' => $name,
                'required' => $estModifiable,
            ],
        ];

        for ($i = 1; $i <= self::NBCODIR; $i++) {
            $codirEnabled = (bool) $this->get('codirecteur' . $i . '-enabled')->getValue();

            $spec[$name = 'codirecteur' . $i . '-enabled'] = [
                'name' => $name,
                'required' => false,
            ];
            $spec[$name = 'codirecteur' . $i . '-individu'] = [
                'name' => $name,
                'required' => $codirEnabled,
            ];
            $spec[$name = 'codirecteur' . $i . '-etablissement'] = [
                'name' => $name,
                'required' => $codirEnabled,
            ];
            $spec[$name = 'codirecteur' . $i . '-ecoleDoctorale'] = [
                'name' => $name,
                'required' => $codirEnabled,
            ];
            $spec[$name = 'codirecteur' . $i . '-uniteRecherche'] = [
                'name' => $name,
                'required' => $codirEnabled,
            ];
            $spec[$name = 'codirecteur' . $i . '-qualite'] = [
                'name' => $name,
                'required' => $codirEnabled,
            ];
            $spec[$name = 'codirecteur' . $i . '-principal'] = [
                'name' => $name,
                'required' => false,
            ];
            $spec[$name = 'codirecteur' . $i . '-exterieur'] = [
                'name' => $name,
                'required' => false,
            ];
        }

        return $spec;
    }
}