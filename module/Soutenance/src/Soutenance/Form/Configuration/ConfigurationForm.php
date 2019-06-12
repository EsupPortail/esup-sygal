<?php

namespace Soutenance\Form\Configuration;

use Soutenance\Entity\Parametre;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

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

        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}