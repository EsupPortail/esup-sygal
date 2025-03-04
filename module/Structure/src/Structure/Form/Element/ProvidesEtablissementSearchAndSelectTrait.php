<?php

namespace Structure\Form\Element;

use Laminas\Form\Factory;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Form\Element\SearchAndSelect2;
use Webmozart\Assert\Assert;

/**
 * Le nécessaire pour instancier/initialiser un élément de formulaire SearchAndSelect2 pour la recherche et sélection
 * d'un {@see \Structure\Entity\Db\Etablissement}.
 */
trait ProvidesEtablissementSearchAndSelectTrait
{
    private array $selectedEtablissements = [];

    /**
     * Spécifie les établissements pré-sélectionnés.
     */
    public function setSelectedEtablissements(array $selectedEtablissements): void
    {
        Assert::allIsInstanceOf($selectedEtablissements, Etablissement::class);
        $this->selectedEtablissements = $selectedEtablissements;
    }

    /**
     * Instancie le Select par défaut.
     * Possibilité de surcharger les specs de création.
     */
    protected function createEtablissementSearchAndSelect(string $name, array $elementSpec = []): SearchAndSelect2
    {
        // génération des 'value options' pré-sélectionnées à partir de la liste fournie
        $selectedValueOptions = array_map(fn(Etablissement $etablissement) => (object) [
            'value' => $etablissement->getId(),
            'text' => $etablissement->getStructure()->getLibelle(),
            'attributes' => [
                'data-extra' => $etablissement->getStructure()->getSigle(),
                'data-sourcecode' => $etablissement->getSourceCode(), // exploité pour filtrer les versions de diplomes
            ],
        ], $this->selectedEtablissements);

        /** @var SearchAndSelect2 $select */
        $select = (new Factory())->createElement(array_merge_recursive([
            'type' => SearchAndSelect2::class,
            'name' => $name,
            'options' => [
                'label' => "Établissement d'inscription :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
                'value_options' => $selectedValueOptions,
                'empty_option' => "Aucune sélection",

            ],
            'attributes' => [
                'id' => $name,
                'multiple' => false,
                'placeholder' => "Sélectionner l'établissement...",
            ],
        ], $elementSpec));

        return $select;
    }
}