<?php

namespace Individu\Form;

use Application\Form\Validator\NewEmailValidator;
use Application\Utils\FormUtils;
use Individu\Entity\Db\Individu;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\EmailAddress;

class IndividuForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var \Application\Entity\Db\Pays[]
     */
    private array $pays = [];

    /**
     * @param array[] $pays
     */
    public function setPays(array $pays): void
    {
        $this->pays = $pays;
    }

    /**
     * @return string[]
     */
    public function getNationalitesOptions(): array
    {
        $options = ['' => "(Non renseignée)"];
        foreach ($this->pays as $p) {
            $options[$p['id']] = [
                'value' => $p['id'],
                'label' => $p['libelleNationalite'],
                'attributes' => [
                    'data-content' => $p['libelleNationalite'] . ' <span class="text-sm text-secondary">' . $p['libelle'] . '</span>'
                ],
            ];
        }

        return $options;
    }

    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );

        $this->add(
            (new Radio('civilite'))
                ->setValueOptions([
                    null => "(Aucune)",
                    Individu::CIVILITE_M => Individu::CIVILITE_M,
                    Individu::CIVILITE_MME => Individu::CIVILITE_MME,
                ])
                ->setLabel("Civilité :")
        );
        $this->add(
            (new Text('nomUsuel'))
                ->setLabel("Nom d'usage :")
        );
        $this->add(
            (new Text('nomPatronymique'))
                ->setLabel("Nom patronymique :")
        );
        $this->add(
            (new Text('prenom1'))
                ->setLabel("Prénom 1 :")
        );
        $this->add(
            (new Text('prenom2'))
                ->setLabel("Prénom 2 :")
        );
        $this->add(
            (new Text('prenom3'))
                ->setLabel("Prénom 3 :")
        );
        $this->add(
            (new Text('emailPro'))
                ->setLabel("Adresse électronique institutionnelle/pro:")
        );
        $this->add(
            (new Date('dateNaissance'))
                ->setLabel("Date de naissance :")
        );
        $this->add(
            (new Select('paysNationalite'))
                ->setLabel("Nationalité :")
                ->setAttributes([
                    'class' => 'selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => 'nationalite',
                ])
                ->setValueOptions($this->getNationalitesOptions())
        );
        $this->add(
            (new Checkbox('apatride'))
                ->setLabel("Apatride")
        );
        $this->add(
            (new Text('supannId'))
                ->setLabel("Supann Id :")
                //->setAttribute('disabled', true)
        );

        $this->add((new Text('idRef'))
            ->setLabel("IdRef :")
        );

        FormUtils::addSaveButton($this);
    }

    public function prepare(): self
    {
        /** @var \Individu\Entity\Db\Individu $individu */
        $individu = $this->getObject();

        $estModifiable = ! $individu->getSource()?->getImportable();

        $this->get('civilite')->setAttribute('disabled', !$estModifiable);
        $this->get('nomUsuel')->setAttribute('disabled', !$estModifiable);
        $this->get('nomPatronymique')->setAttribute('disabled', !$estModifiable);
        $this->get('prenom1')->setAttribute('disabled', !$estModifiable);
        $this->get('prenom2')->setAttribute('disabled', !$estModifiable);
        $this->get('prenom3')->setAttribute('disabled', !$estModifiable);
        $this->get('emailPro')->setAttribute('disabled', !$estModifiable);
        $this->get('dateNaissance')->setAttribute('disabled', !$estModifiable);
        $this->get('apatride')->setAttribute('disabled', !$estModifiable);
        $this->get('paysNationalite')->setAttribute('disabled', !$estModifiable);
        $this->get('supannId')->setAttribute('disabled', !$estModifiable);

        return parent::prepare();

    }

    public function getInputFilterSpecification(): array
    {
        /** @var \Individu\Entity\Db\Individu $individu */
        $individu = $this->getObject();

        $estModifiable = $individu->getSource() === null || ! $individu->getSource()->getImportable();

        $emailValidators = [];
        $emailValidators[] = [
            'name' => EmailAddress::class,
        ];
        if (!$individu instanceof Individu || !$individu->getId()) {
            $emailValidators[] = [
                'name' => NewEmailValidator::class,
                'options' => ['perimetre' => ['individu']],
            ];
        }

        return [
            'civilite' => [
                'name' => 'civilite',
                'required' => false,
            ],
            'nomUsuel' => [
                'name' => 'nomUsuel',
                'required' => $estModifiable,
            ],
            'nomPatronymique' => [
                'name' => 'nomPatronymique',
                'required' => $estModifiable,
            ],
            'prenom1' => [
                'name' => 'prenom1',
                'required' => $estModifiable,
            ],
            'prenom2' => [
                'name' => 'prenom2',
                'required' => false,
            ],
            'prenom3' => [
                'name' => 'prenom3',
                'required' => false,
            ],
            'emailPro' => [
                'name' => 'emailPro',
                'required' => false,
                'validators' => $emailValidators,
            ],
            'dateNaissance' => [
                'name' => 'dateNaissance',
                'required' => false,
            ],
            'paysNationalite' => [
                'name' => 'paysNationalite',
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class], /** nécessaire et suffisant pour mettre la relation à null */
                ],
            ],
            'apatride' => [
                'name' => 'apatride',
                'required' => false,
            ],
            'supannId' => [
                'name' => 'supannId',
                'required' => false,
            ],
            'idRef' => [
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class],
                ],
            ],
        ];
    }
}