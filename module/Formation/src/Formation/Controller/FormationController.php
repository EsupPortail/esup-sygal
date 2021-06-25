<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Formation;
use Formation\Service\Formation\FormationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class FormationController extends AbstractController 
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;

    public function indexAction()
    {
        /** @var Formation[] $formations */
        $formations = $this->getEntityManager()->getRepository(Formation::class)->findAll();

        return new ViewModel([
            'formations' => $formations,
        ]);
    }

    public function ajouterAction()
    {
        $formation = new Formation();
        $formation->setLibelle('test');
        $this->getFormationService()->create($formation);

        return $this->redirect()->toRoute('formation/formation');
    }
}