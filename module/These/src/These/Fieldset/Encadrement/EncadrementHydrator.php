<?php

namespace These\Fieldset\Encadrement;

use Application\Entity\Db\Role;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;

class EncadrementHydrator implements HydratorInterface
{
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use QualiteServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];

        /** @var Acteur $directeur */
        $directeur = ($object->getId() !== null) ? current($this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_DIRECTEUR_THESE)) : null;
        if ($directeur) {
            $data['directeur-individu'] = ['id' => $directeur->getIndividu()->getId(), 'label' => $directeur->getIndividu()->getNomComplet()];
            $data['directeur-qualite'] = ($qualite = $this->qualiteService->findQualiteByLibelle($directeur->getQualite())) ? $qualite->getId() : null;
            $data['directeur-etablissement'] = $directeur->getEtablissement()->getId();
        }

        $codirecteurs = ($object->getId() !== null) ? $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CODIRECTEUR_THESE) : [];
        usort($codirecteurs, function (Acteur $a, Acteur $b) {
            return $a->getIndividu()->getNomComplet() > $b->getIndividu()->getNomComplet();
        });
        $i = 1;
        foreach ($codirecteurs as $codirecteur) {
            $data['codirecteur' . $i . '-individu'] = ["id" => $codirecteur->getIndividu()->getId(), "label" => $codirecteur->getIndividu()->getNomComplet()];
            $data['codirecteur' . $i . '-qualite'] = ($qualite = $this->qualiteService->findQualiteByLibelle($codirecteur->getQualite())) ? $qualite->getId() : null;;
            $data['codirecteur' . $i . '-etablissement'] = $codirecteur->getEtablissement()->getId();
            $i++;
        }

        $coencadrants = ($object->getId() !== null) ? $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CO_ENCADRANT) : [];
        usort($coencadrants, fn(Acteur $a, Acteur $b) => $a->getIndividu()->getNomComplet() <=> $b->getIndividu()->getNomComplet());
        $i = 1;
        foreach ($coencadrants as $coencadrant) {
            $data['coencadrant' . $i . '-individu'] = ["id" => $coencadrant->getIndividu()->getId(), "label" => $coencadrant->getIndividu()->getNomComplet()];
            $i++;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        if ($data['directeur-individu']['id'] and $data['directeur-qualite'] and $data['directeur-etablissement']) {
            /** @var Individu $individu */
            $individu = $this->individuService->getRepository()->find($data['directeur-individu']['id']);
            $qualite = $this->qualiteService->getQualite($data['directeur-qualite']);
            /** @var Etablissement $etablissement */
            $etablissement = $this->etablissementService->getRepository()->find($data['directeur-etablissement']);
            if ($individu and $qualite and $etablissement) {
                $this->acteurService->creerOrModifierActeur($object, $individu, Role::CODE_DIRECTEUR_THESE, $qualite, $etablissement);
            }
        }

        $temoins = [];
        for ($i = 1; $i <= EncadrementFieldset::NBCODIR; $i++) {
            if ($data['codirecteur' . $i . '-individu']['id'] and $data['codirecteur' . $i . '-qualite'] and $data['codirecteur' . $i . '-etablissement']) {
                /** @var Individu $individu */
                $individu = $this->individuService->getRepository()->find($data['codirecteur' . $i . '-individu']['id']);
                $qualite = $this->qualiteService->getQualite($data['codirecteur' . $i . '-qualite']);
                /** @var Etablissement $etablissement */
                $etablissement = $this->etablissementService->getRepository()->find($data['codirecteur' . $i . '-etablissement']);
                if ($individu and $qualite and $etablissement) {
                    $acteur = $this->acteurService->creerOrModifierActeur($object, $individu, Role::CODE_CODIRECTEUR_THESE, $qualite, $etablissement);
                    $temoins[] = $acteur->getId();
                }
            }
        }
        $codirecteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CODIRECTEUR_THESE);
        foreach ($codirecteurs as $codirecteur) {
            if (array_search($codirecteur->getId(), $temoins) === false) $this->acteurService->historise($codirecteur);
        }

        $temoins = [];
        for ($i = 1; $i <= EncadrementFieldset::NB_COENCADRANTS_MAXI; $i++) {
            if ($individuId = $data['coencadrant' . $i . '-individu']['id'] ?? null) {
                /** @var Individu $individu */
                $individu = $this->individuService->getRepository()->find($individuId);
                $acteur = $this->acteurService->creerOrModifierActeur($object, $individu, Role::CODE_CO_ENCADRANT, null, $object->getEtablissement());
                $temoins[] = $acteur->getId();
            }
        }
        $coencadrants = $this->acteurService->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CO_ENCADRANT);
        foreach ($coencadrants as $acteur) {
            if (array_search($acteur->getId(), $temoins) === false) {
                $this->acteurService->historise($acteur);
            }
        }

        return $object;
    }
}