<?php

namespace Acteur\Fieldset\ActeurThese;

use Acteur\Fieldset\AbstractActeurFieldset;
use Acteur\Rule\ActeurThese\ActeurTheseRule;
use Laminas\Filter\ToNull;
use UnicaenApp\Filter\SearchAndSelectFilter;
use UnicaenApp\Form\Element\SearchAndSelect;

/**
 * @property \Acteur\Entity\Db\ActeurThese $object
 * @property \Acteur\Rule\ActeurThese\ActeurTheseRule $acteurRule
 */
class ActeurTheseFieldset extends AbstractActeurFieldset
{
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        $this->acteurRule = new ActeurTheseRule();
    }

    public function init(): void
    {
        parent::init();

        $etablissementForce = new SearchAndSelect('etablissementForce', ['label' => "Établissement forcé :"]);
        $etablissementForce
            ->setAutocompleteSource($this->urlEtablissement)
            ->setAttributes([
                'id' => 'etablissementForce',
                'placeholder' => "Rechercher l'etablissement...",
            ]);
        $this->add($etablissementForce);
    }

    public function getInputFilterSpecification(): array
    {
        $spec = [
            'etablissementForce' => [
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                    ['name' => ToNull::class],
                ],
            ]
        ];

        $this->acteurRule->setActeur($this->object);

        return $this->acteurRule->prepareActeurInputFilterSpecification($spec);
    }
}