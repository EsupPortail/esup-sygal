<?php

namespace TheseUnitTest\Controller;

use These\Controller\TheseController;
use Application\Service\UserContextService;
use BjyAuthorize\Exception\UnAuthorizedException;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use UnicaenAuth\Service\AuthorizeService;

/**
 * Class TheseControllerTest
 *
 * @package ApplicationTest\Controller
 * @see TheseController
 */
class TheseControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function getUnAuthorizedURL()
    {
        return [
            ['/these'],
            ['/admin'],
            ['/droits'],
            ['/import'],
        ];
    }

    /**
     * @dataProvider getUnAuthorizedURL
     * @param string $url
     */
    public function test_url_necessitant_authentification($url)
    {
        $this->dispatch($url);
        $this->assertApplicationException(UnAuthorizedException::class);
    }

    /**
     * @param string $url
     */
    public function test_redirection_vers_accueil_si_doctorant_demande_page_des_theses($url = '/these')
    {
        $prophecy = $this->mockAuthorizeService(AuthorizeService::class);
        $prophecy->isAllowed(Argument::cetera())->willReturn(true);
        $prophecy->getIdentity()->willReturn([
            'ldap' => []
        ]);

        $prophecy = $this->mockUserContextService(UserContextService::class);
        $prophecy->getSelectedRoleDoctorant()->willReturn(true /* tout sauf qqchose évalué à false */);

        $this->dispatch($url);
        $this->assertRedirectTo('/');
    }

    /**
     * @param string $url
     */
    public function test_tris_et_filtres_par_defaut_sur_la_page_des_theses($url = '/these')
    {
        $prophecy = $this->mockAuthorizeService(AuthorizeService::class);
        $prophecy->isAllowed(Argument::cetera())->willReturn(true);
        $prophecy->getIdentity()->willReturn([
            'ldap' => []
        ]);

        $prophecy = $this->mockUserContextService(UserContextService::class);
        $prophecy->getSelectedRoleDoctorant()->willReturn(false /* tout sauf qqchose évalué à true */);

        $this->dispatch($url);
        $this->assertRedirectTo($url . '?etatThese=E&sort=t.datePremiereInscription&direction=asc');
    }

    /**
     * @param string $userContextServiceClass
     * @return UserContextService|ObjectProphecy
     */
    protected function mockUserContextService($userContextServiceClass)
    {
        $prophecy = parent::mockUserContextService($userContextServiceClass);
        $prophecy->setIndividuService(Argument::any())->willReturn();

        return $prophecy;
    }


}