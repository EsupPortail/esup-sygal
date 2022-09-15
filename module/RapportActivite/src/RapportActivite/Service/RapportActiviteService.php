<?php

namespace RapportActivite\Service;

use Application\Command\ShellCommandRunnerTrait;
use Application\Entity\AnneeUniv;
use Fichier\Entity\Db\NatureFichier;
use These\Entity\Db\These;
use Application\Entity\Db\TypeRapport;
use Application\Service\BaseService;
use RapportActivite\Service\Fichier\Exporter\PageValidationExportDataException;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Closure;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Laminas\EventManager\EventManagerAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Formatter\RapportActiviteNomFichierFormatter;
use RapportActivite\Notification\RapportActiviteSupprimeNotification;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Fichier\Exporter\PageValidationExportData;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnexpectedValueException;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteService extends BaseService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;
    use RoleServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    use PageValidationPdfExporterTrait;
    use ShellCommandRunnerTrait;

    use EventManagerAwareTrait;

    const RAPPORT_ACTIVITE__AJOUTE__EVENT = 'RAPPORT_ACTIVITE__AJOUTE__EVENT';
    const RAPPORT_ACTIVITE__SUPPRIME__EVENT = 'RAPPORT_ACTIVITE__SUPPRIME__EVENT';

    /**
     * @param int $id
     * @return RapportActivite|null
     */
    public function findRapportById(int $id): ?RapportActivite
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('t, f')
            ->join('ra.these', 't')
            ->join('ra.fichier', 'f')
            ->where('ra = :id')->setParameter('id', $id);

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
        // ATTENTION ! Les relations suivantes doivent être sélectionnées lors du fetch des rapports :
        // 'rapportAvis->avis->avisType'.

        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('tr, t, f, d, i, rav, raa, a, at')
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr.code = :code')->setParameter('code', TypeRapport::RAPPORT_ACTIVITE)
            ->join('ra.these', 't', Join::WITH, 't =:these')->setParameter('these', $these)
            ->join('ra.fichier', 'f')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'i')
            ->leftJoin('ra.rapportValidations', 'rav')
            ->leftJoin('ra.rapportAvis', 'raa')
            ->leftJoin('raa.avis', 'a')
            ->leftJoin('a.avisType', 'at')
            ->andWhereNotHistorise()
            ->addOrderBy('ra.anneeUniv')
            ->addOrderBy('ra.estFinal')
            ->addOrderBy('at.ordre');

        return $qb->getQuery()->getResult();
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
        $rapportActivite->setTypeRapport($this->findTypeRapport());
        $rapportActivite->setThese($these);

        return $rapportActivite;
    }

    /**
     * Enregistre le rapport spécifié, après avoir créé le fichier à partir des données d'upload fournies.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @param array $uploadData Données résultant de l'upload de fichiers
     * @return \RapportActivite\Event\RapportActiviteEvent Rapport annuel créé
     */
    public function saveRapport(RapportActivite $rapportActivite, array $uploadData): RapportActiviteEvent
    {
        $this->fichierService->setNomFichierFormatter(new RapportActiviteNomFichierFormatter($rapportActivite));
        $fichiers = $this->fichierService->createFichiersFromUpload($uploadData, NatureFichier::CODE_RAPPORT_ACTIVITE);

        $this->entityManager->beginTransaction();
        try {
            $this->fichierService->saveFichiers($fichiers);

            $fichier = array_pop($fichiers); // il n'y a en fait qu'un seul fichier
            $rapportActivite->setFichier($fichier);

            $this->entityManager->persist($rapportActivite);
            $this->entityManager->flush($rapportActivite);
            $this->entityManager->commit();

            $event = $this->triggerEvent(
                self::RAPPORT_ACTIVITE__AJOUTE__EVENT,
                $rapportActivite,
                []
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des rapports annuels, rollback!", 0, $e);
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
            $this->fichierService->supprimerFichiers([$fichier]);

            $this->entityManager->remove($rapportActivite);
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
     * @return TypeRapport
     */
    public function findTypeRapport(): TypeRapport
    {
        $qb = $this->getEntityManager()->getRepository(TypeRapport::class);

        /** @var TypeRapport $type */
        $type = $qb->findOneBy(['code' => TypeRapport::RAPPORT_ACTIVITE]);

        return $type;
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
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr.code = :code')->setParameter('code', TypeRapport::RAPPORT_ACTIVITE)
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
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr.code = :code')->setParameter('code', TypeRapport::RAPPORT_ACTIVITE)
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
     * @param \Application\Entity\AnneeUniv $anneeUniv
     * @return \Closure
     */
    public function getFilterRapportsByAnneeUniv(AnneeUniv $anneeUniv): Closure
    {
        return function (RapportActivite $rapport) use ($anneeUniv) {
            return $rapport->getAnneeUniv() === $anneeUniv;
        };
    }

    /**
     * @throws \RapportActivite\Service\Fichier\Exporter\PageValidationExportDataException Une structure n'a aucun logo
     */
    public function createPageValidationDataForRapport(RapportActivite $rapport): PageValidationExportData
    {
        $exportData = new PageValidationExportData();

        $these = $rapport->getThese();
        $etablissement = $these->getEtablissement();
        $ed = $these->getEcoleDoctorale();
        $ur = $these->getUniteRecherche();

        if ($ed === null) {
            throw new PageValidationExportDataException("La thèse n'est rattachée à aucune école doctorale");
        }
        if ($ur === null) {
            throw new PageValidationExportDataException("La thèse n'est rattachée à aucune unité de recherche");
        }

        // généralités
        $exportData->titre = $these->getTitre();
        $exportData->specialite = $these->getLibelleDiscipline();

        // doctorant
        if ($these->getDoctorant()) {
            $exportData->doctorant = strtoupper($these->getDoctorant()->getIndividu()->getNomComplet(true, true, false, true, true, false));
        }

        // structures
        $exportData->etablissement = $etablissement->getLibelle();
        $exportData->ecoleDoctorale = $ed->getStructure()->getLibelle();
        $exportData->uniteRecherche = $ur->getStructure()->getLibelle();

        $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(true);

        // logo COMUE
        $exportData->useCOMUE = false;
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $exportData->useCOMUE = true;
            if (!$comue->getCheminLogo()) {
                throw new PageValidationExportDataException("La COMUE '{$comue}' n'a aucun logo !");
            }
            try {
                $exportData->logoCOMUE = $this->fichierStorageService->getFileForLogoStructure($comue);
            } catch (StorageAdapterException $e) {
                throw new PageValidationExportDataException(
                    "Accès impossible au logo de la COMUE '{$comue}' : " . $e->getMessage());
            }
        }

        // logo etablissement
        if (!$etablissement->getCheminLogo()) {
            throw new PageValidationExportDataException("L'établissement '{$etablissement}' n'a aucun logo !");
        }
        try {
            $exportData->logoEtablissement = $this->fichierStorageService->getFileForLogoStructure($etablissement);
        } catch (StorageAdapterException $e) {
            throw new PageValidationExportDataException(
                "Accès impossible au logo de l'établissement '{$etablissement}' : " . $e->getMessage());
        }

        // logo ED
        if (!$ed->getCheminLogo()) {
            throw new PageValidationExportDataException("L'ED '{$ed}' n'a aucun logo !");
        }
        try {
            $exportData->logoEcoleDoctorale = $this->fichierStorageService->getFileForLogoStructure($ed);
        } catch (StorageAdapterException $e) {
            throw new PageValidationExportDataException(
                "Accès impossible au logo de l'ED '{$ed}' : " . $e->getMessage());
        }

        // logo UR
        if (!$ur->getCheminLogo()) {
            throw new PageValidationExportDataException("L'UR '{$ur}' n'a aucun logo !");
        }
        try {
            $exportData->logoUniteRecherche = $this->fichierStorageService->getFileForLogoStructure($ur);
        } catch (StorageAdapterException $e) {
            throw new PageValidationExportDataException(
                "Accès impossible au logo de l'UR '{$ur}' : " . $e->getMessage());
        }

        // avis
        $exportData->mostRecentAvis = $this->rapportActiviteAvisService->findMostRecentRapportAvisForRapport($rapport);

        // validation
        $exportData->validation = $rapport->getRapportValidation();

        // signature ED
        $signatureEcoleDoctorale = $this->findSignatureEcoleDoctorale($ed, $etablissement);
        if ($signatureEcoleDoctorale === null) {
            throw new PageValidationExportDataException("Aucune signature trouvée pour l'ED '$ed'.");
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

    public function newRapportActiviteSupprimeNotification(RapportActivite $rapportActivite): RapportActiviteSupprimeNotification
    {
        $doctorant = $rapportActivite->getThese()->getDoctorant();

        $notif = new RapportActiviteSupprimeNotification();
        $notif->setRapportActivite($rapportActivite);
        $notif->setSubject("Rapport d'activité supprimé");
        $notif->setTo([$doctorant->getEmail() => $doctorant->getIndividu()->getNomComplet()]);

        return $notif;
    }
}