<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        return new ViewModel();
    }

}