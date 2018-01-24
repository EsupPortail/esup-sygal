<?php

namespace Application\Controller;

use Application\Entity\Db\Fichier;
use Application\RouteMatch;
use Zend\View\Model\JsonModel;

class FichierController extends AbstractController
{
    public function uploadAction()
    {
        $result = $this->uploader()->upload();

        if ($result instanceof JsonModel) {
            return $result;
        }
        if (is_array($result)) {
            return $result;
        }

        return false;
    }

    public function downloadAction()
    {
        $fichier = $this->requestFichier();

        $this->uploader()->download($fichier);
    }

    /**
     * @return Fichier
     */
    private function requestFichier()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getFichier();
    }
}
