<?php

namespace Application\Form;

use Application\Entity\Db\Etablissement;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class EtablissementForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new Etablissement());

        $this
            ->add(new Hidden('id'));

        $this->add((
            new Text('sigle'))
                ->setLabel("Sigle :")
        );

        $this->add(
            (new Text('domaine'))
                ->setLabel("Domaine :")
                ->setAttribute('placeholder', 'domaine.fr')
        );

        $this
            ->add((
                new Text('libelle'))
                    ->setLabel("Libellé :")
        );

        $this->add((
        new Text('code'))
            ->setLabel("Code :")
        );

        $this->add((
        new Text('id_ref'))
            ->setLabel("IdREF :")
        );

        $this->add((
        new Text('adresse'))
            ->setLabel("Adresse (sur une ligne) :")
        );

        $this->add((
        new Text('telephone'))
            ->setLabel("Téléphone :")
        );

        $this->add((
        new Text('fax'))
            ->setLabel("Fax :")
        );

        $this->add((
        new Text('email'))
            ->setLabel("Adresse électronique :")
        );

        $this->add((
        new Text('siteWeb'))
            ->setLabel("Site internet :")
        );

        $this->add(
            (new Checkbox('estMembre'))
                ->setLabel("Établissement membre")
        );

        $this->add(
            (new Checkbox('estAssocie'))
                ->setLabel("Établissement associé")
        );
        $this->add(
            (new Checkbox('estInscription'))
                ->setLabel("Établissement d'inscription")
        );
        $this->add(
            (new Checkbox('estFerme'))
                ->setLabel("Établissement fermé")
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
                'name' => 'Sigle',
                'required' => false,
            ],
            'id_ref' => [
                'name' => 'id_ref',
                'required' => false,
            ],
            'code' => [
                'name' => 'Code',
                'required' => false,
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
            ],
        ]));
    }
}