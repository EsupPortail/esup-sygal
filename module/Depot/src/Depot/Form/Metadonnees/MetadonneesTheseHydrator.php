<?php

namespace Depot\Form\Metadonnees;

use Depot\Entity\Db\MetadonneeThese;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use These\Entity\Db\These;

class MetadonneesTheseHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];
        $data['titre'] = $object->getTitre();
        /** @var These $object */
        if($object->getMetadonnee()){
            $data['langue'] = $object->getMetadonnee()->getLangue();
            $data['titreAutreLangue'] = $object->getMetadonnee()->getTitreAutreLangue();
            $data['resume'] = $object->getMetadonnee()->getResume();
            $data['resumeAnglais'] = $object->getMetadonnee()->getResumeAnglais();
            $data['motsClesLibresFrancais'] = $object->getMetadonnee()->getMotsClesLibresFrancais();
            $data['motsClesLibresAnglais'] = $object->getMetadonnee()->getMotsClesLibresAnglais();
        }

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array     $data
     * @return object
     */
    public function hydrate(array $data, $object): object
    {
        /** @var These $object */
        $metadonnee = $object->getMetadonnee() ? $object->getMetadonnee() : new MetadonneeThese();
        $metadonnee->setTitre($object->getTitre());
        $data["langue"] ? $metadonnee->setLangue($data["langue"]) : null;
        $data["titreAutreLangue"] ? $metadonnee->setTitreAutreLangue($data["titreAutreLangue"]) : null;
        $data["resume"] ? $metadonnee->setResume($data["resume"]) : null;
        $data["resumeAnglais"] ? $metadonnee->setResumeAnglais($data["resumeAnglais"]) : null;
        $data["motsClesLibresFrancais"] ? $metadonnee->setMotsClesLibresFrancais($data["motsClesLibresFrancais"]) : null;
        $data["motsClesLibresAnglais"] ? $metadonnee->setMotsClesLibresAnglais($data["motsClesLibresAnglais"]) : null;

        $object->addMetadonnee($metadonnee);

        return parent::hydrate($data, $object);
    }
}