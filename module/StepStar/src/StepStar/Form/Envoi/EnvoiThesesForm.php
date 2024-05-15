<?php

namespace StepStar\Form\Envoi;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;

class EnvoiThesesForm extends AbstractEnvoiForm
{
    public function init(): void
    {
        parent::init();

        $this->add((new Text('these'))
            ->setLabel("Numéros de thèses (séparés par une virgule) :")
        );

        $this->add((new Checkbox('force'))
            ->setLabel("Envoyer la thèse même si son fichier TEF n'a pas changé depuis le dernier envoi")
        );

        $this->add((new Text('tag'))
            ->setLabel("Tag éventuel (pour retrouver facilement un ensemble de logs) :")
        );

        $this->add((new Checkbox('clean'))
            ->setLabel("Une fois l'envoi effectué, supprimer les fichiers XML temporaires générés")
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'these' => [
                'name' => 'these',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
            'force' => [
                'name' => 'force',
                'required' => false,
            ],
            'clean' => [
                'name' => 'clean',
                'required' => false,
            ],
        ]);
    }
}