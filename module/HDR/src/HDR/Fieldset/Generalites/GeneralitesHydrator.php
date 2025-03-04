<?php

namespace HDR\Fieldset\Generalites;

use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\Inflector\Inflector;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Persistence\ObjectManager;
use HDR\Entity\Db\HDR;
use UnicaenApp\Hydrator\Strategy\GetIdStrategy;

class GeneralitesHydrator extends DoctrineObject
{
    use SourceServiceAwareTrait;

    public function __construct(ObjectManager $objectManager, bool $byValue = true, ?Inflector $inflector = null)
    {
        parent::__construct($objectManager, $byValue, $inflector);

        $this->addStrategy('etablissement', new GetIdStrategy());
    }

    /**
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var HDR $object */
        $data = parent::extract($object);

        $data['candidat'] = ($object->getCandidat()) ? [
            'id' => $object->getCandidat()->getId(),
            'label' => $object->getCandidat()->getIndividu()->getPrenom() . ' ' . ($object->getCandidat()->getIndividu()->getNomUsuel() ?? $object->getCandidat()->getIndividu()->getNomPatronymique())
        ] : null;
        $data['versionDiplome'] = $object->getVersionDiplome()?->getId();

        //Confidentialité
        $data['confidentialite'] = ($object->getDateFinConfidentialite() !== null) ? 1 : 0;
        
        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return object
     */
    public function hydrate(array $data, object $object): object
    {
        //Nécessaire sinon Doctrine pense que c'est les données appartenant à un fieldset
        $data['candidat'] = $data['candidat']['id'] ?? null; /** todo : utiliser {@see \UnicaenApp\Filter\SearchAndSelectFilter} dans le fieldset ! */

        $data['versionDiplome'] = (isset($data['versionDiplome']) && $data['versionDiplome'] !== "") ? $data['versionDiplome'] : null;

        $data['resultat'] = (isset($data['resultat']) && $data['resultat'] !== "") ? $data['resultat'] : null;

        $data['etatHDR'] = !empty($data['dateAbandon']) ? HDR::ETAT_ABANDONNEE : HDR::ETAT_EN_COURS;

        //date
        $conf = (isset($data['confidentialite']) and $data['confidentialite'] == true);
        $data['dateFinConfidentialite'] = (!empty($data['dateFinConfidentialite']) && $conf) ? $data['dateFinConfidentialite'] : null;

        return parent::hydrate($data, $object);
    }
}