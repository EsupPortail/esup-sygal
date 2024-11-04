<?php

namespace These\Fieldset\Direction;

use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\AbstractHydrator;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Hydrator\ActeurHydratorAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Form\Element\SearchAndSelect2 as SAS2;

class DirectionHydrator extends AbstractHydrator
{
    use IndividuServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    use ActeurHydratorAwareTrait;

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
        /** @var Acteur $directeur */
        if ($these->getId() !== null) {
            $directeur = current($this->acteurService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_DIRECTEUR_THESE)) ?: null;
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

        /** @var Acteur[] $codirecteurs */
        if ($these->getId() !== null) {
            $codirecteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);
            usort($codirecteurs, Acteur::getOrdreComparisonFunction());
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
            $individuId = SAS2::extractIdFromValue($data['directeur-individu']["id"]);
            $individu = $this->individuService->getRepository()->find($individuId);

            /** @var Acteur[] $directeursEnBdd */
            $directeursEnBdd = $these->getId() ?  $this->acteurService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_DIRECTEUR_THESE) : [];
            foreach ($directeursEnBdd as $directeur) {
                if ($directeur->getIndividu()?->getId() !== $individu->getId()) {
                    $directeur->historiser();
                }
            }

            $etablissement = isset($data['directeur-etablissement']) ? $this->etablissementService->getRepository()->find($data['directeur-etablissement']) : new Etablissement();

            $acteur = $this->addActeur($these, $individu, Role::CODE_DIRECTEUR_THESE, $etablissement);

            $prefixe = 'directeur-';
            $this->hydrateActeur($acteur, $data, $prefixe);
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
                $individuId = SAS2::extractIdFromValue($data[$prefixe . 'individu']["id"]);
                $individu = $this->individuService->getRepository()->find($individuId);

                $etablissement = isset($data[$prefixe.'etablissement']) ? $this->etablissementService->getRepository()->find($data[$prefixe.'etablissement']) : new Etablissement();
                $acteur = $this->addActeur($these, $individu, Role::CODE_CODIRECTEUR_THESE, $etablissement);
                $acteur->setOrdre($i);
                $this->hydrateActeur($acteur, $data, $prefixe);

                $temoins[] = $acteur->getId();
            }
        }

        $codirsEnBdd = $these->getId() ?  $this->acteurService->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE) : [];
        foreach ($codirsEnBdd as $codirecteur) {
            if (array_search($codirecteur->getId(), $temoins) === false) {
                $codirecteur->historiser();
            }
        }
    }

    private function extractActeur(Acteur $acteur, string $prefixe): array
    {
        $dataActeur = $this->acteurHydrator->extract($acteur);

        return [
            $prefixe . ($k = 'individu') => SAS2::createValueFromIdAndLabel($dataActeur[$k], (string)$acteur->getIndividu()),
            $prefixe . ($k = 'etablissement') => $dataActeur[$k],
            $prefixe . ($k = 'ecoleDoctorale') => $dataActeur[$k],
            $prefixe . ($k = 'uniteRecherche') => $dataActeur[$k],
            $prefixe . ($k = 'qualite') => $dataActeur[$k],
            $prefixe . ($k = 'principal') => $dataActeur[$k] ?? false,
            $prefixe . ($k = 'exterieur') => $dataActeur[$k] ?? false,
        ];
    }

    private function hydrateActeur(Acteur $acteur, array $data, string $prefixe)
    {
        $dataActeur = [
            ($k = 'individu') => SAS2::extractIdFromValue($data[$prefixe . $k]["id"]),
            ($k = 'etablissement') => $data[$prefixe . $k],
            ($k = 'ecoleDoctorale') => $data[$prefixe . $k],
            ($k = 'uniteRecherche') => $data[$prefixe . $k],
            ($k = 'qualite') => $data[$prefixe . $k],
            ($k = 'principal') => $data[$prefixe . $k] ?? false,
            ($k = 'exterieur') => $data[$prefixe . $k] ?? false,
        ];

        $this->acteurHydrator->hydrate($dataActeur, $acteur);
    }

    private function addActeur(These $these, Individu $individu, string $role, Etablissement $etablissement): Acteur
    {
        $role = $this->roleService->getRepository()->findOneByCodeAndStructureConcrete($role, $etablissement);
        $acteur = $these->getId() ? $this->acteurService->getRepository()->findActeurByIndividuAndThese($individu, $these) : null;
        if($role){
            if ($acteur === null) {
                $acteur = $this->acteurService->newActeur($these, $individu, $role);
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