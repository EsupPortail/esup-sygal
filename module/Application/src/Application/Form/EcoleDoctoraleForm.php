<?php

namespace Application\Form;

use Application\Entity\Db\EcoleDoctorale;
use Zend\Form\Element;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Image;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class EcoleDoctoraleForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new EcoleDoctorale());

        $this
            ->add(new Text('id'));

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
                new Text('sourceCode'))
                    ->setLabel("Code :")
        );
        $this
            ->add((
            new Text('cheminLogo'))
                ->setLabel("Logo :")
            );

        $this
            ->add((
            new Element\Image('logo'))
                ->setAttribute('src',APPLICATION_DIR.$this->getObject()->getCheminLogo())
                ->setValue($this->getObject()->getLogoContent())
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
            'sourceCode' => [
                'name' => 'sourceCode',
                'required' => true,
            ],
        ]));
    }
}