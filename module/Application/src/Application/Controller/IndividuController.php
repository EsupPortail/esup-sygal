<?php

namespace Application\Controller;

use Application\Service\Individu\IndividuServiceAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class IndividuController extends AbstractActionController {
    use IndividuServiceAwareTrait;

    public function rechercherAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $individus = $this->getIndividuService()->getRepository()->findByText($term);

            $result = [];
            foreach ($individus as $xxx =>  $individu) {
                $result[] = array(
                    'id' => $individu['id'],
                    'label' => $individu['prenom1']. " " . $individu['nom_usuel'],
                    'extra' => $individu['source_code'],
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}