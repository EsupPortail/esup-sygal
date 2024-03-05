<?php

namespace Admission\Assertion\ConventionFormationDoctorale;

use Admission\Assertion\AdmissionAbstractAssertion;
use Admission\Entity\Db\Admission;
use Admission\Entity\Db\ConventionFormationDoctorale;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceAwareTrait;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class ConventionFormationDoctoraleAssertion extends AdmissionAbstractAssertion
{
    use AdmissionServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use ConventionFormationDoctoraleServiceAwareTrait;

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

        $this->admission = $this->getRequestedAdmission();
        $conventionAlreadyInBdd = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $this->admission]);

        try {
            if($this->admission){
                switch ($action) {
                    case 'ajouter-convention-formation':
                        $this->assertCanAjouterConventionFormationAdmission($conventionAlreadyInBdd, $this->admission);
                        break;
                }

                switch ($action) {
                    case 'modifier-convention-formation':
                        $this->assertCanModifierConventionFormationAdmission($conventionAlreadyInBdd, $this->admission);
                        break;
                }

                switch ($action) {
                    case 'generer-convention-formation':
                        $this->assertAppartenanceAdmission($this->admission);
                        $this->assertCanGenererConventionFormationAdmission($conventionAlreadyInBdd);
                        break;
                }

                switch ($action) {
                    case 'modifier-convention-formation':
                    case 'ajouter-convention-formation':
                        $this->assertEtatAdmission($this->admission);
                        $this->assertAppartenanceAdmission($this->admission);
                        break;
                }
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
     * @param ConventionFormationDoctorale $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        /** @var ConventionFormationDoctorale $conventionFormationDoctorale */
        $conventionFormationDoctorale = $entity;
        $this->admission = $this->getRequestedAdmission();
        try {

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_MODIFIER:
                case AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_VISUALISER:
                case AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_GENERER:
            }


        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    protected function assertCanAjouterConventionFormationAdmission($conventionAlreadyInBdd, $admission)
    {
        $this->assertTrue(
            empty($conventionAlreadyInBdd) && in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS_SAISIE]),
            "Une convention de formation doctorale a déjà été ajoutée"
        );
    }

    protected function assertCanModifierConventionFormationAdmission($conventionAlreadyInBdd, $admission)
    {
        $this->assertTrue(
            !empty($conventionAlreadyInBdd) && in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS_SAISIE]),
            "Aucune convention de formation doctorale n'a été ajoutée"
        );
    }

    protected function assertCanGenererConventionFormationAdmission($conventionAlreadyInBdd)
    {
        $this->assertTrue(
            $conventionAlreadyInBdd,
            "Aucune convention de formation doctorale n'a été créee"
        );
    }
}