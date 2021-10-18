<?php

namespace Application\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\AbstractMenu;

/**
 * Dessine le menu secondaire de l'application (vertical).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MenuSecondaire extends AbstractMenu
{
    protected $minDepth = 2;
    protected $renderParents = false;
    protected $onlyActiveBranch = true;
    protected $ulClass = 'nav flex-column menu-secondaire';
}