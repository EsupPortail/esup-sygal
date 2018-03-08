<?php

namespace Application\Controller;

use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\View\Model\ViewModel;

class StatistiqueController extends AbstractController
    implements TheseServiceAwareInterface
{
    use TheseServiceAwareTrait;

    public function indexAction()
    {
        $theses = $this->theseService->getRepository()->findAll();
        return new ViewModel([
            'theses' => $theses,
        ]);
    }
}