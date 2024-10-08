<?php

namespace Application\Assertion\AutorisationInscription;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\AnneeUniv;
use Application\Entity\Db\AutorisationInscription;
use Application\Entity\Db\Rapport;
use Application\Provider\Privilege\AutorisationInscriptionPrivileges;
use Application\Service\AutorisationInscription\AutorisationInscriptionServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AutorisationInscriptionAssertion extends AbstractAssertion implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;
    use UserContextServiceAwareTrait;
    use AutorisationInscriptionServiceAwareTrait;
    use RapportServiceAwareTrait;

    private ?AutorisationInscription $autorisationInscription = null;
    private ?Rapport $rapport = null;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
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

        $this->autorisationInscription = $this->getRequestedAutorisationInscription();
        $these = $this->rapport?->getThese();
        try {
            if ($action == 'ajouter') {
                if ($this->autorisationInscription !== null) return false;
                if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                    $this->assertTrue(
                        $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                        "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
                    );
                }
                return $this->canAjouterAutorisationInscription($these);
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /**
     * @param AutorisationInscription $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->autorisationInscription = $entity;
        if($this->autorisationInscription->getId() === null){
            $this->autorisationInscription = $this->autorisationInscriptionService->getRepository()->findOneBy(["rapport" => $this->autorisationInscription->getRapport()->getId()]) ?
                $this->autorisationInscriptionService->getRepository()->findOneBy(["rapport" => $this->autorisationInscription->getRapport()->getId()]) :
                $this->autorisationInscription;
        }

        try {
            switch ($privilege) {
                case AutorisationInscriptionPrivileges::AUTORISATION_INSCRIPTION_AJOUTER:
                    if($this->autorisationInscription->getId()) return false;

                    $this->rapport = $this->autorisationInscription->getRapport();
                    $these = $this->rapport?->getThese();

                    if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                        $this->assertTrue(
                            $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                            "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
                        );
                    }
                    return $this->canAjouterAutorisationInscription($these);
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
        return true;
    }

    private function canAjouterAutorisationInscription(These $these): bool
    {
        //Si la thèse est importée, ne pas donner le droit de créer une autorisation d'inscription (pour l'instant)
        if($these->getSource()->getImportable()) return false;

        $anneesInscriptionThese = array_map(function(TheseAnneeUniv $anneeUniv) {
            return $anneeUniv->getPremiereAnnee();
        },  $these->getAnneesUnivInscription()->toArray());
        $derniereAnneeUnivInscription = AnneeUniv::fromPremiereAnnee(max($anneesInscriptionThese));
        return $derniereAnneeUnivInscription === $this->rapport->getAnneeUniv();
    }

    private function getRequestedAutorisationInscription(): ?AutorisationInscription
    {
        $autorisationInscription = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('rapport')) {
            $autorisationInscription = $this->autorisationInscriptionService->getRepository()->findOneBy(["rapport" => $id]);
            $this->rapport = $this->rapportService->getRepository()->find($id);
        }

        return $autorisationInscription;
    }
}