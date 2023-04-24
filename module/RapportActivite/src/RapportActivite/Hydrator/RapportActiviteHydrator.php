<?php

namespace RapportActivite\Hydrator;

use Doctrine\Inflector\Inflector;
use Doctrine\Persistence\ObjectManager;
use Laminas\Hydrator\Strategy\BooleanStrategy;
use RapportActivite\Entity\ActionDiffusionCultureScientifique;
use RapportActivite\Entity\AutreActivite;
use RapportActivite\Entity\Db\RapportActivite;
use Doctrine\ORM\EntityManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;

/**
 * @property EntityManager $objectManager
 *
 * @author Unicaen
 */
class RapportActiviteHydrator extends DoctrineObject
{
    public function __construct(ObjectManager $objectManager, $byValue = true, ?Inflector $inflector = null)
    {
        parent::__construct($objectManager, $byValue, $inflector);

        $this->addStrategy('estFinContrat', new BooleanStrategy('1', '0'));
    }

    /**
     * @param array $data
     * @param RapportActivite $object
     * @return \RapportActivite\Entity\Db\RapportActivite
     */
    public function hydrate(array $data, $object): RapportActivite
    {
        $formationsSpecifiques = $data['formationsSpecifiques'];
        usort($formationsSpecifiques, fn($a, $b) => $b->getTemps() <=> $a->getTemps());
        $formationsSpecifiques = array_map(fn($entity) => $entity->toArray(), $formationsSpecifiques);
        $data['formationsSpecifiques'] = json_encode($formationsSpecifiques);

        $formationsTransversales = $data['formationsTransversales'];
        usort($formationsTransversales, fn($a, $b) => $b->getTemps() <=> $a->getTemps());
        $formationsTransversales = array_map(fn($entity) => $entity->toArray(), $formationsTransversales);
        $data['formationsTransversales'] = json_encode($formationsTransversales);

        $actionsDiffusionCultureScientifique = $data['actionsDiffusionCultureScientifique'];
        usort($actionsDiffusionCultureScientifique, fn($a, $b) => $a->getDate() <=> $b->getDate());
        $actionsDiffusionCultureScientifique = array_map(fn($action) => $action->toArray(), $actionsDiffusionCultureScientifique);
        $data['actionsDiffusionCultureScientifique'] = json_encode($actionsDiffusionCultureScientifique);

        $autresActivites = $data['autresActivites'];
        usort($autresActivites, fn($a, $b) => $a->getDate() <=> $b->getDate());
        $autresActivites = array_map(fn($action) => $action->toArray(), $autresActivites);
        $data['autresActivites'] = json_encode($autresActivites);

        return parent::hydrate($data, $object);
    }

    /**
     * @param RapportActivite $object
     * @return array
     */
    public function extract($object): array
    {
        $data = parent::extract($object);
        $data['formationsSpecifiques'] = $object->getFormationsSpecifiquesToArray();
        $data['formationsTransversales'] = $object->getFormationsTransversalesToArray();
        $data['actionsDiffusionCultureScientifique'] = $object->getActionsDiffusionCultureScientifiqueToArray();
        $data['autresActivites'] = $object->getAutresActivitesToArray();

        return $data;
    }
}