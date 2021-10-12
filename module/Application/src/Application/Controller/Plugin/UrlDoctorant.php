<?php

namespace Application\Controller\Plugin;

use Application\Entity\Db\These;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 09/06/16
 * Time: 16:48
 */
class UrlDoctorant extends UrlPlugin
{
    public function modifierPersopassUrl(These $these)
    {
        return $this->fromRoute('doctorant/modifier-persopass',
            ['doctorant' => $these->getDoctorant()->getId(), 'back' => 0],[], true
        );
    }
}