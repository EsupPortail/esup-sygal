<?php

namespace Structure\Form\Element;

use Laminas\Form\Factory;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Form\Element\Select2;
use Webmozart\Assert\Assert;

/**
 * Le nécessaire pour instancier/initialiser un élément de formulaire Select2 pour la sélection
 * d'un {@see \Structure\Entity\Db\Etablissement}.
 */
trait ProvidesEtablissementSelectTrait
{
    protected array $selectableEtablissements;

    /**
     * Spécifie les établissements sélectionnables.
     */
    public function setSelectableEtablissements(array $etablissements): void
    {
        Assert::allIsInstanceOf($etablissements, Etablissement::class);
        $this->selectableEtablissements = $etablissements;
    }

    /**
     * Instancie le Select par défaut.
     * Possibilité de surcharger les specs de création.
     */
    protected function createEtablissementSelect(string $name, array $elementSpec = []): Select2
    {
        // génération des 'value options' à partir de la liste des établissements sélectionnables fournie
        $valueOptions = array_map(fn(Etablissement $etablissement) => (object) [
            'value' => $etablissement->getId(),
            'text' => $etablissement->getStructure()->getLibelle(),
            'attributes' => [
                'data-extra' => $etablissement->getStructure()->getSigle(),
                'data-sourcecode' => $etablissement->getSourceCode(), // exploité pour filtrer les versions de diplomes
            ],
        ], $this->selectableEtablissements);

        /** @var Select2 $select */
        $select = (new Factory())->createElement(array_merge_recursive([
            'type' => Select2::class,
            'name' => $name,
            'options' => [
                'label' => "Établissement d'inscription :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
                'value_options' => $valueOptions,
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