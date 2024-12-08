<?php

namespace Soutenance\Form\QualiteEdition;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputProviderInterface;
use Soutenance\Entity\Qualite;

class QualiteEditionForm extends Form implements InputProviderInterface
{
    public function init()
    {
        $this->add(
            (new Text('libelle'))
                ->setLabel("Libelle :")
        );

        $this->add(
            (new Radio('rang'))
                ->setLabel("Rang :")
                ->setValueOptions(['A' => 'A', 'B' => 'B', 'aucun' => Qualite::RANG_LIBELLE_AUCUN]))
        ;

        $this->add(
            (new Radio('hdr'))
                ->setLabel("Possède une habilitation à diriger des recherches :")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        $this->add(
            (new Radio('emeritat'))
                ->setLabel("Possède un émeritat :")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        $this->add(
            (new Radio('justificatif'))
                ->setLabel("Nécessite un justificatif (chercheur&middot;e étranger&middot;ère):")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        FormUtils::addSaveButton($this);
    }

    public function getInputSpecification(): array
    {
        return [
            'libelle' => [
                'required' => true,
            ],
            'rang' => [
                'required' => true,
            ],
            'hdr' => [
                'required' => true,
            ],
            'emeritat' => [
                'required' => true,
            ],
            'justificatif' => [
                'required' => true,
            ],
        ];
    }
}