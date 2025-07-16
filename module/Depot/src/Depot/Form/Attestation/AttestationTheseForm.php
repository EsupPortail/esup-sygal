<?php

namespace Depot\Form\Attestation;

use Application\Utils\FormUtils;
use Depot\Entity\Db\Attestation;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class AttestationTheseForm extends Form implements InputFilterProviderInterface
{
    protected bool $depotExemplairePapierTheseEnabled;
    protected string $text = "L’auteur certifie que...";

    public function setDepotExemplairePapierTheseEnabled(?bool $enabled = true): static
    {
        $this->depotExemplairePapierTheseEnabled = $enabled;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init(): void
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

    public function prepare(): static
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

        if (! $this->depotExemplairePapierTheseEnabled) {
            $this->remove('exemplaireImprimeConformeAVersionDeposee');
        }

        return $this;
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'versionDeposeeEstVersionRef' => [
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
            'exemplaireImprimeConformeAVersionDeposee' => [
                'required'   => $this->depotExemplairePapierTheseEnabled,
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