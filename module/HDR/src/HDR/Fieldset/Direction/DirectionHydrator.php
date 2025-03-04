<?php

namespace HDR\Fieldset\Direction;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Hydrator\ActeurHDR\ActeurHDRHydratorAwareTrait;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\AbstractHydrator;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use HDR\Entity\Db\HDR;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Form\Element\SearchAndSelect2 as SAS2;

class DirectionHydrator extends AbstractHydrator
{
    use IndividuServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    use ActeurHDRHydratorAwareTrait;

    /**
     * @param HDR|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        return $this->extractGarant($object);
    }

    /**
     * @param array $data
     * @param HDR|object $object
     * @return HDR
     */
    public function hydrate(array $data, object $object): HDR
    {
        $this->hydrateGarant($data, $object);

        return $object;
    }

    /**
     * @param HDR $hdr
     * @return array
     */
    private function extractGarant(HDR $hdr): array
    {
        /** @var ActeurHDR $garant */
        if ($hdr->getId() !== null) {
            $garant = current($this->acteurHDRService->getRepository()->findActeursByHDRAndRole($hdr, Role::CODE_HDR_GARANT)) ?: null;
        } else {
            $garant = $hdr->getActeursByRoleCode(Role::CODE_HDR_GARANT)->first() ?: null;
        }
        if ($garant === null) {
            return [];
        }

        $prefixe = 'garant-';
        $dataActeur = $this->extractActeur($garant, $prefixe);

        return $dataActeur;
    }

    private function hydrateGarant(array $data, HDR $hdr)
    {
        if(isset($data['garant-individu'])){
            /** @var Individu $individu */
            $individuId = SAS2::extractIdFromValue($data['garant-individu']["id"]);
            $individu = $this->individuService->getRepository()->find($individuId);

            /** @var ActeurHDR[] $garantsEnBdd */
            $garantsEnBdd = $hdr->getId() ?  $this->acteurHDRService->getRepository()->findActeursByHDRAndRole($hdr, Role::CODE_HDR_GARANT) : [];
            foreach ($garantsEnBdd as $garant) {
                if ($garant->getIndividu()?->getId() !== $individu->getId()) {
                    $garant->historiser();
                    //on historise également l'acteur ayant le rôle Membre associé à l'individu
                    $membreEnBdd = $this->acteurHDRService->getRepository()->findActeurByIndividuAndHDRAndRole($garant->getIndividu(), $hdr, Role::CODE_MEMBRE_JURY);
                    if($membreEnBdd) $membreEnBdd->historiser();
                }
            }

            $etablissement = isset($data['garant-etablissement']) ? $this->etablissementService->getRepository()->find($data['garant-etablissement']) : new Etablissement();

            $acteur = $this->addActeur($hdr, $individu, Role::CODE_HDR_GARANT, $etablissement);
            $prefixe = 'garant-';
            $this->hydrateActeur($acteur, $data, $prefixe);

            //ajout d'un deuxième acteur avec le rôle Membre pour le même individu
            $membreActeur = $this->addActeur($hdr, $individu, Role::CODE_MEMBRE_JURY, $etablissement);
            $this->hydrateActeur($membreActeur, $data, $prefixe);
        }
    }

    private function extractActeur(ActeurHDR $acteur, string $prefixe): array
    {
        $dataActeur = $this->acteurHDRHydrator->extract($acteur);

        return [
            $prefixe . ($k = 'individu') => SAS2::createValueFromIdAndLabel($dataActeur[$k], (string)$acteur->getIndividu()),
            $prefixe . ($k = 'etablissement') => $dataActeur[$k],
//            $prefixe . ($k = 'ecoleDoctorale') => $dataActeur[$k],
            $prefixe . ($k = 'uniteRecherche') => $dataActeur[$k],
            $prefixe . ($k = 'qualite') => $dataActeur[$k],
            $prefixe . ($k = 'exterieur') => $dataActeur[$k] ?? false,
        ];
    }

    private function hydrateActeur(ActeurHDR $acteur, array $data, string $prefixe)
    {
        $dataActeur = [
            ($k = 'individu') => SAS2::extractIdFromValue($data[$prefixe . $k]["id"]),
            ($k = 'etablissement') => $data[$prefixe . $k],
//            ($k = 'ecoleDoctorale') => $data[$prefixe . $k],
            ($k = 'uniteRecherche') => $data[$prefixe . $k],
            ($k = 'qualite') => $data[$prefixe . $k],
            ($k = 'exterieur') => $data[$prefixe . $k] ?? false,
        ];

        $this->acteurHDRHydrator->hydrate($dataActeur, $acteur);
    }

    private function addActeur(HDR $hdr, Individu $individu, string $role, Etablissement $etablissement): ActeurHDR
    {
        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete($role, $etablissement);
        $acteur = $hdr->getId() ? $this->acteurHDRService->getRepository()->findActeurByIndividuAndHDRAndRole($individu, $hdr, $role) : null;
        if($role){
            if ($acteur === null) {
                $acteur = $this->acteurHDRService->newActeurHDR($hdr, $individu, $role);
                $hdr->addActeur($acteur);
            } else {
                $acteur->setRole($role);
            }
        }else{
            throw new RuntimeException("Aucun rôle n'a été trouvé pour le code [".$role."] et l'établissement ['.$etablissement.']");
        }
        return $acteur;
    }
}