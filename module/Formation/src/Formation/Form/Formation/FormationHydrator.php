<?php

namespace Formation\Form\Formation;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Structure;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Module;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Zend\Hydrator\HydratorInterface;

class FormationHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ModuleServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @param Formation $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'libelle' => $object->getLibelle(),
            'description' => $object->getDescription(),
            'module' => ($object->getModule())?$object->getModule()->getId():null,
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
     * @param Formation $object
     * @return Formation
     */
    public function hydrate(array $data, $object)
    {
        $libelle = (isset($data['libelle']) AND trim($data['libelle']) !== '')?trim($data['libelle']):null;
        $description = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;
        /** @var Module|null $module */
        $module = (isset($data['module']) AND trim($data['module']) !== '')?$this->getModuleService()->getRepository()->find($data['module']):null;
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

        $object->setLibelle($libelle);
        $object->setDescription($description);
        $object->setModule($module);
        $object->setSite($site);
        $object->setResponsable($responsable);
        $object->setModalite($modalite);
        $object->setType($type);
        $object->setTypeStructure($structure);
        $object->setTailleListePrincipale($tailleListePrincipale);
        $object->setTailleListeComplementaire($tailleListeComplementaire);
        return $object;
    }


}