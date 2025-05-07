<?php

namespace InscriptionAdministrative\Process\Reception;

use Admission\Entity\Db\Admission;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Exception;
use Individu\Service\IndividuServiceAwareTrait;
use InscriptionAdministrative\Config\Reception\ReceptionConfig;
use InscriptionAdministrative\Config\Reception\ReceptionConfigAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Entity\Db\Traits\InscriptionAdministrativeAwareTrait;
use InscriptionAdministrative\Process\AbstractProcess;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManagerAwareTrait;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use InvalidArgumentException;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;
use These\Service\These\TheseServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareInterface;
use Webmozart\Assert\Assert;

class InscriptionAdministrativeReceptionProcess extends AbstractProcess
{
    use ReceptionConfigAwareTrait;
    use ReceptionRulePluginManagerAwareTrait;

    use InscriptionAdministrativeServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use AdmissionServiceAwareTrait;

    use InscriptionAdministrativeAwareTrait;

    private InscriptionAdministrativeApprenant $inscriptionAdministrativeApprenant;
    private ?InscriptionAdministrativeDoctorant $inscriptionAdministrativeDoctorant = null;

    private ?Admission $admission;
    private ?Doctorant $doctorant = null;
    private ?These $these = null;
    private ?TheseAnneeUniv $theseAnneeUniv = null;

    private ReceptionRuleInterface $receptionRule;

    private InscriptionAdministrativeReceptionProcessResult $result;

