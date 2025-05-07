<?php

namespace Doctorant\Service;

use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\BaseService;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\Repository\DoctorantRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Individu\Entity\Db\Individu;
use RapportActivite\Rule\Creation\RapportActiviteCreationRuleAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use Soutenance\Entity\PropositionThese;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DoctorantService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use SourceServiceAwareTrait;
    use EntityManagerAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteCreationRuleAwareTrait;
    use RapportServiceAwareTrait;
    use TheseServiceAwareTrait;
    use PropositionTheseServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    /**
     * @return DoctorantRepository
     */
    public function getRepository(): DoctorantRepository
    {
        /** @var DoctorantRepository $repo */
        $repo = $this->entityManager->getRepository(Doctorant::class);

        return $repo;
    }

    /**
     * On recherche un doctorant dont le SOURCE_CODE égale '{PREFIX_ETAB}::{supannId}, où :
     * - {PREFIX_ETAB} (ex : 'INSA') : code de l'établissement/structure dont le domaine égale celui extrait de l'EPPN (ex: 'insa-rouen.fr') ;
     * - {supannId} (ex : '000020533') : supannEtuId, supannEmpId ou autre (cf. {@see \UnicaenAuthentification\Service\ShibService::extractShibUserIdValueForDomainFromShibData()}
     *   issue des données d'identité.
     *
     * @param UserWrapper $user
     * @return Doctorant|null
     */
    public function findOneByUserWrapper(UserWrapper $user): ?Doctorant
    {
        if ($individu = $user->getIndividu()) {
            return $this->getRepository()->findOneByIndividu($individu);
        }

        $id = $user->getSupannId();
        if (!$id) {
//            throw new RuntimeException("Aucun id supann disponible.");
            return null;
        }

        $domaineEtab = $user->getDomainFromEppn();
        if (!$domaineEtab) {
            return null;
        }

        $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
        if (!$etablissement) {
            return null;
        }

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($id, $etablissement);

        return $this->getRepository()->findOneBySourceCode($sourceCode);
    }

    /**
     * @param string|null $term
     * @return Doctorant[]
     */
    public function getDoctorantsByTerm(?string $term = null): array
    {
        $seach = '%' . strtolower($term) . '%';
        $qb = $this->getRepository()->createQueryBuilder('doctorant')
            ->join('doctorant.individu', 'individu')->addSelect('individu')
            ->andWhere("concat(concat(concat(concat(lower(individu.prenom1), ' '), lower(individu.nomUsuel)), ' '), doctorant.ine) like :search")
            ->setParameter('search', $seach);

        return $qb->getQuery()->getResult();
    }

    public function getDoctorantsByUser(?Utilisateur $user): ?Doctorant
    {
        if ($user === null or $user->getIndividu() === null) return null;

        $qb = $this->getEntityManager()->getRepository(Doctorant::class)->createQueryBuilder('doctorant')
            ->leftJoin('doctorant.individu', 'individu')
            ->andWhere('individu.id = :id')->setParameter('id', $user->getIndividu()->getId())
            ->andWhere('individu.histoDestruction IS NULL')
            ->andWhere('doctorant.histoDestruction IS NULL');

        /** @var Doctorant $result */
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants sont liés au mếme individu [" . $user->getIndividu()->getId() . "]");
        }
        return $result;


    }

    /**
     * Enregistre le Doctorant en base de données.
     *
     * NB : l'IdRef de l'individu lié peut avoir été modifié donc le flush() sans argument est nécessaire.
     */
    public function saveDoctorant(Doctorant $doctorant): void
    {
        try {
            $this->entityManager->persist($doctorant);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement du nouveau doctorant", null, $e);
        }
    }

    /**
     * Instancie et intialise un nouveau Doctorant, lié à l'établissement spécifié.
     */
    public function newDoctorant(Individu $individu, Etablissement $etablissement): Doctorant
    {
        try {
            $etablissement = $this->etablissementService->getRepository()->find($etablissement->getId());
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun établissement de trouvé avec cet id", null, $e);
        }

        $doctorant = new Doctorant();
        $doctorant->setIndividu($individu);
        $doctorant->setEtablissement($etablissement);
        $doctorant->setSource($this->sourceService->fetchApplicationSource());
        $doctorant->setSourceCode($this->sourceService->genereateSourceCode());
        $doctorant->setCodeApprenantInSource(uniqid());

        return $doctorant;
    }

    public function getVariablesForTableauDeBord(Doctorant $doctorant)
    {
        $theses = $this->theseService->getRepository()->findThesesByDoctorant($doctorant, [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]);

        /** @var Inscription[] $inscription */
        $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);
        $enCours = [];
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $seances = $inscription->getSession()->getSeances()->toArray();
            $seances = array_filter($seances, function ($a) {
                return $a->estNonHistorise();
            });
            $nbSeance = count($seances);
            if ($nbSeance > 0 && $inscription->getSession()->getEtat()->getCode() !== Etat::CODE_CLOTURER && $inscription->getSession()->getEtat()->getCode() !== Etat::CODE_ANNULEE) {
                if ($inscription->estNonHistorise()) $enCours[] = $inscription;
            }
        }

        $typeRapport = $this->rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
        $rapportsCSIParAnnee = $this->rapportService->findRapportsParAnneesForThese($theses[0], $typeRapport);

        $rapportsActiviteParAnnee = $this->rapportActiviteService->findRapportsParAnneesForThese($theses[0]);
        $rapportsActivite = $this->rapportActiviteService->findRapportsForThese($theses[0]);
        $this->rapportActiviteCreationRule->setRapportsExistants($rapportsActivite);
        $canCreateRapportFinContrat = $this->rapportActiviteCreationRule->canCreateRapportFinContrat();

        /** @var PropositionThese $proposition */
        $proposition = $this->propositionTheseService->findOneForThese($theses[0]);
        if ($proposition->getDate() !== null) {
            $anneeUnivProposition = $this->anneeUnivService->fromDate($proposition->getDate());
        }

        return [
            'doctorant' => $doctorant,
            'theses' => $theses,
            'enCours' => $enCours,
            'rapportsActiviteParAnnee' => $rapportsActiviteParAnnee,
            'rapportsCSIParAnnee' => $rapportsCSIParAnnee,
            'canCreateRapportFinContrat' => $canCreateRapportFinContrat,
            'anneeUnivProposition' => $anneeUnivProposition ?? null,
            'proposition' => $proposition
        ];
    }

    /**
     * Met à jour les attributs de l'instance de Doctorant spécifiée, à partir des données apprenant fournies.
     */
    public function updateDoctorantFromApprenant(Doctorant $doctorant, InscriptionAdministrativeApprenant $iaa): void
    {
        $doctorant->setCodeApprenantInSource($iaa->getCode());
        $doctorant->setIne($iaa->getINE());
    }


    /**
     * Détermine si le doctorant spécifié possède des attributs dont la valeur pourrait être mise à jour
     * à partir de ceux de l'apprenant spécifié.
     */
    public function diffDoctorantFromApprenant(
        Doctorant $doctorant,
        InscriptionAdministrativeApprenant $apprenant,
        ?array $fieldNames = null): array
    {
        $doctorantFromApprenant = new Doctorant();
        $this->updateDoctorantFromApprenant($doctorantFromApprenant, $apprenant);

        if ($fieldNames === null) {
            $fieldNames = [
                'ine',
            ];
        }

        return $this->computeEntityDiff($doctorant, $doctorantFromApprenant, $fieldNames);
    }
}