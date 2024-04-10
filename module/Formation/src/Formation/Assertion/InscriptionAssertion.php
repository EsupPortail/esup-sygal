<?php

namespace Formation\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use DateInterval;
use DateTime;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Exception;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Privilege\InscriptionPrivileges;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RuntimeException;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class InscriptionAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;
    use InscriptionServiceAwareTrait;

    private ?int $delaiDescinscription = null;

    public function getDelaiDescinscription(): ?int
    {
        return $this->delaiDescinscription;
    }

    public function setDelaiDescinscription(?int $delaiDescinscription): void
    {
        $this->delaiDescinscription = $delaiDescinscription;
    }



    /**
     * @param array $page
     * @return bool
     */
    #[Pure] public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page): bool
    {
        return true;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $sessionId = (($this->getMvcEvent()->getRouteMatch()->getParam('isncription')));
        $inscription = $this->getInscriptionService()->getRepository()->find($sessionId);

        switch($action) {
            case 'desinscription' :
                if ($inscription !== null) {
                    return $this->canDescinscrire($inscription);
                }
                return false;
        }
        return true;
    }


    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }
        $this->admission = $entity;


        switch ($privilege) {
            case InscriptionPrivileges::INSCRIPTION_AJOUTER :
                return $this->canDescinscrire($entity);
        }

        return true;
    }

    private function canDescinscrire(?Inscription $inscription): bool
    {
        if (!$this->scopeValide($inscription)) return false;
        $session = $inscription->getSession();
        $etatSession = $session->getEtat();
        if (!in_array($etatSession->getCode(), [Session::ETAT_IMMINENTE, Session::ETAT_EN_COURS, Session::ETAT_CLOS_FINAL])) return false;

        $dateMax = DateTime::createFromFormat('d/m/Y H:m', $session->getDateDebut()->format('d/m/Y H:m'));
        try {
            $dateMax->sub(new DateInterval('P' . $this->getDelaiDescinscription() . 'D'));
        } catch (Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la manipulation de la date bloquante", 0, $e);
        }
        if ($dateMax < (new DateTime())) return false;

        return true;
    }

    public function scopeValide(?Inscription $inscription): bool
    {
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return false;
        }

        switch ($role->getRoleId()) {
            case Role::CODE_DOCTORANT :
                $doctorant = $inscription->getDoctorant();
                $userDoctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
                return $doctorant === $userDoctorant;
        }
        return true;

    }
}