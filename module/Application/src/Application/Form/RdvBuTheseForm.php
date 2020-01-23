<?php

namespace Application\Form;

use Application\Entity\Db\RdvBu;
use Application\Filter\MotsClesFilter;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class RdvBuTheseForm extends Form
{
    const SEPARATEUR_MOTS_CLES_RAMEAU = RdvBu::SEPARATEUR_MOTS_CLES_RAMEAU;
    const SEPARATEUR_MOTS_CLES_RAMEAU_LIB = RdvBu::SEPARATEUR_MOTS_CLES_RAMEAU_LIB;

    private $disableExemplPapierFourni = false;

    /**
     * @param bool $disable
     */
    public function disableExemplPapierFourni(bool $disable = true)
    {
        $this->disableExemplPapierFourni = $disable;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new RdvBu());

        $this->add((new Textarea('coordDoctorant'))
            ->setLabel("Téléphone :")
        );

        $this->add((new Textarea('dispoDoctorant'))
            ->setLabel("Disponibilités :")
        );

        $this->add((new Checkbox('pageTitreConforme'))
            ->setLabel("Page de couverture validée")
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
            ->setAttribute('disabled', 'disabled')
        );

        $this->add((new Checkbox('versionArchivableFournie'))
            ->setLabel("Version archivable fournie")
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
            ->setAttribute('disabled', 'disabled')
        );

        $this->add((new Checkbox('exemplPapierFourni'))
            ->setLabel("Exemplaire papier remis")
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
        );

        $this->add((new Checkbox('conventionMelSignee'))
            ->setLabel("Convention de mise en ligne signée en 2 exemplaires")
            ->setLabelOptions([
                'disable_html_escape' => true,
            ])
        );

        $this->add([
            'type'       => 'Text',
            'name'       => 'motsClesRameau',
            'options'    => [
                'label' => 'Mots-clés RAMEAU',
            ],
            'attributes' => [
                'title' => sprintf("Mots-clés séparés par le caractère %s (%s)",
                    self::SEPARATEUR_MOTS_CLES_RAMEAU,
                    self::SEPARATEUR_MOTS_CLES_RAMEAU_LIB),
            ],
        ]);

        $this->add([
            'type'       => 'Textarea',
            'name'       => 'divers',
            'options'    => [
                'label' => 'Points de vigilance',
            ],
            'attributes' => [
                'rows' => 5,
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'idOrcid',
            'options'    => [
                'label' => 'Identifiant ORCID (facultatif)',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'halId',
            'options'    => [
                'label' => 'IdHAL (facultatif)',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'coordDoctorant' => [
                'name' => 'coordDoctorant',
                'required' => true,
            ],
            'dispoDoctorant' => [
                'name' => 'dispoDoctorant',
                'required' => true,
            ],
            'pageTitreConforme' => [
                'name' => 'pageTitreConforme',
                'required' => false,
            ],
            'exemplPapierFourni' => [
                'name' => 'exemplPapierFourni',
                'required' => false,
            ],
            'versionArchivableFournie' => [
                'name' => 'versionArchivableFournie',
                'required' => false,
            ],
            'conventionMelSignee' => [
                'name' => 'conventionMelSignee',
                'required' => false,
            ],
            'motsClesRameau' => [
                'required' => false,
                'filters' => [
                    new MotsClesFilter(['separator' => self::SEPARATEUR_MOTS_CLES_RAMEAU]),
                ],
            ],
            'idOrcid' => [
                'name' => 'idOrcid',
                'required' => false,
            ],
            'halId' => [
                'name' => 'halId',
                'required' => false,
            ],
            'divers' => [
                'name' => 'divers',
                'required' => false,
            ],
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        if ($this->disableExemplPapierFourni) {
            $this->remove('exemplPapierFourni');
        }

        return parent::prepare();
    }
}