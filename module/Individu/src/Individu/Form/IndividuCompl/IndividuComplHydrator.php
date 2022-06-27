<?php

namespace Individu\Form\IndividuCompl;

use Structure\Entity\Db\Etablissement;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuCompl;
use Structure\Entity\Db\UniteRecherche;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndividuComplHydrator implements HydratorInterface {
    use EntityManagerAwareTrait;

    /**
     * @param IndividuCompl $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data  = [
            'individu' => [
                'id' => ($object->getIndividu())?$object->getIndividu()->getId():null,
                'label' => ($object->getIndividu())?$object->getIndividu()->getNomComplet():null,
            ],
            'email' => ($object->getEmail())?:null,
            'etablissement' => [
                'id' => ($object->getEtablissement())?$object->getEtablissement()->getId():null,
                'label' => ($object->getEtablissement())?$object->getEtablissement()->getLibelle():null,
            ],
            'uniteRecherche' => [
                'id' => ($object->getUniteRecherche())?$object->getUniteRecherche()->getId():null,
                'label' => ($object->getUniteRecherche())?$object->getUniteRecherche()->getLibelle():null,
            ],
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param IndividuCompl $object
     * @return IndividuCompl
     */
    public function hydrate(array $data, object $object)
    {
        /** @var Individu|null $individu */
        $individuId = (isset($data['individu']) AND isset($data['individu']['id']))?$data['individu']['id']:null;
        $individu = ($individuId)?$this->getEntityManager()->getRepository(Individu::class)->find($individuId):null;
        /** @var string $email */
        $email = (isset($data['email']) AND trim($data['email']) !== '')?trim($data['email']):null;
        /** @var Etablissement|null $etablissement */
        $etablissementId = (isset($data['etablissement']) AND isset($data['etablissement']['id']))?$data['etablissement']['id']:null;
        $etablissement = ($etablissementId)?$this->getEntityManager()->getRepository(Etablissement::class)->find($etablissementId):null;
        /** @var UniteRecherche|null $unite */
        $uniteId = (isset($data['uniteRecherche']) AND isset($data['uniteRecherche']['id']))?$data['uniteRecherche']['id']:null;
        $unite = ($uniteId)?$this->getEntityManager()->getRepository(UniteRecherche::class)->find($uniteId):null;

        if ($individu) $object->setIndividu($individu);
        $object->setEtablissement($etablissement);
        $object->setUniteRecherche($unite);
        if ($email) $object->setEmail($email);
        return $object;
    }


}