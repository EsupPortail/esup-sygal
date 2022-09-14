<?php

namespace These\Fieldset\Structures;

use Laminas\Hydrator\HydratorInterface;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;

class StructuresHydrator implements HydratorInterface
{
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];

        $data['unite-recherche'] = ($object->getUniteRecherche()) ? $object->getUniteRecherche()->getId() : null;
        $data['ecole-doctorale'] = ($object->getEcoleDoctorale()) ? $object->getEcoleDoctorale()->getId() : null;
        $data['etablissement'] = ($object->getEtablissement()) ? $object->getEtablissement()->getId() : null;

        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        // decaller pour le moment dans le controller
        /** @var UniteRecherche|null $uniteRecherche */
        $uniteRecherche = (isset($data['unite-recherche']) and trim($data['unite-recherche']) !== '') ? $this->getUniteRechercheService()->getRepository()->find(trim($data['unite-recherche'])) : null;
        /** @var EcoleDoctorale|null $ecoleDoctorale */
        $ecoleDoctorale = (isset($data['ecole-doctorale']) and trim($data['ecole-doctorale']) !== '') ? $this->getEcoleDoctoraleService()->getRepository()->find(trim($data['ecole-doctorale'])) : null;
        /** @var Etablissement $etablissement */
        $etablissement = (isset($data['etablissement']) and trim($data['etablissement']) !== '') ? $this->getEtablissementService()->getRepository()->find(trim($data['etablissement'])) : null;

        $object->setUniteRecherche($uniteRecherche);
        $object->setEcoleDoctorale($ecoleDoctorale);
        $object->setEtablissement($etablissement);

        return $object;
    }


}