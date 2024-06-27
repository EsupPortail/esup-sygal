<?php

namespace These\Fieldset\Encadrement;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect;

class EncadrementFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;

    const NBCODIR = 3;
    const NB_COENCADRANTS_MAXI = 3;

    private string $urlDirecteur;

    public function setUrlDirecteur(string $urlDirecteur): void
    {
        $this->urlDirecteur = $urlDirecteur;
    }

    private string $urlCoEncadrant;

    public function setUrlCoEncadrant(string $urlCoEncadrant): void
    {
        $this->urlCoEncadrant = $urlCoEncadrant;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        /**  DIRECTION  ***********************************************************************************************/
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
                'value_options' => $this->getEtablissementService()->getEtablissementInscriptionAsOption(),
                'empty_option' => "Sélectionner un établissement",
            ],
            'attributes' => [
                'id' => 'directeur-etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ],
        ]);

        /** CODIRECTION **********************************************************************************************/
        for ($i = 1; $i <= self::NBCODIR; $i++) {
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
                    'value_options' => $this->getEtablissementService()->getEtablissementInscriptionAsOption(),
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

        /** COENCADREMENT ****************************************************************************************/
        for ($i = 1; $i <= self::NB_COENCADRANTS_MAXI; $i++) {
            $coEncadrant = new SearchAndSelect('coencadrant' . $i . '-individu', ['label' => "Coencadrant·e :"]);
            $coEncadrant
                ->setAutocompleteSource($this->urlCoEncadrant)
                ->setSelectionRequired(true)
                ->setAttributes([
                    'id' => 'coencadrant' . $i,
                    'placeholder' => "Sélectionner un coencadrant ... ",
                ]);
            $this->add($coEncadrant);
        }
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        $spec = [
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
        ];

        for ($i = 1; $i <= self::NBCODIR; $i++) {
            $spec['codirecteur' . $i . '-individu'] = [
                'name' => 'codirecteur-individu' . $i,
                'required' => false,
            ];
            $spec['codirecteur' . $i . '-qualite'] = [
                'name' => 'codirecteur-qualite' . $i,
                'required' => false,
            ];
            $spec['codirecteur' . $i . '-etablissement'] = [
                'name' => 'codirecteur-etablissement' . $i,
                'required' => false,
            ];
        }

        for ($i = 1; $i <= self::NB_COENCADRANTS_MAXI; $i++) {
            $spec['coencadrant' . $i . '-individu'] = [
                'name' => 'coencadrant-individu' . $i,
                'required' => false,
            ];
        }

        return $spec;
    }
}