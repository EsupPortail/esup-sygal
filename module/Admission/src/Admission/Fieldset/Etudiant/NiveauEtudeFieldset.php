<?php
namespace Admission\Fieldset\Etudiant;

use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class NiveauEtudeFieldset extends Fieldset implements InputFilterProviderInterface
{
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
        $this->add(
            (new Radio('niveau_etude'))
                ->setValueOptions([
                    1 => "Diplôme national tel que Master",
                    2 => "Autre - à titre dérogatoire (Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire)"
                ])
        );

        $this->add(
            (new Text('intitule_du_diplome'))
                ->setLabel("Intitulé")
        );

        $this->add(
            (new Select("annee_dobtention_diplome"))
                ->setLabel("Année d'obtention")
                ->setValueOptions($this->generateYearOptions())
        );

        $this->add(
            (new Text("etablissement_dobtention_diplome"))
                ->setLabel("Etablissement d'obtention")
        );

        $this->add(
            (new Radio('type_diplome_autre'))
                ->setValueOptions([
                    1 => "Diplôme obtenu à l'étranger",
                    2 => "Diplôme français ne conférant pas le grade de master"
                ])
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'niveau_etude' => [
                'name' => 'niveau_etude',
                'required' => false,
            ],
            'intitule_du_diplome' => [
                'name' => 'intitule_du_diplome',
                'required' => false,
            ],
            'annee_dobtention_diplome' => [
                'name' => 'annee_dobtention_diplome',
                'required' => false,
            ],
            'etablissement_dobtention_diplome' => [
                'name' => 'etablissement_dobtention_diplome',
                'required' => false,
            ],
            'type_diplome_autre' => [
                'name' => 'type_diplome_autre',
                'required' => false,
            ],
        ];
    }
}