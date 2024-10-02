<?php

namespace Structure\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\Form\FormInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Form\InputFilter\Etablissement\Ced\EtablissementCedInputFilter;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilter;
use Structure\Form\InputFilter\Etablissement\Inscription\EtablissementInscriptionInputFilter;
use Webmozart\Assert\Assert;

/**
 * @property \Structure\Form\InputFilter\Etablissement\EtablissementInputFilterInterface $filter
 * @method Etablissement getObject()
 */
class EtablissementForm extends StructureForm
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init(): void
    {
        parent::init();

        $this->setObject(new Etablissement());

        $this->add((new Text('sourceCode'))
            ->setLabel("Source Code")
        );

        $this->get('code')
            ->setLabel("Code (ex : UAI/RNE, etc.)")
            ->setAttribute('placeholder', "Ex : 0141408E");

        $this->get('sigle')->setAttribute('placeholder', "Ex : Unicaen");

        $this->add((new Text('domaine'))
            ->setLabel("Domaine")
            ->setAttribute('placeholder', 'Ex : domaine.fr')
        );

        $this->add((new Text('adresse'))
            ->setLabel("Adresse postale (sur une ligne)")
        );

        $this->add((new Text('telephone'))
            ->setLabel("Téléphone")
        );

        $this->add((new Text('fax'))
            ->setLabel("Fax")
        );

        $this->add((new Text('email'))
            ->setLabel("Adresse électronique")
        );

        $this->add((new Text('emailAssistance'))
            ->setLabel("Adresse électronique d'Assistance")
        );

        $this->add((new Text('emailBibliotheque'))
            ->setLabel("Adresse électronique pour les aspects Bibliothèque")
        );

        $this->add((new Text('emailDoctorat'))
            ->setLabel("Adresse électronique pour les aspects Doctorat")
        );

        $this->add((new Text('siteWeb'))
            ->setLabel("Site internet")
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

    public function bind(object $object, int $flags = FormInterface::VALUES_NORMALIZED): self
    {
        /** @var Etablissement $object */
        Assert::isInstanceOf($object, Etablissement::class);

        $this->setInputFilterForEtablissement($object);

        return parent::bind($object, $flags);
    }

    private function setInputFilterForEtablissement(Etablissement $etablissement): void
    {
        $inputFilterManager = $this->getFormFactory()->getInputFilterFactory()->getInputFilterManager();

        $type = $etablissement->getTypeFromEtiquettes();

        switch ($type) {
            case Etablissement::TYPE_INSCRIPTION:
                /** @var EtablissementInscriptionInputFilter $inputFilter */
                $inputFilter = $inputFilterManager->get(EtablissementInscriptionInputFilter::class);
                $inputFilter->get('sourceCode')->setRequired($etablissement->getId() === null);
                break;
            case Etablissement::TYPE_COLLEGE_ED:
                /** @var EtablissementCedInputFilter $inputFilter */
                $inputFilter = $inputFilterManager->get(EtablissementCedInputFilter::class);
                break;
            case Etablissement::TYPE_AUTRE:
            default:
                /** @var EtablissementInputFilter $inputFilter */
                $inputFilter = $inputFilterManager->get(EtablissementInputFilter::class);
                break;
        }

        $this->setInputFilter($inputFilter);
    }

    public function prepare(): self
    {
        parent::prepare();

        $this->filter->prepareForm($this);

        return $this;
    }
}