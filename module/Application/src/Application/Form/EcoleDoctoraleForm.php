<?php

namespace Application\Form;

use Application\Entity\Db\EcoleDoctorale;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\File\Extension;

class EcoleDoctoraleForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new EcoleDoctorale());

        $this->add((
            new Text('sigle'))
                ->setLabel("Sigle :")
        );

        $this
            ->add((
                new Text('libelle'))
                    ->setLabel("Libellé :")
        );

        $this
            ->add((
                new Text('code'))
                    ->setLabel("Code :")
        );

        $this->add((
        new Text('id_ref'))
            ->setLabel("IdREF :")
        );

        $this->add((
        new Text('theme'))
            ->setLabel("Thème :")
        );

        $this->add((
        new Text('offre-these'))
            ->setLabel("Lien vers l'offre de thèse :")
        );

        $this->add(
            (new Checkbox('estFerme'))
                ->setLabel("École doctorale fermée")
        );
        $this
            ->add((
                new File('cheminLogo'))
                ->setLabel('Logo de l\'école doctorale :')
            );
        $this
            ->add((
            new Submit('supprimer-logo'))
                ->setValue("Supprimer le logo")
                ->setAttribute('class', 'btn btn-danger')
            );

        $this
            ->add((
                new Submit('submit'))
                    ->setValue("Enregistrer")
                    ->setAttribute('class', 'btn btn-primary')
        );


        $this->setInputFilter((new Factory())->createInputFilter([
            'sigle' => [
                'name' => 'sigle',
                'required' => true,
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
            ],
            'id_ref' => [
                'name' => 'id_ref',
                'required' => false,
            ],
            'code' => [
                'name' => 'code',
                'required' => true,
            ],
            'cheminLogo' => [
                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['bmp', 'png', 'jpg', 'jpeg'],
//                            'case' => false,
//                        ],
//                        'break_chain_on_failure' => true,
//                    ],
//                ],
            ],
        ]));
    }
}