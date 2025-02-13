<?php

namespace Soutenance\Assertion;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use Exception;
use HDR\Entity\Db\HDR;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class AvisSoutenanceAssertion extends AbstractAssertion implements  AssertionInterface {
    use PropositionServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;

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

        $entity = $this->getRequestedEntity();
        if($entity instanceof Avis){
            $object = $entity->getProposition() instanceof PropositionThese ? $entity->getProposition()->getThese() : $entity->getProposition()->getHDR();
        }else{
            return true;
        }

        if($object instanceof These){
            if(!$this->userContextService->isStructureDuRoleRespecteeForThese($object)) return false;
        }elseif($object instanceof HDR){
            if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($object)) return false;
        }

        return true;
    }

    /**
     * @param ActeurThese|ActeurHDR $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {

        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        /**
         * @var ActeurThese|ActeurHDR $rapporteur
         * @var These|HDR $object
         */
        $rapporteur = $entity;
        $object = $rapporteur instanceof ActeurThese ? $rapporteur->getThese() : $rapporteur->getHDR();

        if($object instanceof These){
            if(!$this->userContextService->isStructureDuRoleRespecteeForThese($object)) return false;
        }elseif($object instanceof HDR){
            if(!$this->userContextService->isStructureDuRoleRespecteeForHDR($object)) return false;
        }

        switch ($privilege) {
            /**
             * Les personnes pouvant visualiser l'avis de soutenance sont :
             * - l'administrateur technique ou observateur COMUE
             * - le BdD de l'établissement de la thèse
             * - les directeurs/co-directeurs de la thèses
             * - le rapporteur émettant l'avis
             */
            case AvisSoutenancePrivileges::AVIS_VISUALISER :
            /**
             * Les personnes pouvant éditer l'avis de soutenance sont :
             * - le rapporteur émettant l'avis
             */
            case AvisSoutenancePrivileges::AVIS_MODIFIER :
                try {
                    $currentDate = new DateTime();
                } catch (Exception $e) {
                    throw new RuntimeException("Problème de récupération de la date");
                }

                /** @var Proposition $proposition */
                $proposition = $this->getPropositionService()->findOneForObject($object);
                $dateRetour = ($proposition->getRenduRapport())->add(new DateInterval('P1D'));
                if ($currentDate > $dateRetour) return false;
            /**
             * Les personnes pouvant révoquer un avis
             * - le rapporteur
             * - le bdd de l'etablissement
             */
            case AvisSoutenancePrivileges::AVIS_ANNULER :
        }
        return true;
    }

    protected function getRequestedEntity(): Avis|null
    {
        $avis = null;
        if (($routeMatch = $this->getRouteMatch())) {
            if($routeMatch->getParam('rapporteur') !== null){
                $membre = $this->membreService->getEntityManager()->getRepository(Membre::class)->find($routeMatch->getParam('rapporteur'));
                if($membre) $avis = $this->avisService->getAvisByMembre($membre);
            }
        }

        return $avis;
    }
}