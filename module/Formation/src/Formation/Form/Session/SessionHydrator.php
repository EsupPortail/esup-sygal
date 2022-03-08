<?php

namespace Formation\Form\Session;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Structure;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Formation\Entity\Db\Session;
use Laminas\Hydrator\HydratorInterface;

class SessionHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @param object|Session $object
     * @return array
     */
    public function extract(object $object) : array
    {
        $data = [
            'libelle' => $object->getFormation()->getLibelle(),
            'description' => $object->getDescription(),
            'site' => ($object->getSite())?$object->getSite()->getId():null,
            'responsable' => ($object->getResponsable())?['id' => $object->getResponsable()->getId(), 'label' => $object->getResponsable()->getNomComplet()]:null,
            'modalite' => $object->getModalite(),
            'type' => $object->getType(),
            'type_structure' => ($object->getTypeStructure())?$object->getTypeStructure()->getId():null,
            'taille_liste_principale' => $object->getTailleListePrincipale(),
            'taille_liste_complementaire' => $object->getTailleListeComplementaire(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Session $object
     * @return Session
     */
    public function hydrate(array $data, $object)
    {
        $description = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;
        /** @var Etablissement|null $site */
        $site = (isset($data['site']))?$this->getEtablissementService()->getRepository()->find($data['site']):null;
        /** @var Individu|null $responsable */
        $responsable = (isset($data['responsable']) AND trim($data['responsable']['id']) !== '')?$this->getIndividuService()->getRepository()->find($data['responsable']['id']):null;
        $modalite = (isset($data['modalite']))?$data['modalite']:null;
        $type = (isset($data['type']))?$data['type']:null;
        /** @var Structure $structure */
        $structure = (isset($data['type_structure']) AND trim($data['type_structure']) !== "")?$this->getStructureService()->getRepository()->find($data['type_structure']):null;
        $tailleListePrincipale = (isset($data['taille_liste_principale']) AND trim($data['taille_liste_principale']) !== '')?trim($data['taille_liste_principale']):null;
        $tailleListeComplementaire = (isset($data['taille_liste_complementaire']) AND trim($data['taille_liste_complementaire']) !== '')?trim($data['taille_liste_complementaire']):null;

        $object->setDescription($description);
        $object->setSite($site);
        $object->setResponsable($responsable);
        $object->setModalite($modalite);
        $object->setType($type);
        $object->setTypeStructure($structure);
        $object->setTailleListePrincipale((int) $tailleListePrincipale);
        $object->setTailleListeComplementaire((int) $tailleListeComplementaire);
        return $object;
    }


}