<?php

namespace Depot\Form\Attestation;

use Application\Utils\FormUtils;
use Depot\Entity\Db\Attestation;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use These\Entity\Db\These;

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

    private $disableExemplaireImprimeConformeAVersionDeposee = false;

    /**
     * @param bool $disable
     */
    public function disableExemplaireImprimeConformeAVersionDeposee(bool $disable = true)
    {
        $this->disableExemplaireImprimeConformeAVersionDeposee = $disable;
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

        FormUtils::addSaveButton($this);
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

        if ($this->disableExemplaireImprimeConformeAVersionDeposee) {
            $this->remove('exemplaireImprimeConformeAVersionDeposee');
        }

        return $this;
    }

    /**
     * {@inheritDoc}
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
                'required'   => ! $this->disableExemplaireImprimeConformeAVersionDeposee,
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