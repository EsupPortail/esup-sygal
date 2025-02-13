<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Provider\Privilege\InterventionPrivileges;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class InterventionAssertion extends AbstractAssertion implements  AssertionInterface
{
    use UserContextServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;

    /**
     * !!!! Pour éviter l'erreur "Serialization of 'Closure' is not allowed"... !!!!
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    public function __invoke($page)
    {
        return true;
    }

    public function assertEntity(ResourceInterface $entity = null, $privilege = null)
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        /** @var These|HDR $entity */
        $proposition = $this->getPropositionService()->findOneForObject($entity);
        if($proposition instanceof PropositionThese){
            $date_soutenance = ($entity->getDateSoutenance())?$entity->getDateSoutenance():$proposition->getDate();
            $categorieCode = SoutenanceParametres::CATEGORIE;
        }else if($proposition instanceof PropositionHDR){
            $date_soutenance = $proposition->getDate();
            $categorieCode = \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;
        }

        $interval = $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::DELAI_INTERVENTION);
        $mini = (new DateTime())->sub(new DateInterval('P'.$interval.'D'));
        $maxi = (new DateTime())->add(new DateInterval('P'.$interval.'D'));

        switch ($privilege) {
            case InterventionPrivileges::INTERVENTION_AFFICHER:
                if($entity instanceof These){
                    return $this->userContextService->isStructureDuRoleRespecteeForThese($entity);
                }elseif($entity instanceof HDR){
                    return $this->userContextService->isStructureDuRoleRespecteeForHDR($entity);
                }
                return false;
            case InterventionPrivileges::INTERVENTION_MODIFIER:
                if ($date_soutenance < $mini OR $date_soutenance > $maxi) return false;
                if($entity instanceof These){
                    return $this->userContextService->isStructureDuRoleRespecteeForThese($entity);
                }elseif($entity instanceof HDR){
                    return $this->userContextService->isStructureDuRoleRespecteeForHDR($entity);
                }
                return false;
        }

        return false;
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

        $this->entity = $this->getRequestedEntity();
        $proposition = $this->getPropositionService()->findOneForObject($this->entity);

        if ($this->entity !== null) {
            if ($this->entity instanceof These){
                return $this->userContextService->isStructureDuRoleRespecteeForThese($this->entity);
            }elseif($this->entity instanceof HDR){
                return $this->userContextService->isStructureDuRoleRespecteeForHDR($this->entity);
            }
        }
        try {
            switch ($action) {
                case 'toggle-president-distanciel':
                case 'ajouter-visioconference-tardive':
                case 'supprimer-visioconference-tardive':
                    if($this->entity instanceof These){
                        $date_soutenance = ($this->entity->getDateSoutenance())?$this->entity->getDateSoutenance():$proposition->getDate();
                    }else if($this->entity instanceof HDR){
                        $date_soutenance = $proposition->getDate();
                    }
                    $categorieCode = ($proposition instanceof PropositionThese) ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;

                    $interval = $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::DELAI_INTERVENTION);
                    $mini = (new DateTime())->sub(new DateInterval('P'.$interval.'D'));
                    $maxi = (new DateTime())->add(new DateInterval('P'.$interval.'D'));
                    if ($date_soutenance < $mini OR $date_soutenance > $maxi) return false;
                break;
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    protected function getRequestedEntity(): These|HDR|null
    {
        $entity = null;
        if (($routeMatch = $this->getRouteMatch())) {
            if($routeMatch->getParam('these') !== null){
                $entity = $this->theseService->getRepository()->find($routeMatch->getParam('these'));
            }else if($routeMatch->getParam('hdr') !== null){
                $entity = $this->hdrService->getRepository()->find($routeMatch->getParam('hdr'));
            }
        }

        return $entity;
    }
}