<?php

namespace These\Fieldset\Direction;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilterProviderInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect2;

class DirectionFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;

    const NBCODIR = 2;

    private string $urlAutocompleteIndividu;
    private string $urlAutocompleteEtablissement;
    private string $urlAutocompleteEcoleDoctorale;
    private string $urlAutocompleteUniteRecherche;

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
        $individu = new SearchAndSelect2($prefixe . '-individu', ['label' => "Individu :"]);
        $individu
            ->setAutocompleteSource($this->urlAutocompleteIndividu)
            ->setAttributes([
                'id' => $prefixe . '-individu',
                'placeholder' => "Recherchez l'individu...",
            ]);
        $this->add($individu);

        $etab = new SearchAndSelect2($prefixe . '-etablissement', ['label' => "Établissement :"]);
        $etab
            ->setAutocompleteSource($this->urlAutocompleteEtablissement)
            ->setAttributes([
                'id' => $prefixe . '-etablissement',
                'placeholder' => "Recherchez l'établissement...",
            ]);
        $this->add($etab);

        $ed = new SearchAndSelect2($prefixe . '-ecoleDoctorale', ['label' => "École doctorale :"]);
        $ed
            ->setAutocompleteSource($this->urlAutocompleteEcoleDoctorale)
            ->setAttributes([
                'id' => $prefixe . '-ecoleDoctorale',
                'placeholder' => "Recherchez l'école doctorale...",
            ]);
        $this->add($ed);

        $ur = new SearchAndSelect2($prefixe . '-uniteRecherche', ['label' => "Unité de recherche :"]);
        $ur
            ->setAutocompleteSource($this->urlAutocompleteUniteRecherche)
            ->setAttributes([
                'id' => $prefixe . '-uniteRecherche',
                'placeholder' => "Recherchez l'unité de recherche...",
            ]);
        $this->add($ur);

        $this->add([
            'type' => Select::class,
            'name' => $prefixe . '-qualite',
            'options' => [
                'label' => "Qualité :",
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
        $spec = [
            $name = 'directeur-individu' => [
                'name' => $name,
                'required' => true,
            ],
            $name = 'directeur-etablissement' => [
                'name' => $name,
                'required' => true,
            ],
            $name = 'directeur-ecoleDoctorale' => [
                'name' => $name,
                'required' => true,
            ],
            $name = 'directeur-uniteRecherche' => [
                'name' => $name,
                'required' => true,
            ],
            $name = 'directeur-qualite' => [
                'name' => $name,
                'required' => true,
            ],
        ];

        for ($i = 1; $i <= self::NBCODIR; $i++) {
            $codirEnabled = (bool) $this->get('codirecteur' . $i . '-enabled')->getValue();

            $spec[$name = 'codirecteur' . $i . '-enable'] = [
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