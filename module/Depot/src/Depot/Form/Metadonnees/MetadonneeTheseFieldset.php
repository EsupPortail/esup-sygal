<?php

namespace Depot\Form\Metadonnees;

use Application\Utils\FormUtils;
use Depot\Entity\Db\MetadonneeThese;
use Depot\Filter\MotsClesFilter;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 28/04/16
 * Time: 16:32
 */
class MetadonneeTheseFieldset extends Fieldset implements InputFilterProviderInterface
{
    const SEPARATEUR_MOTS_CLES = MetadonneeThese::SEPARATEUR_MOTS_CLES;
    const SEPARATEUR_MOTS_CLES_LIB = MetadonneeThese::SEPARATEUR_MOTS_CLES_LIB;

    /**
     * @var array
     */
    private $resumeMaxlength = [
        'resume'        => 4000,
        'resumeAnglais' => 4000,
    ];

    /**
     * @param int $resumeMaxlength
     * @return self
     */
    public function setResumeMaxlength($resumeMaxlength)
    {
        $this->resumeMaxlength = (int) $resumeMaxlength;

        return $this;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->add([
            'type'       => 'Textarea',
            'name'       => 'titre',
            'options'    => [
                'label' => 'Titre',
            ],
            'attributes' => [
                'rows' => 3,
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Select',
            'name'       => 'langue',
            'options'    => [
                'label'         => "Langue de la thèse <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'value_options' => MetadonneeThese::$langues,
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Textarea',
            'name'       => 'titreAutreLangue',
            'options'    => [
                'label' => "Titre en anglais <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'rows' => 3,
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Textarea',
            'name'       => $name = 'resume',
            'options'    => [
                'label' => "Résumé en <u>français</u> (" . ($ml = $this->resumeMaxlength[$name]) . " caractères maximum, espaces compris) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'label_options' => [
                    'disable_html_escape' => true,
                ]
            ],
            'attributes' => [
                'rows' => 8,
                'title' => "",
                'maxlength' => $ml,
            ],
        ]);

        $this->add([
            'type'       => 'Textarea',
            'name'       => $name = 'resumeAnglais',
            'options'    => [
                'label' => "Résumé en <u>anglais</u> (" . ($ml = $this->resumeMaxlength[$name]) . " caractères maximum, espaces compris) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'label_options' => [
                    'disable_html_escape' => true,
                ]
            ],
            'attributes' => [
                'rows' => 8,
                'title' => "",
                'maxlength' => $ml,
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'motsClesLibresFrancais',
            'options'    => [
                'label' => "Proposition de mots-clés en français <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'title' => sprintf("Mots-clés séparés par le caractère %s (%s)",
                    self::SEPARATEUR_MOTS_CLES,
                    self::SEPARATEUR_MOTS_CLES_LIB),
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'motsClesLibresAnglais',
            'options'    => [
                'label' => "Proposition de mots-clés en anglais <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span>",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'title' => sprintf("Mots-clés séparés par le caractère %s (%s)",
                    self::SEPARATEUR_MOTS_CLES,
                    self::SEPARATEUR_MOTS_CLES_LIB),
            ],
        ]);

        $this->add([
            'name' => 'id',
            'type' => 'Hidden',
        ]);

        $this->add([
            'name' => 'these',
            'type' => 'Hidden',
        ]);

        FormUtils::addSaveButton($this);
    }


    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
        \Locale::setDefault("fr_FR");

        return [
            'titre'          => [
                'required' => false,
            ],
            'langue'         => [
                'required' => true,
            ],
            'titreAutreLangue'   => [
                'required' => true,
            ],
            $name = 'resume'         => [
                'required' => true,
                'validators' => [
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->resumeMaxlength[$name]
                        ),
                    ),
                ]
            ],
            $name = 'resumeAnglais'  => [
                'required' => true,
                'validators' => [
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->resumeMaxlength[$name]
                        ),
                    ),
                ]
            ],
            'motsClesLibresFrancais' => [
                'required' => true,
                'filters' => [
                    new MotsClesFilter(['separator' => self::SEPARATEUR_MOTS_CLES]),
                ],
            ],
            'motsClesLibresAnglais' => [
                'required' => true,
                'filters' => [
                    new MotsClesFilter(['separator' => self::SEPARATEUR_MOTS_CLES]),
                ],
            ],
        ];
    }

    /**
     * Retourne le label du champ de saisie du titre dans une autre langue.
     * Il dépend de la langue sélectionnée.
     *
     * @param $langueSelectionnee
     * @return string
     */
    public function getLabelTitreAutreLangue($langueSelectionnee = null)
    {
        return !$langueSelectionnee || $langueSelectionnee === MetadonneeThese::LANGUE_FRANCAIS ?
            "Titre en anglais" :
            "Titre en français";
    }
}