<?php

namespace Individu\Controller\IndividuRoleEtablissement;

use Application\Controller\AbstractController;
use Individu\Service\IndividuRoleEtablissement\IndividuRoleEtablissementServiceAwareTrait;
use Laminas\View\Model\JsonModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class IndividuRoleEtablissementController extends AbstractController
{
    use EtablissementServiceAwareTrait;
    use IndividuRoleEtablissementServiceAwareTrait;

    public function rechercherEtablissementAction()
    {
        if ($term = $this->params()->fromQuery('term')) {
            $qb = $this->etablissementService->getRepository()->findByTextQb($term)
                ->andWhere('e.estInscription = :oui')->setParameter('oui', true)
                ->orderBy('structure.libelle');
            $etablissements = $qb->getQuery()->getArrayResult();
            $result = [];
            foreach ($etablissements as $etablissement) {
                $result[] = array(
                    'id' => $etablissement['id'],            // identifiant unique de l'item
                    'label' => $etablissement['structure']['libelle'],    // libellé de l'item
                    'extra' => $etablissement['structure']['sigle'],      // infos complémentaires (facultatives) sur l'item
                );
            }

            return new JsonModel($result);
        }
        exit;
    }
}