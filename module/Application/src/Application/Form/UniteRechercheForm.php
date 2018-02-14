<?php

namespace Application\Form;

use Application\Entity\Db\UniteRecherche;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\File;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class UniteRechercheForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new UniteRecherche());

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
        new File('cheminLogo'))
            ->setLabel('Logo de l\'unité de recherche :')
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
            'sourceCode' => [
                'name' => 'sourceCode',
                'required' => true,
            ],
        ]));
    }
}