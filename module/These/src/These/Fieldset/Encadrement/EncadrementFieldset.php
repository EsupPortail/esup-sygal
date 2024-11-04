<?php

namespace These\Fieldset\Encadrement;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\SearchAndSelect;

class EncadrementFieldset extends Fieldset implements InputFilterProviderInterface
{

    const NB_COENCADRANTS_MAXI = 3;

    private string $urlCoEncadrant;

    public function setUrlCoEncadrant(string $urlCoEncadrant): void
    {
        $this->urlCoEncadrant = $urlCoEncadrant;
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        /** COENCADREMENT ****************************************************************************************/
        for ($i = 1; $i <= self::NB_COENCADRANTS_MAXI; $i++) {
            $coEncadrant = new SearchAndSelect('coencadrant' . $i . '-individu', ['label' => "Coencadrant·e :"]);
            $coEncadrant
                ->setAutocompleteSource($this->urlCoEncadrant)
                ->setSelectionRequired(true)
                ->setAttributes([
                    'id' => 'coencadrant' . $i,
                    'placeholder' => "Sélectionner un coencadrant ... ",
                ]);
            $this->add($coEncadrant);
        }
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        $spec = [];
        for ($i = 1; $i <= self::NB_COENCADRANTS_MAXI; $i++) {
            $spec['coencadrant' . $i . '-individu'] = [
                'name' => 'coencadrant-individu' . $i,
                'required' => false,
            ];
        }

        return $spec;
    }
}