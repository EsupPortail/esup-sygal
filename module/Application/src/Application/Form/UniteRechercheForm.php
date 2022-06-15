<?php

namespace Application\Form;

use Application\Entity\Db\UniteRecherche;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class UniteRechercheForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new UniteRecherche());

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

        $this
            ->add((
            new Text('RNSR'))
                ->setLabel("Identifiant RNSR :")
            );
        $this->add(
            (new Checkbox('estFerme'))
                ->setLabel("Unité de recherche fermée")
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
            'id_ref' => [
                'name' => 'id_ref',
                'required' => false,
            ],
            'code' => [
                'name' => 'code',
                'required' => true,
            ],
        ]));
    }
}