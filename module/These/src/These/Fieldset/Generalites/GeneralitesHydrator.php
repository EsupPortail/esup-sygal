<?php

namespace These\Fieldset\Generalites;

use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;

class GeneralitesHydrator implements HydratorInterface
{
    use EtablissementServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];

        $data['etablissement'] = ($object->getEtablissement()) ? $object->getEtablissement()->getId() : null;
        $data['titre'] = $object->getTitre();
        $data['doctorant'] = ($object->getDoctorant()) ? [
            'id' => $object->getDoctorant()->getId(),
            'label' => $object->getDoctorant()->getIndividu()->getPrenom() . ' ' . ($object->getDoctorant()->getIndividu()->getNomUsuel() ?? $object->getDoctorant()->getIndividu()->getNomPatronymique())
        ] : null;
        $data['discipline'] = $object->getLibelleDiscipline();

        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        /** @var Etablissement $etablissement */
        $etablissement = (isset($data['etablissement']) and trim($data['etablissement']) !== '') ? $this->getEtablissementService()->getRepository()->find(trim($data['etablissement'])) : null;
        $object->setEtablissement($etablissement);

        $titre = (isset($data['titre']) and trim($data['titre'])) ? trim($data['titre']) : null;
        $object->setTitre($titre);

        /** @var Doctorant|null $doctorant */
        $doctorant = (isset($data['doctorant']) and isset($data['doctorant']['id']) and trim($data['doctorant']['id']) !== null) ? $this->doctorantService->getRepository()->find(trim($data['doctorant']['id'])) : null;
        $object->setDoctorant($doctorant);

        $discipline = (isset($data['discipline']) and trim($data['discipline'])) ? trim($data['discipline']) : null;
        $object->setLibelleDiscipline($discipline);

        return $object;
    }


}