<?php
namespace Admission\Fieldset\Financement;

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
            (new Radio('contrat_doctoral'))
                ->setLabel("Avez-vous un contrat doctoral ?")
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"
                ])
        );

        $this->add(
            (new Radio('employeur_contrat'))
                ->setValueOptions([
                    "universite" => "Université de Caen Normandie",
                    "region" => "Région Normandie (RIN 50%, RIN 100%)",
                    "autre" => "Autre employeur"
                ])
        );

        $this->add(
            (new Textarea('detail_contrat_doctoral'))
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'contrat_doctoral' => [
                'name' => 'contrat_doctoral',
                'required' => false,
            ],
            'employeur_contrat' => [
                'name' => 'employeur_contrat',
                'required' => false,
            ],
            'detail_contrat_doctoral' => [
                'name' => 'detail_contrat_doctoral',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}