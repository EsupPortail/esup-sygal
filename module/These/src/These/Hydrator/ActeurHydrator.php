<?php

namespace These\Hydrator;

use Individu\Entity\Db\Individu;
use Laminas\Hydrator\AbstractHydrator;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\Acteur;

class ActeurHydrator extends AbstractHydrator
{
    use QualiteServiceAwareTrait;

    /**
     * @param \These\Entity\Db\Acteur|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $qualite = $object->getQualite() ? $this->qualiteService->findQualiteByLibelle($object->getQualite()) : null;

        $data = [];
        $data['individu'] = $object->getIndividu() ? $this->extractValue('individu', $object->getIndividu()) : null;
        $data['etablissement'] = $object->getEtablissement() ? $this->extractValue('etablissement', $object->getEtablissement()) : null;
        $data['ecoleDoctorale'] = $object->getEcoleDoctorale() ? $this->extractValue('ecoleDoctorale', $object->getEcoleDoctorale()) : null;
        $data['uniteRecherche'] = $object->getUniteRecherche() ? $this->extractValue('uniteRecherche', $object->getUniteRecherche()) : null;
        $data['qualite'] = $qualite?->getId();
        $data['principal'] = $object->isPrincipal();
        $data['exterieur'] = $object->isExterieur();

        return $data;
    }

    /**
     * @param array $data
     * @param \These\Entity\Db\Acteur|object $object
     * @return \These\Entity\Db\Acteur
     */
    public function hydrate(array $data, object $object): Acteur
    {
        $qualiteLib = $data['qualite'];
        $principal = $data['principal'] ?? false;
        $exterieur = $data['exterieur'] ?? false;

        /** @var Individu $individu */
        $individu = $this->hydrateValue('individu', $data['individu']);
        /** @var Etablissement $etablissement */
        $etablissement = $this->hydrateValue('etablissement', $data['etablissement']);
        /** @var \Structure\Entity\Db\EcoleDoctorale $ecoleDoctorale */
        $ecoleDoctorale = $this->hydrateValue('ecoleDoctorale', $data['ecoleDoctorale']);
        /** @var \Structure\Entity\Db\UniteRecherche $uniteRecherche */
        $uniteRecherche = $this->hydrateValue('uniteRecherche', $data['uniteRecherche']);

        $qualite = $this->qualiteService->getQualite($qualiteLib);

        $object->setIndividu($individu);
        $object->setEtablissement($etablissement);
        $object->setEcoleDoctorale($ecoleDoctorale);
        $object->setUniteRecherche($uniteRecherche);
        $object->setQualite($qualite);
        $object->setPrincipal($principal);
        $object->setExterieur($exterieur);

        return $object;
    }
}