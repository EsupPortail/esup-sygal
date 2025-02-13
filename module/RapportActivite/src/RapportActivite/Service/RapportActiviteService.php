<?php

namespace RapportActivite\Service;

use Application\Command\ShellCommandRunnerTrait;
use Application\Entity\AnneeUnivInterface;
use Application\Exporter\ExporterDataException;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\BaseService;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Closure;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Formatter\RapportActiviteNomFichierFormatter;
use RapportActivite\Provider\Parametre\RapportActiviteParametres;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Fichier\Exporter\PageValidationExportData;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterTrait;
use RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporterData;
use RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporterTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationServiceAwareTrait;

class RapportActiviteService extends BaseService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use StructureServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    use RapportActivitePdfExporterTrait;

    use PageValidationPdfExporterTrait;
    use ShellCommandRunnerTrait;

    use EventManagerAwareTrait;

    const RAPPORT_ACTIVITE__AJOUTE__EVENT = 'RAPPORT_ACTIVITE__AJOUTE__EVENT';
    const RAPPORT_ACTIVITE__MODIFIE__EVENT = 'RAPPORT_ACTIVITE__MODIFIE__EVENT';
    const RAPPORT_ACTIVITE__SUPPRIME__EVENT = 'RAPPORT_ACTIVITE__SUPPRIME__EVENT';

    /**
     * Fetch complet d'un rapport par son id :
     *  - Les relations suivantes doivent être sélectionnées : 'rapportAvis->avis->avisType' ;
     *  - L'orderBy 'avisType.ordre' doit être spécifié.
     *
     * @param int $id
     * @return RapportActivite|null
     */
    public function fetchRapportById(int $id): ?RapportActivite
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb->where('ra = :id')->setParameter('id', $id);

        $this->addRelationships($qb);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * @param These $these
     * @return RapportActivite[]
     */
    public function findRapportsForThese(These $these): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $this->addRelationships($qb);
        $qb->andWhere('t =:these')->setParameter('these', $these);

        return $qb->getQuery()->getResult();
    }

    private function addRelationships(DefaultQueryBuilder $qb)
    {
        //
        // ATTENTION ! Lors du fetch des rapports :
        // - Les relations suivantes doivent avoir été sélectionnées : 'rapportAvis->avis->avisType' ;
        // - L'orderBy 'avisType.ordre' doit avoir été utilisé.
        //
        $qb
            ->addSelect('t, f, d, i, rav, raa, a, at')
            ->join('ra.these', 't')
            ->leftJoin('ra.fichier', 'f')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'i')
            ->leftJoin('ra.rapportValidations', 'rav')
            ->leftJoin('ra.rapportAvis', 'raa')
            ->leftJoin('raa.avis', 'a')
            ->leftJoin('a.avisType', 'at')
            ->andWhereNotHistorise()
            ->addOrderBy('ra.anneeUniv')
            ->addOrderBy('ra.estFinContrat')
            ->addOrderBy('at.ordre');
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(RapportActivite::class);
    }

    /**
     * Retourne une nouvelle instance de {@see RapportActivite}, liée à la thèse spécifiée.
     *
     * @param \These\Entity\Db\These $these
     * @return \RapportActivite\Entity\Db\RapportActivite
     */
    public function newRapportActivite(These $these): RapportActivite
    {
        $rapportActivite = new RapportActivite();
        $rapportActivite->setThese($these);

        return $rapportActivite;
    }

    /**
     * Enregistre le rapport spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @return \RapportActivite\Event\RapportActiviteEvent Rapport annuel créé
     */
    public function saveRapport(RapportActivite $rapportActivite): RapportActiviteEvent
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($rapportActivite);
            $this->entityManager->flush($rapportActivite);
            $this->entityManager->commit();

            $event = $this->triggerEvent(
                $rapportActivite->getId() ? self::RAPPORT_ACTIVITE__MODIFIE__EVENT : self::RAPPORT_ACTIVITE__AJOUTE__EVENT,
                $rapportActivite,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement du rapport, rollback!", 0, $e);
        }

        return $event;
    }

    /**
     * @param RapportActivite $rapportActivite
     * @return \RapportActivite\Event\RapportActiviteEvent
     */
    public function deleteRapport(RapportActivite $rapportActivite): RapportActiviteEvent
    {
        $fichier = $rapportActivite->getFichier();

        $this->entityManager->beginTransaction();
        try {
            $this->rapportActiviteAvisService->deleteAllAvisForRapportActivite($rapportActivite);
            $this->rapportActiviteValidationService->deleteRapportValidationForRapportActivite($rapportActivite);
            if ($fichier) {
                $rapportActivite->removeFichier();
                $this->fichierService->supprimerFichiers([$fichier]);
            }

            $rapportActivite->historiser();
            $this->entityManager->flush($rapportActivite);
            $this->entityManager->commit();

            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__SUPPRIME__EVENT,
                $rapportActivite,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des rapports, rollback!", 0, $e);
        }

        return $event;
    }

    /**
     * @param bool $cacheable
     * @return array
     */
    public function findDistinctAnnees(bool $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->distinct()
            ->select("ra.anneeUniv")
            ->orderBy("ra.anneeUniv", 'desc');

        $qb->setCacheable($cacheable);

        return array_map(function ($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    /**
     * @param \These\Entity\Db\These $these
     * @param bool $cacheable
     * @return RapportActivite[] [int => Rapport[]]
     */
    public function findRapportsParAnneesForThese(These $these, bool $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->join('ra.these', 't', Join::WITH, 't = :these')->setParameter('these', $these)
            ->orderBy("ra.anneeUniv", 'desc');

        /** @var RapportActivite $rapports */
        $rapports = $qb->setCacheable($cacheable)->getQuery()->getArrayResult();

        $rapportsParAnnees = [];
        foreach ($rapports as $rapport) {
            $anneeUniv = $rapport['anneeUniv'];
            if (!array_key_exists($anneeUniv, $rapportsParAnnees)) {
                $rapportsParAnnees[$anneeUniv] = [];
            }
            $rapportsParAnnees[$anneeUniv][] = $rapport;
        }

        return $rapportsParAnnees;
    }

    /**
     * @param \Application\Entity\AnneeUnivInterface $anneeUniv
     * @return \Closure
     */
    public function getFilterRapportsByAnneeUniv(AnneeUnivInterface $anneeUniv): Closure
    {
        return function (RapportActivite $rapport) use ($anneeUniv) {
            return $rapport->getAnneeUniv()->getPremiereAnnee() === $anneeUniv->getPremiereAnnee();
        };
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @throws \Application\Exporter\ExporterDataException
     */
    public function genererRapportActivitePdf(RapportActivite $rapport): void
    {
        $f = new RapportActiviteNomFichierFormatter();
        $filename = $f->filter($rapport);

        $outputFilepath = sys_get_temp_dir() . '/' . $filename;

        $data = $this->createRapportActivitePdfExporterData($rapport);

        $exporter = clone $this->rapportActivitePdfExporter; // clonage indispensable
        $exporter->setMarginTop(40);
        $exporter->setWatermark("CONFIDENTIEL");
        $exporter->getMpdf()->watermarkTextAlpha = 0.1;
        $exporter->setVars(['rapport' => $rapport, 'data' => $data]);
        $exporter->export($outputFilepath, Pdf::DESTINATION_BROWSER);
    }

    /**
     * @throws \Application\Exporter\ExporterDataException Pb de données bloquant
     */
    private function createRapportActivitePdfExporterData(RapportActivite $rapport): RapportActivitePdfExporterData
    {
        $data = new RapportActivitePdfExporterData();

        $data->rapport = $rapport;

        $these = $rapport->getThese();
        $ed = $these->getEcoleDoctorale();
        $ur = $these->getUniteRecherche();

        if ($ed === null) {
            throw new ExporterDataException("La thèse n'est rattachée à aucune école doctorale");
        }
        if ($ur === null) {
            throw new ExporterDataException("La thèse n'est rattachée à aucune unité de recherche");
        }

        $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(true);

        // Logos COMUE & établissements d'inscription
        $data->logosEtablissements = [];
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            if (!$comue->getStructure()->getCheminLogo()) {
                throw new ExporterDataException("La COMUE '{$comue}' n'a aucun logo !");
            }
            try {
                $data->logosEtablissements[] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                throw new ExporterDataException(
                    "Accès impossible au logo de la COMUE '{$comue}' : " . $e->getMessage());
            }
        }
        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions();
        if (!$etablissements) {
            throw new ExporterDataException("Aucun établissement d'inscription trouvé !");
        }
        foreach ($etablissements as $etablissement) {
            if (!$etablissement->getStructure()->getCheminLogo()) {
                throw new ExporterDataException("L'établissement '{$etablissement}' n'a aucun logo !");
            }
            try {
                $data->logosEtablissements[] = $this->fichierStorageService->getFileForLogoStructure($etablissement->getStructure());
            } catch (StorageAdapterException $e) {
                throw new ExporterDataException(
                    "Accès impossible au logo de l'établissement '{$etablissement}' : " . $e->getMessage());
            }
        }

        // Collège des ED (CED)
        if ($ced = $this->etablissementService->fetchEtablissementCed()) {
            if (!$ced->getStructure()->getCheminLogo()) {
                throw new ExporterDataException("Le CED n'a aucun logo !");
            }
            try {
                $data->logoCED = $this->fichierStorageService->getFileForLogoStructure($ced->getStructure());
            } catch (StorageAdapterException $e) {
                throw new ExporterDataException(
                    "Accès impossible au logo du CED : " . $e->getMessage());
            }
        }

        // operations
        $data->operations = $this->rapportActiviteOperationRule->getOperationsForRapport($rapport);

        $data->anneeUnivCourante = $this->anneeUnivService->courante();

        return $data;
    }

    /**
     * @throws \Application\Exporter\ExporterDataException Une structure n'a aucun logo
     */
    public function createPageValidationDataForRapport(RapportActivite $rapport): PageValidationExportData
    {
        $exportData = new PageValidationExportData();

        $these = $rapport->getThese();
        $etablissement = $these->getEtablissement();
        $ed = $these->getEcoleDoctorale();
        $ur = $these->getUniteRecherche();

        if ($ed === null) {
            throw new ExporterDataException("La thèse n'est rattachée à aucune école doctorale");
        }
        if ($ur === null) {
            throw new ExporterDataException("La thèse n'est rattachée à aucune unité de recherche");
        }

        // généralités
        $exportData->titre = $these->getTitre();
        $exportData->specialite = (string) $these->getDiscipline();

        // doctorant
        if ($these->getDoctorant()) {
            $exportData->doctorant = strtoupper($these->getDoctorant()->getIndividu()->getNomCompletFormatter()->avecCivilite()->f());
        }

        // structures
        $exportData->etablissement = $etablissement->getStructure()->getLibelle();
        $exportData->ecoleDoctorale = $ed->getStructure()->getLibelle();
        $exportData->uniteRecherche = $ur->getStructure()->getLibelle();

        $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(true);

        // logo COMUE
        $exportData->useCOMUE = false;
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $exportData->useCOMUE = true;
            if (!$comue->getStructure()->getCheminLogo()) {
                throw new ExporterDataException("La COMUE '{$comue}' n'a aucun logo !");
            }
            try {
                $exportData->logoCOMUE = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                throw new ExporterDataException(
                    "Accès impossible au logo de la COMUE '{$comue}' : " . $e->getMessage());
            }
        }

        // logo etablissement
        if (!$etablissement->getStructure()->getCheminLogo()) {
            throw new ExporterDataException("L'établissement '{$etablissement}' n'a aucun logo !");
        }
        try {
            $exportData->logoEtablissement = $this->fichierStorageService->getFileForLogoStructure($etablissement->getStructure());
        } catch (StorageAdapterException $e) {
            throw new ExporterDataException(
                "Accès impossible au logo de l'établissement '{$etablissement}' : " . $e->getMessage());
        }

        // logo ED
        if (!$ed->getStructure()->getCheminLogo()) {
            throw new ExporterDataException("L'ED '{$ed}' n'a aucun logo !");
        }
        try {
            $exportData->logoEcoleDoctorale = $this->fichierStorageService->getFileForLogoStructure($ed->getStructure());
        } catch (StorageAdapterException $e) {
            throw new ExporterDataException(
                "Accès impossible au logo de l'ED '{$ed}' : " . $e->getMessage());
        }

        // logo UR
        if (!$ur->getStructure()->getCheminLogo()) {
            throw new ExporterDataException("L'UR '{$ur}' n'a aucun logo !");
        }
        try {
            $exportData->logoUniteRecherche = $this->fichierStorageService->getFileForLogoStructure($ur->getStructure());
        } catch (StorageAdapterException $e) {
            throw new ExporterDataException(
                "Accès impossible au logo de l'UR '{$ur}' : " . $e->getMessage());
        }

        // avis
        $exportData->mostRecentAvis = $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType(
            $rapport, RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST
        );

        // validation
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO);
        $exportData->validation = $rapport->getRapportValidationOfType($typeValidation);

        // signature ED
        $signatureEcoleDoctorale = $this->findSignatureEcoleDoctorale($ed, $etablissement);
        if ($signatureEcoleDoctorale === null) {
            throw new ExporterDataException("Aucune signature trouvée pour l'ED '$ed'.");
        }
        $exportData->signatureEcoleDoctorale = $signatureEcoleDoctorale;

        return $exportData;
    }

    private function findSignatureEcoleDoctorale(EcoleDoctorale $ed, Etablissement $etablissement): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $ed->getStructure(),
            NatureFichier::CODE_SIGNATURE_RAPPORT_ACTIVITE,
            $etablissement);

        if ($fichier === null) {
            return null;
        }

        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(true);
            return $this->fichierStorageService->getFileForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la signature de l'ED !", 0, $e);
        }
    }

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteEvent
    {
        $event = new RapportActiviteEvent($name, $target, $params);

        $this->events->triggerEvent($event);

        return $event;
    }

    public function fetchParametresCampagneDepotDates(): array
    {
        try {
            $campagneDepotDeb = $this->parametreService->getValeurForParametre(RapportActiviteParametres::CATEGORIE, $k = RapportActiviteParametres::CAMPAGNE_DEPOT_DEBUT);
            $campagneDepotFin = $this->parametreService->getValeurForParametre(RapportActiviteParametres::CATEGORIE, $k = RapportActiviteParametres::CAMPAGNE_DEPOT_FIN);
        } catch (Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'obtention du paramètre $k", null, $e);
        }

        $a = $this->anneeUnivService->courante()->getPremiereAnnee();
        $dateDebSpec = str_replace(['N+1', 'N'], [$a+1, $a], $campagneDepotDeb);
        $dateFinSpec = str_replace(['N+1', 'N'], [$a+1, $a], $campagneDepotFin);
        $dateDeb = DateTime::createFromFormat('d/m/Y H:i:s', "$dateDebSpec 00:00:00");
        $dateFin = DateTime::createFromFormat('d/m/Y H:i:s', "$dateFinSpec 23:59:59");

        if ($dateDeb > $dateFin) {
            throw new RuntimeException(sprintf(
                "Les valeurs des paramètres suivants sont invalides car on obtient une date de début postérieure à la date de fin : %s, %s (catégorie %s)",
                RapportActiviteParametres::CAMPAGNE_DEPOT_DEBUT,
                RapportActiviteParametres::CAMPAGNE_DEPOT_FIN,
                RapportActiviteParametres::CATEGORIE
            ));
        }

        return [
            RapportActiviteParametres::CAMPAGNE_DEPOT_DEBUT => $dateDeb,
            RapportActiviteParametres::CAMPAGNE_DEPOT_FIN => $dateFin,
        ];
    }
}