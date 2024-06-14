<?php

namespace These\Hydrator;

use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\Acteur;

class ActeurHydrator implements HydratorInterface
{
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    private string $keyPrefix = '';

    public function setKeyPrefix(string $keyPrefix): self
    {
        $this->keyPrefix = $keyPrefix;

        return $this;
    }

    /**
     * @param \These\Entity\Db\Acteur|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $these = $object->getThese();
        $individu = $object->getIndividu();
        $etab = $object->getEtablissement() ? $object->getEtablissement() : $these->getEtablissement();
        $ed = $object->getEcoleDoctorale() ? $object->getEcoleDoctorale() : $these->getEcoleDoctorale();
        $ur = $object->getUniteRecherche() ? $object->getUniteRecherche() : $these->getUniteRecherche();
        $qualite = $this->qualiteService->findQualiteByLibelle($object->getQualite());

        $data = [];

        $data[$this->keyPrefix . 'individu'] = [
            'id' => $individu->getId(),
            'label' => $individu->getNomComplet()
        ];
        $data[$this->keyPrefix . 'etablissement'] = [
            'id' => $etab->getId(),
            'label' => (string) $etab->getStructure()
        ];
        $data[$this->keyPrefix . 'ecoleDoctorale'] = [
            'id' => $ed->getId(),
            'label' => (string) $ed->getStructure()
        ];
        $data[$this->keyPrefix . 'uniteRecherche'] = [
            'id' => $ur->getId(),
            'label' => (string) $ur->getStructure()
        ];
        $data[$this->keyPrefix . 'qualite'] = $qualite ? $qualite->getId() : null;

        return $data;
    }

    /**
     * @param array $data
     * @param \These\Entity\Db\Acteur|object $object
     * @return \These\Entity\Db\Acteur
     */
    public function hydrate(array $data, object $object): Acteur
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->find($data[$this->keyPrefix . 'individu']['id']);
        /** @var Etablissement $etablissement */
        $etablissement = $this->etablissementService->getRepository()->find($data[$this->keyPrefix . 'etablissement']);
        /** @var \Structure\Entity\Db\EcoleDoctorale $ed */
        $ed = $this->ecoleDoctoraleService->getRepository()->find($data[$this->keyPrefix . 'ecoleDoctorale']);
        /** @var \Structure\Entity\Db\UniteRecherche $ur */
        $ur = $this->uniteRechercheService->getRepository()->find($data[$this->keyPrefix . 'uniteRecherche']);

        $qualite = $this->qualiteService->getQualite($data[$this->keyPrefix . 'qualite']);

        $object->setIndividu($individu);
        $object->setQualite($qualite);
        $object->setEtablissement($etablissement);
        $object->setEcoleDoctorale($ed);
        $object->setUniteRecherche($ur);

        return $object;
    }
}