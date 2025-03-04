<?php

namespace These\Fieldset\Direction;

use Acteur\Entity\Db\ActeurThese;
use Acteur\Hydrator\ActeurThese\ActeurTheseHydratorAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\AbstractHydrator;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Form\Element\SearchAndSelect as SAS;

class DirectionHydrator extends AbstractHydrator
{
    use IndividuServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    use ActeurTheseHydratorAwareTrait;

    /**
     * @param \These\Entity\Db\These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        return array_merge(
            $this->extractDirecteur($object),
            $this->extractCodirecteurs($object)
        );
    }

    /**
     * @param array $data
     * @param \These\Entity\Db\These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        $this->hydrateDirecteur($data, $object);
        $this->hydrateCodirecteurs($data, $object);

        return $object;
    }

    /**
     * @param \These\Entity\Db\These $these
     * @return array
     */
    private function extractDirecteur(These $these): array
    {
        /** @var ActeurThese $directeur */
        if ($these->getId() !== null) {
            $directeur = current($this->acteurTheseService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_DIRECTEUR_THESE)) ?: null;
        } else {
            $directeur = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE)->first() ?: null;
        }
        if ($directeur === null) {
            return [];
        }

        $prefixe = 'directeur-';
        $dataActeur = $this->extractActeur($directeur, $prefixe);

        return $dataActeur;
    }

    /**
     * @param \These\Entity\Db\These $these
     * @return array
     */
    private function extractCodirecteurs(These $these): array
    {
        $data = [];

        /** @var ActeurThese[] $codirecteurs */
        if ($these->getId() !== null) {
            $codirecteurs = $this->acteurTheseService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);
            usort($codirecteurs, ActeurThese::getOrdreComparisonFunction());
        } else {
            $codirecteurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        }

        $i = 1;
        foreach ($codirecteurs as $codirecteur) {
            $prefixe = 'codirecteur' . $i . '-';
            $data[$prefixe . 'enabled'] = true;
            $dataActeur = $this->extractActeur($codirecteur, $prefixe);
            $data = array_merge($data, $dataActeur);
            $i++;
        }

        return $data;
    }

    private function hydrateDirecteur(array $data, These $these)
    {
        if(isset($data['directeur-individu'])){
            /** @var Individu $individu */
            $individuId = SAS::extractIdFromValue($data['directeur-individu']["id"]);
            $individu = $this->individuService->getRepository()->find($individuId);

            /** @var ActeurThese[] $directeursEnBdd */
            $directeursEnBdd = $these->getId() ?  $this->acteurTheseService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_DIRECTEUR_THESE) : [];
            foreach ($directeursEnBdd as $acteur) {
                if ($acteur->getIndividu()?->getId() !== $individu->getId()) {
                    $acteur->historiser();

                    //on historise également l'acteur ayant le rôle Membre associé à l'individu
                    $membreEnBdd = $this->acteurTheseService->getRepository()->findActeurByIndividuAndTheseAndRole($acteur->getIndividu(),$these, Role::CODE_MEMBRE_JURY);
                    if($membreEnBdd) $membreEnBdd->historiser();
                }
            }

            $etablissement = isset($data['directeur-etablissement']) ? $this->etablissementService->getRepository()->find($data['directeur-etablissement']) : new Etablissement();
            $prefixe = 'directeur-';
            $directeurActeur = $this->addActeur($these, $individu, Role::CODE_DIRECTEUR_THESE, $etablissement);
            $this->hydrateActeur($directeurActeur, $data, $prefixe);

            //ajout d'un deuxième acteur avec le rôle Membre pour le même individu
            $membreActeur = $this->addActeur($these, $individu, Role::CODE_MEMBRE_JURY, $etablissement);
            $this->hydrateActeur($membreActeur, $data, $prefixe);
        }
    }

    private function hydrateCodirecteurs(array $data, These $these)
    {
        $temoins = [];

        for ($i = 1; $i <= DirectionFieldset::NBCODIR; $i++) {
            $prefixe = "codirecteur$i-";
            $isEnabled = $data[$prefixe . 'enabled'] ?? false;
            if ($isEnabled) {
                /** @var Individu $individu */
                $individuId = SAS::extractIdFromValue($data[$prefixe . 'individu']["id"]);
                $individu = $this->individuService->getRepository()->find($individuId);

                $etablissement = isset($data[$prefixe.'etablissement']) ? $this->etablissementService->getRepository()->find($data[$prefixe.'etablissement']) : new Etablissement();
                $acteur = $this->addActeur($these, $individu, Role::CODE_CODIRECTEUR_THESE, $etablissement);
                $acteur->setOrdre($i);
                $this->hydrateActeur($acteur, $data, $prefixe);

                $temoins[] = $acteur->getId();

                //ajout d'un deuxième acteur avec le rôle Membre pour le même individu
                $membreActeur = $this->addActeur($these, $individu, Role::CODE_MEMBRE_JURY, $etablissement);
                $this->hydrateActeur($membreActeur, $data, $prefixe);
            }
        }

        $codirsEnBdd = $these->getId() ?  $this->acteurTheseService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE) : [];
        foreach ($codirsEnBdd as $acteur) {
            if (array_search($acteur->getId(), $temoins) === false) {
                $acteur->historiser();

                //on historise également l'acteur ayant le rôle Membre associé à l'individu
                $membreEnBdd = $this->acteurTheseService->getRepository()->findActeurByIndividuAndTheseAndRole($acteur->getIndividu(),$these, Role::CODE_MEMBRE_JURY);
                if($membreEnBdd) $membreEnBdd->historiser();
            }
        }
    }

    private function extractActeur(ActeurThese $acteur, string $prefixe): array
    {
        $dataActeur = $this->acteurTheseHydrator->extract($acteur);

        return [
            $prefixe . ($k = 'individu') => SAS::createValueFromIdAndLabel($dataActeur[$k], (string)$acteur->getIndividu()),
            $prefixe . ($k = 'etablissement') => $dataActeur[$k],
            $prefixe . ($k = 'ecoleDoctorale') => $dataActeur[$k],
            $prefixe . ($k = 'uniteRecherche') => $dataActeur[$k],
            $prefixe . ($k = 'qualite') => $dataActeur[$k],
            $prefixe . ($k = 'principal') => $dataActeur[$k] ?? false,
            $prefixe . ($k = 'exterieur') => $dataActeur[$k] ?? false,
        ];
    }

    private function hydrateActeur(ActeurThese $acteur, array $data, string $prefixe)
    {
        $dataActeur = [
            ($k = 'individu') => SAS::extractIdFromValue($data[$prefixe . $k]["id"]),
            ($k = 'etablissement') => $data[$prefixe . $k],
            ($k = 'ecoleDoctorale') => $data[$prefixe . $k],
            ($k = 'uniteRecherche') => $data[$prefixe . $k],
            ($k = 'qualite') => $data[$prefixe . $k],
            ($k = 'principal') => $data[$prefixe . $k] ?? false,
            ($k = 'exterieur') => $data[$prefixe . $k] ?? false,
        ];

        $this->acteurTheseHydrator->hydrate($dataActeur, $acteur);
    }

    private function addActeur(These $these, Individu $individu, string $role, Etablissement $etablissement): ActeurThese
    {
        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete($role, $etablissement);
        $acteur = $these->getId() ? $this->acteurTheseService->getRepository()->findActeurByIndividuAndTheseAndRole($individu, $these, $role) : null;
        if($role){
            if ($acteur === null) {
                $acteur = $this->acteurTheseService->newActeurThese($these, $individu, $role);
                $these->addActeur($acteur);
            } else {
                $acteur->setRole($role);
            }
        }else{
            throw new RuntimeException("Aucun rôle n'a été trouvé pour le code [".$role."] et l'établissement ['.$etablissement.']");
        }
        return $acteur;
    }
}