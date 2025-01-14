<?php

namespace Formation\Assertion\Inscription;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use DateInterval;
use DateTime;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Exception;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Privilege\InscriptionPrivileges;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RuntimeException;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class InscriptionAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SessionServiceAwareTrait;

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

        $inscription = null;
        $sessionId = (($this->getMvcEvent()->getRouteMatch()->getParam('inscription')));
        if($sessionId) $inscription = $this->getInscriptionService()->getRepository()->find($sessionId);

        switch($action) {
            case 'ajouter' :
                $doctorantId = (($this->getMvcEvent()->getRouteMatch()->getParam('doctorant')));
                if ($inscription === null && $doctorantId) {
                    $sessionId = (($this->getMvcEvent()->getRouteMatch()->getParam('session')));
                    if($sessionId){
                        $doctorant = $this->doctorantService->getRepository()->find($doctorantId);
                        $session = $this->sessionService->getRepository()->find($sessionId);

                        $inscription = new Inscription();
                        $inscription->setDoctorant($doctorant);
                        $inscription->setSession($session);
                        return $this->canInscrire($inscription);

//                        return $this->scopeValide($inscription);
                    }
                    return false;
                }
                return true;
            case 'desinscription' :
                if ($inscription !== null) {
                    return $this->canDesinscrire($inscription);
                }
                return false;
        }
        return true;
    }

    /** @var Inscription $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $inscription = $entity;

        if ($privilege == InscriptionPrivileges::INSCRIPTION_AJOUTER) {
            if ($inscription->getHistoCreateur()) return $this->canDesinscrire($entity);
            return $this->canInscrire($entity);
        }

        return true;
    }

    private function canInscrire(?Inscription $inscription): bool
    {
        if (!$this->scopeValide($inscription)) return false;
        $doctorant = $inscription->getDoctorant();
        $session = $inscription->getSession();
        $inscriptions = $this->inscriptionService->getRepository()->findInscriptionsByDoctorant($doctorant);

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $sessionInscriptionEnregistree = $inscription->getSession();
            if (!$sessionInscriptionEnregistree || !$sessionInscriptionEnregistree->getDateDebut()) {
                continue; // Passe à l'inscription suivante si la session ou la date de début est manquante
            }

            $premiereAnneeUnivSessionInscription = $this->anneeUnivService->fromDate($sessionInscriptionEnregistree->getDateDebut())->getPremiereAnnee();
            //si la session demandée possède la même formation qu'une inscription possédée par l'étudiant,
            //on vérifie que l'année universitaire ne soit pas la même
            //sinon on refuse l'inscription, car l'étudiant ne peut pas s'inscrire sur une session appartenant à une formation déjà suivie sur la même année universitaire
            if ($session && $session->getFormation() === $sessionInscriptionEnregistree->getFormation()) {
                $sessionOuverteDateDebut = $session->getDateDebut();
                if ($sessionOuverteDateDebut) {
                    $premiereAnneeUnivSessionOuverte = $this->anneeUnivService->fromDate($sessionOuverteDateDebut)->getPremiereAnnee();
                    // Si les années universitaires sont identiques, on interdit l'inscription
                    if ($premiereAnneeUnivSessionOuverte === $premiereAnneeUnivSessionInscription) {
                        return false;
                    }
                }
            }
        }
        return true;
    }


    private function canDesinscrire(?Inscription $inscription): bool
    {
        if (!$this->scopeValide($inscription)) return false;
        $session = $inscription->getSession();
        $etatSession = $session->getEtat();
        if (in_array($etatSession->getCode(), [Session::ETAT_IMMINENTE, Session::ETAT_EN_COURS, Session::ETAT_CLOS_FINAL])) return false;

        if($session->getDateDebut()){
            $dateMax = DateTime::createFromFormat('d/m/Y H:m', $session->getDateDebut()->format('d/m/Y H:m'));
            try {
                $dateMax->sub(new DateInterval('P' . $this->getDelaiDescinscription() . 'D'));
            } catch (Exception $e) {
                throw new RuntimeException("Un problème est survenu lors de la manipulation de la date bloquante", 0, $e);
            }
            if ($dateMax < (new DateTime())) return false;
        }

        return true;
    }

    public function scopeValide(?Inscription $inscription): bool
    {
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return false;
        }

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $doctorant = $inscription->getDoctorant();
                $userDoctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
                return $doctorant === $userDoctorant;
        }
        return true;
    }
}