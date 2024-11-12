<?php

namespace These\Fieldset\Acteur;

use Application\Entity\Db\Role;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use These\Rule\ActeurRule;
use UnicaenApp\Filter\SearchAndSelectFilter;
use UnicaenApp\Form\Element\SearchAndSelect;
use Webmozart\Assert\Assert;

/**
 * @property \These\Entity\Db\Acteur $object
 * @property \These\Fieldset\Acteur\ActeurHydrator $hydrator
 */
class ActeurFieldset extends Fieldset implements InputFilterProviderInterface
{
    use QualiteServiceAwareTrait;

    private string $urlIndividu;
    private string $urlEtablissement;
    private array $unitesRecherches;
    private array $roles = [];
    private ActeurRule $acteurRule;

    public function __construct($name = null, array $options = [])
    {
        parent::__construct('acteur', $options);

        $this->acteurRule = new ActeurRule();
    }

    /**
     * @param string|null $urlIndividu
     */
    public function setUrlIndividu(?string $urlIndividu): void
    {
        $this->urlIndividu = $urlIndividu;
    }

    /**
     * @param string|null $urlEtablissement
     */
    public function setUrlEtablissement(?string $urlEtablissement): void
    {
        $this->urlEtablissement = $urlEtablissement;
    }

    public function setRoles(array $roles): void
    {
        Assert::allIsInstanceOf($roles, Role::class);
        $this->roles = $roles;
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche[] $unitesRecherches
     */
    public function setUnitesRecherches(array $unitesRecherches): void
    {
        Assert::allIsInstanceOf($unitesRecherches, UniteRecherche::class);
        $this->unitesRecherches = $unitesRecherches;
    }

    public function init(): void
    {
        $this->add([
            'type' => Select::class,
            'name' => 'role',
            'options' => [
                'label' => "Rôle :",
                'value_options' => [], // dépend du contexte (cf. controller)
                'empty_option' => "Sélectionnez le rôle",
            ],
            'attributes' => [
                'id' => 'role',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ],
        ]);

        $individu = new SearchAndSelect('individu', ['label' => "Individu :"]);
        $individu
            ->setAutocompleteSource($this->urlIndividu)
            ->setAttributes([
                'id' => 'individu',
                'placeholder' => "Rechercher l'individu...",
            ]);
        $this->add($individu);

        $etablissement = new SearchAndSelect('etablissement', ['label' => "Établissement :"]);
        $etablissement
            ->setAutocompleteSource($this->urlEtablissement)
            ->setAttributes([
                'id' => 'etablissement',
                'placeholder' => "Rechercher l'etablissement...",
            ]);
        $this->add($etablissement);

        $etablissementForce = new SearchAndSelect('etablissementForce', ['label' => "Établissement forcé :"]);
        $etablissementForce
            ->setAutocompleteSource($this->urlEtablissement)
            ->setAttributes([
                'id' => 'etablissementForce',
                'placeholder' => "Rechercher l'etablissement...",
            ]);
        $this->add($etablissementForce);

        $this->add([
            'type' => Select::class,
            'name' => 'uniteRecherche',
            'options' => [
                'label' => "Unité de recherche :",
                'value_options' => UniteRecherche::toValueOptions($this->unitesRecherches),
                'empty_option' => "Sélectionnez l'unité de recherche",
            ],
            'attributes' => [
                'id' => 'uniteRecherche',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
                'data-bs-html' => 'true',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité :",
            ],
            'attributes' => [
                'id' => 'qualite',
            ]
        ]);
        $this->add([
            'type' => Select::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité :",
                'value_options' => $this->qualiteService->getQualitesAsGroupOptions(),
                'empty_option' => "Sélectionner une qualité...",
            ],
            'attributes' => [
                'id' => 'qualite',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
            ]
        ]);
    }

    public function prepareElement(FormInterface $form): void
    {
        /** @var \Laminas\Form\Element\Select $roleSelect */
        $roleSelect = $this->get('role');
        $roleSelect->setValueOptions(Role::toValueOptions($this->roles));

        $this->acteurRule->setActeur($this->object);
        $this->acteurRule->prepareActeurFieldset($this);

        parent::prepareElement($form);
    }

    public function getInputFilterSpecification(): array
    {
        $spec = [
            'role' => [
                'filters' => [
                    ['name' => ToNull::class],
                ],
            ],
            'individu' => [
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                    ['name' => ToNull::class],
                ],
            ],
            'etablissement' => [
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                    ['name' => ToNull::class],
                ],
            ],
            'etablissementForce' => [
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                    ['name' => ToNull::class],
                ],
            ],
            'uniteRecherche' => [
                'filters' => [
                    ['name' => ToNull::class],
                ],
            ],
            'qualite' => [
                'filters' => [
                    ['name' => SearchAndSelectFilter::class],
                    ['name' => ToNull::class],
                ],
            ],
        ];

        $this->acteurRule->setActeur($this->object);

        return $this->acteurRule->prepareActeurInputFilterSpecification($spec);
    }
}