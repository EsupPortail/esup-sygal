<?php

namespace Acteur\Rule;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Laminas\Form\Fieldset;

abstract class AbstractActeurRule implements ActeurRuleInterface
{
    protected ActeurThese|ActeurHDR $acteur;
    protected bool $acteurExistant;
    protected bool $acteurImporte;

    public function setActeur(ActeurThese|ActeurHDR $acteur): void
    {
        $this->acteur = $acteur;
        $this->execute();
    }

    public function execute(): void
    {
        $this->acteurExistant = (bool) $this->acteur->getId();
        $this->acteurImporte = $this->acteur->getSource()->getImportable();
    }

    public function prepareActeurFieldset(Fieldset $fieldset): void
    {
        if ($this->acteurExistant) {
            $fieldset->get('individu')->setAttribute('readonly', true);
        }

        if ($this->acteur instanceof ActeurThese) {
            if ($this->acteurImporte) {
                $fieldset->get('individu')->setAttribute('readonly', true);
                $fieldset->get('etablissement')->setAttribute('disabled', true);
                $fieldset->remove('qualite');
            } else {
                $fieldset->remove('etablissementForce');
            }
        }

        $fieldset->get('role')->setAttribute('disabled', true);
    }

    public function prepareActeurHydratorData(array $data): array
    {
        if ($this->acteurExistant) {
            unset($data['role']);
            unset($data['individu']);
        }

        if ($this->acteur instanceof ActeurThese) {
            if ($this->acteurImporte) {
                unset($data['role']);
                unset($data['individu']);
                unset($data['etablissement']);
                unset($data['qualite']);
            } else {
                unset($data['etablissementForce']);
            }
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
                'required' => !$this->acteur instanceof ActeurThese || !$this->acteurImporte && !$this->acteurExistant,
            ],
            'etablissement' => [
                'required' => !$this->acteur instanceof ActeurThese || !$this->acteurImporte && !$this->acteurExistant,
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