    private bool $commitRequired = false;

    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): void
    {
        $this->inscriptionAdministrative = $inscriptionAdministrative;

        /** @var InscriptionAdministrativeApprenant $iaApprenant */
        $iaApprenant = $this->inscriptionAdministrative->getInscriptionAdministrativeApprenant();
        if ($iaApprenant === null) {
            throw new InvalidArgumentException("Anomalie : l'IA n'est liée à aucun apprenant");
        }

        $this->inscriptionAdministrativeApprenant = $iaApprenant;
    }

    /**
     * Point d'entrée.
     */
    public function run(): void
    {
        $this->receptionRule = $this->receptionRulePluginManager->getRuleForInscriptionAdministrative($this->inscriptionAdministrative);

        $this->result = new InscriptionAdministrativeReceptionProcessResult();

        $this->beginTransaction();
        try {
            $this->process();
            if ($this->commitRequired) {
                $this->commit();
            }
            $this->result->setSuccess(true);
        } catch (Exception $e) {
            $this->result->setSuccess(false);
            $this->result->setException($e);
        }
    }

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    protected function commit(): void
    {
        if ($this->inscriptionAdministrativeDoctorant !== null) {
            $this->entityManager->persist($this->inscriptionAdministrativeDoctorant);
        }

        parent::commit();
    }

    /**
     * @see doc/integration_flux_inscrip_admin.drawio
     */
    private function process(): void
    {
        $this->receptionRule->execute();

        $this->commitRequired = false;

        if ($this->receptionRule->canCreateDoctorant()) {
            $this->createDoctorant();
        } else {
            $this->doctorant = $this->receptionRule->getFoundDoctorant();
        }

        if ($this->receptionRule->canUpdateDoctorant()) {
            if ($this->getUpdateDoctorantIfPossible()) {
                $this->updateDoctorant();
                // NB : la mise à jour complète du doctorant inclue la màj du code apprenant
            }
        } else {
            if ($this->receptionRule->canUpdateCodeApprenantDoctorant()) {
                $this->updateCodeApprenantDoctorant();
            }
        }

        if ($this->receptionRule->canCreateLinkInscriptionAdministrativeDoctorant()) {
            $this->createLinkInscriptionAdministrativeDoctorant();
        }

        if ($this->receptionRule->canCreateThese()) {
            $this->admission = $this->receptionRule->getFoundAdmission();
            $this->createThese();
        } else {
            $this->these = $this->receptionRule->getFoundThese(); // évite de fetcher à nouveau la thèse
        }

        if ($this->receptionRule->canCreateTheseAnneeUniv()) {
            $this->createTheseAnneeUniv();
        }
        if ($this->receptionRule->canLinkTheseAnneeUnivToInscriptionAdministrative()) {
            $this->linkTheseAnneeUnivToInscriptionAdministrative();
        }

        if ($this->receptionRule->canLinkAdmissionToDoctorant()) {
            $this->linkAdmissionToDoctorant();
        }
    }

    /**
     * Proxy pour {@see ReceptionConfig::getUpdateDoctorantIfPossible()}.
     */
    public function getUpdateDoctorantIfPossible(): bool
    {
        return $this->receptionConfig->getUpdateDoctorantIfPossible();
    }

    public function getResult(): InscriptionAdministrativeReceptionProcessResult
    {
        return $this->result;
    }


    private function createDoctorant(): void
    {
        $etablissement = $this->inscriptionAdministrativeApprenant->getSource()->getEtablissement();

        $individu = $this->individuService->newIndividuFromApprenant($this->inscriptionAdministrativeApprenant);

        $this->doctorant = $this->doctorantService->newDoctorant($individu, $etablissement);
        $this->doctorantService->updateDoctorantFromApprenant($this->doctorant, $this->inscriptionAdministrativeApprenant);
        $this->doctorantService->saveDoctorant($this->doctorant);

        $message = sprintf(
            "Le doctorant %s a été créé à partir de l'apprenant.",
            $this->doctorant->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);
        $this->result->setIsDoctorantCreated(true);
        $this->result->setDoctorant($this->doctorant);

        $this->commitRequired = true;
    }

    private function updateDoctorant(): void
    {
        $individu = $this->doctorant->getIndividu();
        $this->individuService->updateIndividuFromApprenant($individu, $this->inscriptionAdministrativeApprenant);
        $this->individuService->saveIndividu($individu);

        $this->doctorantService->updateDoctorantFromApprenant($this->doctorant, $this->inscriptionAdministrativeApprenant);
        $this->doctorantService->saveDoctorant($this->doctorant);

        $message = sprintf(
            "Le doctorant %s a été mis à jour à partir de l'apprenant.",
            $this->doctorant->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);
        $this->result->setIsDoctorantUpdated(true);
        $this->result->setDoctorant($this->doctorant);

        $this->commitRequired = true;
    }

    private function createThese(): void
    {
        $this->these = $this->theseService->newTheseFromAdmission($this->admission, $this->doctorant);
        $this->theseService->saveThese($this->these);

        $message = sprintf(
            "La thèse %s a été créée pour le doctorant %s à partir du dossier d'admission.",
            $this->these->getId(),
            $this->doctorant->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);
        $this->result->setIsTheseCreated(true);
        $this->result->setCreatedThese($this->these);

        $this->commitRequired = true;
    }

    private function createTheseAnneeUniv(): void
    {
        Assert::notNull($this->these, "Incohérence dans l'algo : une thèse devrait être dispo !"); // todo : généraliser ce type de vérif ?

        $annee = $this->inscriptionAdministrative->getPeriodeAnneeUniversitaire();

        $this->theseAnneeUniv = $this->theseAnneeUnivService->newTheseAnneeUniv($annee);
        $this->theseAnneeUniv->setThese($this->these);
        $this->theseAnneeUniv->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->theseAnneeUnivService->save($this->theseAnneeUniv);

        $message = sprintf(
            "L'année universitaire %s a été créée pour la thèse %s à partir de l'inscription administrative.",
            $this->theseAnneeUniv,
            $this->these->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);
        $this->result->setIsTheseAnneeUnivCreated(true);
        $this->result->setCreatedTheseAnneeUniv($this->theseAnneeUniv);

        $this->commitRequired = true;
    }

    private function linkTheseAnneeUnivToInscriptionAdministrative(): void
    {
        $this->theseAnneeUniv = $this->receptionRule->getFoundTheseAnneeUniv();
        $this->theseAnneeUniv->setInscriptionAdministrative($this->inscriptionAdministrative);
        $this->theseAnneeUnivService->save($this->theseAnneeUniv);

        $message = sprintf(
            "Le lien entre l'année universitaire %s et l'inscription administrative a été effectué.",
            $this->theseAnneeUniv,
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);

        $this->commitRequired = true;
    }

    private function updateCodeApprenantDoctorant(): void
    {
        $codeApprenant = $this->inscriptionAdministrativeApprenant->getCode();
        $this->doctorant->setCodeApprenantInSource($codeApprenant);

        $message = sprintf(
            "Le code apprenant '%s' a été renseigné sur le doctorant %s.",
            $codeApprenant,
            $this->doctorant->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);

        $this->commitRequired = true;
    }

    private function createLinkInscriptionAdministrativeDoctorant(): void
    {
        // vérif que le lien n'existe pas déjà, auquel cas stop.
        if ($this->inscriptionAdministrative->getInscriptionAdministrativeDoctorant() !== null) {
            return;
        }

        $this->inscriptionAdministrativeDoctorant =
            $this->inscriptionAdministrative->addInscriptionAdministrativeDoctorantForDoctorant($this->doctorant);

        $message = sprintf(
            "Le doctorant %s a été lié à l'inscription administrative %s.",
            $this->doctorant->getId(),
            $this->inscriptionAdministrativeApprenant->getId()
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);

        $this->commitRequired = true;
    }

    private function linkAdmissionToDoctorant(): void
    {
        $this->admission->setDoctorant($this->doctorant);

        $message = sprintf(
            "Le doctorant %s a été lié à l'admission %s.",
            $this->doctorant->getId(),
            $this->admission->getId(),
        );
        $this->result->addMessage($message, MessageAwareInterface::INFO);

        $this->commitRequired = true;
    }
}