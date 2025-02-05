<?php

namespace Admission\Service\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Etat;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\AdmissionRepository;
use Admission\Entity\Db\TypeValidation;
use Admission\Entity\Db\Verification;
use Admission\Provider\Template\TexteTemplates;
use Admission\Service\Avis\AdmissionAvisServiceAwareTrait;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Entity\Db\Variable;
use Application\Service\BaseService;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use InvalidArgumentException;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;
use UnicaenRenderer\Entity\Db\Template;
use UnicaenRenderer\Service\Template\TemplateServiceAwareTrait;

class AdmissionService extends BaseService
{
    use EtudiantServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use AdmissionAvisServiceAwareTrait;
    use VariableServiceAwareTrait;
    use ConventionFormationDoctoraleServiceAwareTrait;
    use VerificationServiceAwareTrait;
    use TemplateServiceAwareTrait;

    const ADMISSION__AJOUTE__EVENT = 'ADMISSION__AJOUTE__EVENT';
    const ADMISSION__SUPPRIME__EVENT = 'ADMISSION__SUPPRIME__EVENT';

    private const DEFAULT_SOURCE_CODE = "SYG";


    /**
     * @return AdmissionRepository
     * @throws NotSupported
     */
    public function getRepository(): AdmissionRepository
    {
        /** @var AdmissionRepository $repo */
        $repo = $this->entityManager->getRepository(Admission::class);

        return $repo;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function create(Admission $admission): Admission
    {
        try {
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->flush($admission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }

        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function update(Admission $admission): Admission
    {
        try {
            $this->getEntityManager()->flush($admission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }

        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function historise(Admission $admission): Admission
    {
        try {
            $admission->historiser();
            $this->getEntityManager()->flush($admission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }
        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function restore(Admission $admission): Admission
    {
        try {
            $admission->dehistoriser();
            $this->getEntityManager()->flush($admission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }
        return $admission;
    }

    /**
     * @param Admission $admission
     * @return void
     * @throws ORMException
     */
    public function delete(Admission $admission): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $etudiant = $admission->getEtudiant()->first();
            if ($etudiant instanceof Etudiant) {
                $this->etudiantService->delete($etudiant);
            }

            $inscription = $admission->getInscription()->first();
            if ($inscription instanceof Inscription) {
                $this->inscriptionService->delete($inscription);
            }

            $financement = $admission->getFinancement()->first();
            if ($financement instanceof Financement) {
                $this->admissionFinancementService->delete($financement);
            }

            $conventionFormationDoctorale = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);
            if($conventionFormationDoctorale){
                $this->conventionFormationDoctoraleService->delete($conventionFormationDoctorale);
            }

            $this->documentService->deleteAllDocumentsForAdmission($admission);
            $this->admissionValidationService->deleteValidationForAdmission($admission);
            $this->admissionAvisService->deleteAllAvisForAdmission($admission);

            $this->getEntityManager()->remove($admission);
            $this->getEntityManager()->flush($admission);

            // commit
            $this->commit();
        } catch (ORMException $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * @param Admission $admission
     * @return void
     */
    public function deleteAllVerifications(Admission $admission): void
    {
        $this->verificationService->deleteAllVerificationFromAdmission($admission);
    }

    /**
     * @param Admission $admission
     * @return array Un tableau associatif des commentaires triées par ordre d'étape du formulaire.
     *  [
     *      1 => [
     *          "etape" => 'Étape 1',
     *          "commentaire" => 'Commentaire pour l'étape 1'
     *      ],
     *      2 => [
     *          "etape" => 'Étape 2',
     *          "commentaire" => 'Commentaire pour l'étape 2'
     *      ],
     *      ....
     *  ]
     */
    public function getCommentaires(Admission $admission): array
    {
        $verifs = $this->verificationService->getAllVerificationFromAdmission($admission);
        $verifications = [];
        /** @var Verification $verification */
        foreach($verifs as $verification){
            if($verification->getCommentaire()){
                $sections = [
                    ["etape" => 'Étape 1', "verification" => $verification->getEtudiant(), "ordre" => 1],
                    ["etape" => 'Étape 2', "verification" => $verification->getInscription(), "ordre" => 2],
                    ["etape" => 'Étape 3', "verification" => $verification->getFinancement(), "ordre" => 3],
                    ["etape" => 'Étape 4', "verification" => $verification->getDocument(), "ordre" => 4]
                ];

                foreach ($sections as $section) {
                    if ($section['verification']) {
                        $verifications[$section["ordre"]] = [
                            "etape" => $section["etape"],
                            "commentaire" => $verification->getCommentaire()
                        ];
                        break;
                    }
                }
            }
            ksort($verifications);
        }
        return $verifications;
    }

    public function changeEtatAdmission(AdmissionOperationInterface $operation, string $typeAction): Admission
    {
        switch (true) {
            case $operation instanceof AdmissionValidation:
                $code = $operation->getTypeValidation()->getCode();
                break;
            case $operation instanceof AdmissionAvis:
                $code = $operation->getAvis()->getAvisType()->getCode();
                break;
            default:
                throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }
        $admission = $operation->getAdmission();
        $etatRepository = $this->entityManager->getRepository(Etat::class);
        switch ($code) {
            case AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE:
                if($typeAction === "modifier" || $typeAction === "aviser"){
                    //Mise du dossier d'admission dans l'état "Validé"
                    if ($operation->getAvis()->getAvisValeur()->getCode() == AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_POSITIF) {
                        /** @var Etat $valide */
                        $valide = $etatRepository->findOneBy(["code" => Etat::CODE_VALIDE]);
                        $admission->setEtat($valide);
                        $this->update($admission);
                        break;
                        //Mise du dossier d'admission dans l'état "Rejeté"
                    }else if($operation->getAvis()->getAvisValeur()->getCode() == AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF){
                        /** @var Etat $rejete */
                        $rejete = $etatRepository->findOneBy(["code" => Etat::CODE_REJETE]);
                        $admission->setEtat($rejete);
                        $this->update($admission);
                        break;
                    }
                }
                break;
            case TypeValidation::CODE_ATTESTATION_HONNEUR:
                //Mise du dossier d'admission dans l'état "En cours de validation"
                if ($typeAction === "valider") {
                    $enCoursDeValidation = $etatRepository->findOneBy(["code" => Etat::CODE_EN_COURS_VALIDATION]);
                    $admission->setEtat($enCoursDeValidation);
                    $this->update($admission);
                    break;
                    //Mise du dossier d'admission dans l'état "En cours de saisie"
                }else if($typeAction === "devalider"){
                    /** @var Etat $enCoursDeSaisie */
                    $enCoursDeSaisie = $etatRepository->findOneBy(["code" => Etat::CODE_EN_COURS_SAISIE]);
                    $admission->setEtat($enCoursDeSaisie);
                    $this->update($admission);
                    break;
                }
                break;
            default:
                break;
        }
        return $admission;
    }

    /**
     * Genere la texte "M XXXX XXXXX, Le président de l'Universite de Caen Normandie"
     * @var Admission $admission
     * @return string
     */
    public function generateLibelleSignaturePresidenceForAdmission(Admission $admission): string
    {
        $etabInscription = $admission->getInscription()->first()->getEtablissementInscription();
        $libelle = "";
        if($etabInscription) {
            $ETB_LIB_NOM_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_NOM_RESP, $etabInscription);
            $ETB_LIB_TIT_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_TIT_RESP, $etabInscription);
            $ETB_ART_ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_ART_ETB_LIB, $etabInscription);
            $ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB, $etabInscription);

            $libelle .= $ETB_LIB_NOM_RESP ? $ETB_LIB_NOM_RESP->getValeur() : "";
            $libelle .= ", ";
            $libelle .= $ETB_LIB_TIT_RESP ? $ETB_LIB_TIT_RESP->getValeur() : "(Variable ETB_LIB_TIT_RESP introuvable)";
            $libelle .= " de ";
            $libelle .= $ETB_ART_ETB_LIB ? $ETB_ART_ETB_LIB->getValeur() : "";
            $libelle .= $ETB_LIB ? $ETB_LIB->getValeur() : "(Variable ETB_LIB introuvable)";
        }
        return $libelle;
    }

    public function generateUniqueNumeroCandidature(Admission $admission): string
    {
        $currentYear = (new \DateTime())->format('y');
        $id = $admission->getId();
        $formattedId = str_pad($id, 2, '0', STR_PAD_LEFT);
        return self::DEFAULT_SOURCE_CODE.$currentYear.$formattedId;
    }

    public function createTemplatesForEtablissement(Etablissement $etablissement): void
    {
        $codes = TexteTemplates::CODES;
        foreach($codes as $code){
            $template = clone $this->templateService->getTemplateByCode($code);
            $templateForEtab = $template;
            $templateForEtab->setCode($code."/".$etablissement->getStructure()->getSigle());

            $isNew = !$this->templateService->getTemplateByCode($templateForEtab->getCode());
            //Si le template n'existe pas encore, on le crée
            if($isNew) $this->templateService->create($templateForEtab);
        }
    }

    public function canEtabAccessModuleAdmission(Etablissement $etablissement): bool
    {
        $variable = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_UTILISATION_MODULE_ADMISSION, $etablissement);
        return $variable?->getValeur() === "true";
    }
}