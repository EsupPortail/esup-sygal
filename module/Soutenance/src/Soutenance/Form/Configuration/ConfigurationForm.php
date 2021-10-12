<?php

namespace Soutenance\Form\Configuration;

use Soutenance\Entity\Parametre;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;

class ConfigurationForm extends Form {

    public function init()
    {
        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_AVIS_DEADLINE,
                'options' => [
                    'label' => "Delai avant le retour des rapports : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_AVIS_DEADLINE,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_SIZE_MIN,
                'options' => [
                    'label' => "Nombre minimal de membre dans le jury : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_SIZE_MIN,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_SIZE_MAX,
                'options' => [
                    'label' => "Nombre maximal de membre dans le jury : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_SIZE_MAX,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_RAPPORTEUR_SIZE_MIN,
                'options' => [
                    'label' => "Nombre minimal de rapporteur : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_RAPPORTEUR_SIZE_MIN,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_RANGA_RATIO_MIN,
                'options' => [
                    'label' => "Ratio minimal de membre de rang A : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_RANGA_RATIO_MIN,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_EXTERIEUR_RATIO_MIN,
                'options' => [
                    'label' => "Ratio minimal de membre exterieur : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_EXTERIEUR_RATIO_MIN,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_JURY_PARITE_RATIO_MIN,
                'options' => [
                    'label' => "Ratio minimal de parité : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_JURY_PARITE_RATIO_MIN,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_FORMULAIRE_DELOCALISATION,
                'options' => [
                    'label' => "Formulaire de délocalisation de la soutenance : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_FORMULAIRE_DELOCALISATION,
                ],
            ]
        );
        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_FORMULAIRE_DELEGUATION,
                'options' => [
                    'label' => "Formulaire de délégation de signature : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_FORMULAIRE_DELEGUATION,
                ],
            ]
        );
        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN,
                'options' => [
                    'label' => "Formulaire de demande de label européen : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN,
                ],
            ]
        );
        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_FORMULAIRE_THESE_ANGLAIS,
                'options' => [
                    'label' => "Formulaire de demande de rédaction en anglais : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_FORMULAIRE_THESE_ANGLAIS,
                ],
            ]
        );
        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_FORMULAIRE_CONFIDENTIALITE,
                'options' => [
                    'label' => "Formulaire de demande de confidentialité : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_FORMULAIRE_CONFIDENTIALITE,
                ],
            ]
        );

        $this->add(
            [
                'type' => Text::class,
                'name' => Parametre::CODE_DIRECTEUR_INTERVENTION,
                'options' => [
                    'label' => "Durée permettant aux directeurs d'intervenir [-j jour:+j jour] : ",
                ],
                'attributes' => [
                    'id' => Parametre::CODE_DIRECTEUR_INTERVENTION,
                ],
            ]
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}