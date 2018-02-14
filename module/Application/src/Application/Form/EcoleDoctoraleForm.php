<?php

namespace Application\Form;

use Application\Entity\Db\EcoleDoctorale;
use Zend\Form\Annotation\InputFilter;
use Zend\Form\Element;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Image;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\FileInput;

class EcoleDoctoraleForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new EcoleDoctorale());

        $this
            ->add(new Hidden('id'));

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
                new Hidden('sourceCode'))
                    ->setLabel("Code :")
        );
        $this
            ->add((
                new Element\File('cheminLogo'))
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
            /*'sourceCode' => [
                'name' => 'sourceCode',
                'required' => true,
            ],*/
        ]));
    }
}