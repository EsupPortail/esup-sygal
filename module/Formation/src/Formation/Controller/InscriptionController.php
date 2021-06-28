<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Inscription;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class InscriptionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use InscriptionServiceAwareTrait;

    public function indexAction()
    {
        /** @var Inscription[] $inscriptions */
        $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findAll();

        return new ViewModel([
            'inscriptions' => $inscriptions,
        ]);
    }
}