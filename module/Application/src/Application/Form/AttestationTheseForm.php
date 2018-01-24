<?php

namespace Application\Form;

use Application\Entity\Db\Attestation;
use Application\Entity\Db\These;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class AttestationTheseForm extends Form implements InputFilterProviderInterface
{
    protected $text = "L’auteur certifie que...";

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @var These
     */
    private $these;

    /**
     * @param These $these
     * @return static
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new Attestation());

        $this->add([
            'type'       => 'Checkbox',
            'name'       => 'versionDeposeeEstVersionRef',
            'options'    => [
                'label'              => "la version déposée sous forme numérique constitue la version de référence de la thèse",
                'use_hidden_element' => true,
                'unchecked_value'    => '' // indispensable car validateur NotEmpty
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Checkbox',
            'name'       => 'exemplaireImprimeConformeAVersionDeposee',
            'options'    => [
                'label'              => "l’exemplaire imprimé fourni est conforme à la version numérique déposée",
                'use_hidden_element' => true,
                'unchecked_value'    => '' // indispensable car validateur NotEmpty
            ],
            'attributes' => [
                'title' => "",
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

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    /**
     * @return Form
     */
    public function prepare()
    {
        parent::prepare();

        /** @var Attestation $attestation */
        $attestation = $this->getObject();

        $this->get('versionDeposeeEstVersionRef')->setLabel(sprintf(
            "la version %s déposée sous forme numérique constitue la version de référence de la thèse",
            $attestation->getThese()->getCorrectionAutorisee() ? "corrigée" : ""
        ));
        $this->get('exemplaireImprimeConformeAVersionDeposee')->setLabel(sprintf(
            "l’exemplaire imprimé fourni est conforme à la version numérique %s déposée",
            $attestation->getThese()->getCorrectionAutorisee() ? "corrigée" : ""
        ));

        return $this;
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            $name = 'versionDeposeeEstVersionRef' => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez cocher la case"],
                        ],
                    ],
                ],
            ],
            $name = 'exemplaireImprimeConformeAVersionDeposee' => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez cocher la case"],
                        ],
                    ],
                ],
            ],
        ];
    }
}