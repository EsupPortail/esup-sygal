<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;

class AdminController extends AbstractController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}