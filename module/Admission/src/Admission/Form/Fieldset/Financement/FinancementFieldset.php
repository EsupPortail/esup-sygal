<?php
namespace Admission\Form\Fieldset\Financement;

use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Application\Entity\Db\OrigineFinancement;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class FinancementFieldset extends Fieldset implements InputFilterProviderInterface
{
    private $financements = null;

    public function setFinancements(array $financements): void
    {
        $options = [];
        foreach ($financements as $origine) {
            /** @var OrigineFinancement $origine */
            if(!in_array($origine->getLibelleLong(), $options)){
                $options[$origine->getId()] = $origine->getLibelleLong();
            }
        }
        $this->financements = $options;
        $this->get('financement')->setEmptyOption('Sélectionnez une option');
        $this->get('financement')->setValueOptions($this->financements);
    }

    public function init()
    {

        $this->add(
            (new Radio('contratDoctoral'))
                ->setLabel("Avez-vous un contrat doctoral ?")
                ->setLabelAttributes(['data-after' => " / Do you have a PhD contract?"])
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"
                ])
                ->setLabelAttributes(['data-after' => " / Name of thesis supervisor"])
        );

        $this->add(
            (new Select("financement"))
                ->setLabel("Financement")
                ->setLabelAttributes(['data-after' => " / Funding"])
                ->setOptions(['emptyOption' => 'Choisissez un élément',])
                ->setAttributes([
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => 'financement',
                ])
        );

        $this->add(
            (new Textarea('detailContratDoctoral'))
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationFinancement");
        $this->add($verificationFieldset);
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
            'financement' => [
                'name' => 'financement',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
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