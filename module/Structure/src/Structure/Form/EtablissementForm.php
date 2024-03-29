<?php

namespace Structure\Form;

use Laminas\Filter\ToNull;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Uri;
use Structure\Entity\Db\Etablissement;

class EtablissementForm extends StructureForm implements InputFilterProviderInterface
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        parent::init();

        $this->setObject(new Etablissement());

        $this->add((new Text('domaine'))
            ->setLabel("Domaine :")
            ->setAttribute('placeholder', 'domaine.fr')
        );

        $this->add((new Text('adresse'))
            ->setLabel("Adresse postale (sur une ligne) :")
        );

        $this->add((new Text('telephone'))
            ->setLabel("Téléphone :")
        );

        $this->add((new Text('fax'))
            ->setLabel("Fax :")
        );

        $this->add((new Text('email'))
            ->setLabel("Adresse électronique :")
        );

        $this->add((new Text('emailAssistance'))
            ->setLabel("Adresse électronique d'Assistance :")
        );

        $this->add((new Text('emailBibliotheque'))
            ->setLabel("Adresse électronique pour les aspects Bibliothèque :")
        );

        $this->add((new Text('emailDoctorat'))
            ->setLabel("Adresse électronique pour les aspects Doctorat :")
        );

        $this->add((new Text('siteWeb'))
            ->setLabel("Site internet :")
        );

        $this->add((new Checkbox('estMembre'))
            ->setLabel("Établissement membre")
        );
        $this->add((new Checkbox('estAssocie'))
            ->setLabel("Établissement associé")
        );
        $this->add((new Checkbox('estInscription'))
            ->setLabel("Établissement d'inscription")
        );
        $this->add((new Checkbox('estCed'))
            ->setLabel("Collège des écoles doctorales")
        );

        $this->add((new Checkbox('estFerme'))
            ->setLabel("Établissement fermé")
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return array_merge(parent::getInputFilterSpecification(), [
            'code' => [
                'required' => true, // requis pour le calcul du nom de fichier logo
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'domaine' => [
                'name' => 'domaine',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'adresse' => [
                'name' => 'adresse',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'telephone' => [
                'name' => 'telephone',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'fax' => [
                'name' => 'fax',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
            ],
            'email' => [
                'name' => 'email',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => EmailAddress::class],
                ],
            ],
            'emailAssistance' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => EmailAddress::class],
                ],
            ],
            'emailBibliotheque' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => EmailAddress::class],
                ],
            ],
            'emailDoctorat' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => EmailAddress::class],
                ],
            ],
            'siteWeb' => [
                'name' => 'siteWeb',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => ToNull::class],
                ],
                'validators' => [
                    ['name' => Uri::class, 'options' => ['allowRelative' => false]],
                ],
            ],
        ]);
    }
}