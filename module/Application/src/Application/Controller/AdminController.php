<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

class AdminController extends AbstractController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}