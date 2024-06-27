<?php

namespace These\Fieldset\Financement;

use Application\Entity\Db\OrigineFinancement;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class FinancementFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use DisciplineServiceAwareTrait;

    private $originesFinancements = null;

    public function setOrigineFinancementsPossibles(array $originesFinancements): void
    {
        $options = [];
        foreach ($originesFinancements as $origine) {
            /** @var OrigineFinancement $origine */
            if(!in_array($origine->getLibelleLong(), $options)){
                $options[$origine->getId()] = $origine->getLibelleLong();
            }
        }
        $this->originesFinancements = $options;
        $this->get('origineFinancement')->setEmptyOption('Sélectionnez une option');
        $this->get('origineFinancement')->setValueOptions($this->originesFinancements);
    }

    // Méthode pour générer les options d'année
    protected function generateYearOptions() : array
    {
        $currentYear = date('Y');
        $options = [];
        for ($year = $currentYear; $year >= $currentYear - 50; $year--) {
            $options[$year] = $year;
        }
        return $options;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Select("annee"))
                ->setLabel("Année")
                ->setValueOptions($this->generateYearOptions())
                ->setEmptyOption("Sélectionner une année")
        );

        $this->add(
            (new Select("origineFinancement"))
                ->setLabel("Financement·s")
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setDisableInArrayValidator(true)
                ->setAttributes([
                    'class' => 'selectpicker show-tick form-control',
                    'data-live-search' => 'true',
                    'id' => 'financement',
                    'multiple' => 'true'
                ])
        );

        $this->add([
            'type' => Text::class,
            'name' => 'complementFinancement',
            'options' => [
                'label' => "Complément de financement",
            ],
        ]);

        $this->add([
            'name' => 'quotiteFinancement',
            'type' => Number::class,
            'options' => [
                'label' => "Quotité de financement",
            ],
            'attributes' => [
                'min' => 1,
                'max' => 100,
                'step' => 1,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'annee' => [
                'name' => 'annee',
                'required' => false,
            ],
            'origineFinancement' => [
                'name' => 'origineFinancement',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'quotiteFinancement' => [
                'name' => 'quotiteFinancement',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
        ];
    }
}