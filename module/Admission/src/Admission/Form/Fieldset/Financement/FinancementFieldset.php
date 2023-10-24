<?php
namespace Admission\Form\Fieldset\Financement;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class FinancementFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {

        $this->add(
            (new Radio('contratDoctoral'))
                ->setLabel("Avez-vous un contrat doctoral ?")
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"
                ])
        );

        $this->add(
            (new Radio('employeurContrat'))
                ->setValueOptions([
                    "universite" => "Université de Caen Normandie",
                    "region" => "Région Normandie (RIN 50%, RIN 100%)",
                    "autre" => "Autre employeur"
                ])
        );

        $this->add(
            (new Textarea('detailContratDoctoral'))
        );

        $this->add(
            (new Textarea('verificationFinancement'))
                ->setLabel("Observations (Non enregistrées encore en base de données)")
                ->setAttributes([
                    "class" => "text_observations_gestionnaire"
                ])
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'contratDoctoral' => [
                'name' => 'contratDoctoral',
                'required' => false,
            ],
            'employeurContrat' => [
                'name' => 'employeurContrat',
                'required' => false,
            ],
            'detailContratDoctoral' => [
                'name' => 'detailContratDoctoral',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}