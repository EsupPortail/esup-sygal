<?php
namespace Admission\Fieldset\Inscription;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\SearchAndSelect;

class InformationsInscriptionFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {

        $this->add(
            (new Select("discipline_doctorat"))
                ->setLabel("Code et libellé de la discipline d'inscription en doctorat souhaitée")
                ->setOptions(['empty_option' => 'Choisissez un élément',])
        );

        $this->add(
            (new Select("composante_doctorat"))
                ->setLabel("Composante de rattachement (U.F.R., instituts…)")
                ->setOptions(['empty_option' => 'Choisissez un élément',])
        );

        $this->add(
            (new Select("ecole_doctorale"))
                ->setLabel("Ecole doctorale")
                ->setOptions(['empty_option' => 'Choisissez un élément',])
        );

        $this->add(
            (new Select("unite_recherche"))
                ->setLabel("Unité de recherche")
                ->setOptions(['empty_option' => 'Choisissez un élément',])
        );

        $nom_directeur_thèse = new SearchAndSelect('nom_directeur_thèse', ['label' => "Nom du directeur de thèse"]);
        $nom_directeur_thèse
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nom_directeur_thèse',
            ]);
        $this->add($nom_directeur_thèse);

        $nom_codirecteur_thèse = new SearchAndSelect('nom_codirecteur_thèse', ['label' => "Nom du co-directeur de thèse"]);
        $nom_codirecteur_thèse
            ->setSelectionRequired()
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'nom_directeur_thèse',
            ]);
        $this->add($nom_codirecteur_thèse);

        $this->add(
            (new Textarea('titre_these'))
                ->setLabel("Titre de la thèse")
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'discipline_doctorat' => [
                'name' => 'discipline_doctorat',
                'required' => false,
            ],
            'composante_doctorat' => [
                'name' => 'composante_doctorat',
                'required' => false,
            ],
            'ecole_doctorale' => [
                'name' => 'ecole_doctorale',
                'required' => false,
            ],
            'unite_recherche' => [
                'name' => 'unite_recherche',
                'required' => false,
            ],
            'nom_directeur_thèse' => [
                'name' => 'nom_directeur_thèse',
                'required' => false,
            ],
            'nom_codirecteur_thèse' => [
                'name' => 'nom_codirecteur_thèse',
                'required' => false,
            ],
            'titre_these' => [
                'name' => 'titre_these',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}