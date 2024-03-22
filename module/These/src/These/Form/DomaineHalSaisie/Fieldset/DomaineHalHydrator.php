<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Application\Entity\Db\DomaineHal;
use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use These\Entity\Db\These;

class DomaineHalHydrator extends DoctrineObject
{
    use DomaineHalServiceAwareTrait;

    /**
     * @param These $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];
        foreach ($object->getDomainesHal() as $domaineHal) {
            $data["domaineHal"][] = $domaineHal->getId();
        }

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return These
     */
    public function hydrate(array $data, object $object): object
    {
        if(isset($data["domaineHal"]) && is_array($data["domaineHal"])){
            //Si aucun domaine n'est sélectionné, on retire les potentiels domaines déjà enregistrés
            if(count($data["domaineHal"]) === 1 && $data["domaineHal"][0] === ""){
                foreach ($object->getDomainesHal() as $domaineHal) {
                    $object->removeDomainesHal($domaineHal);
                }
                return parent::hydrate($data, $object);
            }
            foreach ($data["domaineHal"] as $idDomaineHal) {
                // Vérifier si le domaine est déjà associé à la thèse
                $domaineHalExistant = $object->getDomainesHal()->filter(function ($domaineHal) use ($idDomaineHal) {
                    return $domaineHal->getId() === $idDomaineHal;
                })->first();

                // Si le domaine n'est pas déjà associé à la thèse, l'ajouter
                if (!$domaineHalExistant) {
                    $domaineHal = $this->domaineHalService->getEntityManager()->getRepository(DomaineHal::class)->find($idDomaineHal);
                    $object->addDomainesHal($domaineHal);
                }
            }
            foreach ($object->getDomainesHal() as $domaineHal) {
                $idDomaineHal = $domaineHal->getId();

                // Vérifier si l'identifiant du domaine associé est présent dans les identifiants des domaines sélectionnés dans le formulaire
                if (!in_array($idDomaineHal, $data["domaineHal"])) {
                    // Le domaine n'est pas sélectionné dans le formulaire, donc le supprimer de l'entité These
                    $object->removeDomainesHal($domaineHal);
                }
            }
        }

        return parent::hydrate($data, $object);
    }
}