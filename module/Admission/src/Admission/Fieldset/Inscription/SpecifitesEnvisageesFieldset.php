<?php
namespace Admission\Fieldset\Inscription;

use Laminas\Filter\Digits;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Radio;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use UnicaenApp\Form\Element\SearchAndSelect;

class SpecifitesEnvisageesFieldset extends Fieldset implements InputFilterProviderInterface
{
    private $dateDuJourFormatee;
    private $dateDans10Ans;

    private ?string $urlPaysNationalite = null;

    public function setUrlPaysNationalite(string $urlPaysNationalite): void
    {
        $this->$urlPaysNationalite = $urlPaysNationalite;
        $this->get('pays_co-tutelle')->setAutocompleteSource($this->$urlPaysNationalite);
    }

    public function __construct($name = null)
    {
        parent::__construct($name);

        // Obtenez la date actuelle au format "YYYY-MM-DD"
        $aujourdHui = new \DateTime();
        $this->dateDuJourFormatee = $aujourdHui->format('Y-m-d');
        // Ajoutez 10 ans à la date actuelle
        $aujourdHui->add(new \DateInterval('P10Y'));

        // Formatez la date résultante au format "YYYY-MM-DD"
        $this->dateDans10Ans = $aujourdHui->format('Y-m-d');
    }

    public function init()
    {
        $this->add(
            (new Radio('confidentialite'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Confidentialité souhaitée")
        );

        $this->add(
            (new Date('date_confidentialité'))
                ->setLabel("Date de fin de confidentialité souhaitée (limitée à 10 ans)")
                ->setAttributes([
                    'min'  => $this->dateDuJourFormatee,
                    'max'  => $this->dateDans10Ans,
                    'step' => '1', // days; default step interval is 1 day
                ])
        );

        $this->add(
            (new Radio('co_tutelle'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Cotutelle envisagée")
        );

        $paysNationalite = new SearchAndSelect('pays_co-tutelle', ['label' => "Pays concerné"]);
        $paysNationalite
            ->setAutocompleteSource($this->urlPaysNationalite)
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'pays_co-tutelle',
            ]);
        $this->add($paysNationalite);

        $this->add(
            (new Radio('co_encadrement'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-encadrement envisagé")
        );

        $this->add(
            (new Radio('co-direction'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non"])
                ->setLabel("Co-direction demandée")
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'confidentialite' => [
                'name' => 'confidentialite',
                'required' => false,
            ],
            'date_confidentialité' => [
                'name' => 'date_confidentialité',
                'required' => false,
            ],
            'co_tutelle' => [
                'name' => 'co_tutelle',
                'required' => false,
            ],
            'pays_co-tutelle' => [
                'name' => 'pays_co-tutelle',
                'required' => false ,
            ],
            'co_encadrement' => [
                'name' => 'co_encadrement',
                'required' => false,
            ],
            'co-direction' => [
                'name' => 'co-direction',
                'required' => false,
            ],
        ];
    }
}