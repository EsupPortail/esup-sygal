<?php

namespace Acteur\Hydrator;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Individu\Entity\Db\Individu;
use Laminas\Hydrator\AbstractHydrator;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;

abstract class AbstractActeurHydrator extends AbstractHydrator
{
    use QualiteServiceAwareTrait;

    public function extract(object $object): array
    {
        /** @var ActeurThese|ActeurHDR $object */

        $qualite = $object->getLibelleQualite() ? $this->qualiteService->findQualiteByLibelle($object->getLibelleQualite()) : null;

        $data = [];
        $data['individu'] = $object->getIndividu() ? $this->extractValue('individu', $object->getIndividu()) : null;
        $data['etablissement'] = $object->getEtablissement() ? $this->extractValue('etablissement', $object->getEtablissement()) : null;
        if($object instanceof ActeurThese) $data['ecoleDoctorale'] = $object->getEcoleDoctorale() ? $this->extractValue('ecoleDoctorale', $object->getEcoleDoctorale()) : null;
        $data['uniteRecherche'] = $object->getUniteRecherche() ? $this->extractValue('uniteRecherche', $object->getUniteRecherche()) : null;
        $data['qualite'] = $qualite?->getId();
        if($object instanceof ActeurThese) $data['principal'] = $object->isPrincipal();
        $data['exterieur'] = $object->isExterieur();

        return $data;
    }

    public function hydrate(array $data, object $object): ActeurThese|ActeurHDR
    {
        /** @var ActeurThese|ActeurHDR $object */

        $qualiteLib = $data['qualite'];
        $principal = $data['principal'] ?? false;
        $exterieur = $data['exterieur'] ?? false;

        /** @var Individu $individu */
        $individu = $this->hydrateValue('individu', $data['individu']);
        /** @var Etablissement $etablissement */
        $etablissement = $this->hydrateValue('etablissement', $data['etablissement']);

        /** @var UniteRecherche $uniteRecherche */
        $uniteRecherche = $this->hydrateValue('uniteRecherche', $data['uniteRecherche']);

        $qualite = $this->getQualiteService()->getQualite($qualiteLib);

        if($object instanceof ActeurHDR){
            /** @var EcoleDoctorale $ecoleDoctorale */
            $ecoleDoctorale = $this->hydrateValue('ecoleDoctorale', $data['ecoleDoctorale']);
            $object->setEcoleDoctorale($ecoleDoctorale);
        }

        $object->setIndividu($individu);
        $object->setEtablissement($etablissement);
        $object->setUniteRecherche($uniteRecherche);
        $object->setQualite($qualite);
        $object->setPrincipal($principal);
        $object->setExterieur($exterieur);

        return $object;
    }
}