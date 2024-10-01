<?php

namespace These\Rule;

use Application\Rule\RuleInterface;
use These\Entity\Db\Acteur;
use These\Fieldset\Acteur\ActeurFieldset;

class ActeurRule implements RuleInterface
{
    private Acteur $acteur;
    private bool $acteurExistant;
    private bool $acteurImporte;

    public function setActeur(Acteur $acteur): void
    {
        $this->acteur = $acteur;
        $this->execute();
    }

    public function execute(): void
    {
        $this->acteurExistant = (bool) $this->acteur->getId();
        $this->acteurImporte = $this->acteur->getSource()->getImportable();
    }

    public function prepareActeurFieldset(ActeurFieldset $fieldset): void
    {
        if ($this->acteurExistant) {
            $fieldset->get('individu')->setAttribute('disabled', true);
        }

        if ($this->acteurImporte) {
            $fieldset->get('individu')->setAttribute('disabled', true);
            $fieldset->get('etablissement')->setAttribute('disabled', true);
            $fieldset->get('qualite')->setAttribute('disabled', true);
        } else {
            $fieldset->remove('etablissementForce');
        }

        $fieldset->get('role')->setAttribute('disabled', true);
    }

    public function prepareActeurHydratorData(array $data): array
    {
        if ($this->acteurExistant) {
            unset($data['role']);
            unset($data['individu']);
        }

        if ($this->acteurImporte) {
            unset($data['role']);
            unset($data['individu']);
            unset($data['etablissement']);
            unset($data['qualite']);
        } else {
            unset($data['etablissementForce']);
        }

        return $data;
    }

    public function prepareActeurInputFilterSpecification(array $spec): array
    {
        return array_merge_recursive($spec, [
            'role' => [
                'required' => false,
            ],
            'individu' => [
                'required' => !$this->acteurImporte && !$this->acteurExistant,
            ],
            'etablissement' => [
                'required' => !$this->acteurImporte && !$this->acteurExistant,
            ],
            'etablissementForce' => [
                'required' => false,
            ],
            'uniteRecherche' => [
                'required' => false,
            ],
            'qualite' => [
                'required' => false,
            ],
        ]);
    }
}