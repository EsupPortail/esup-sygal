<?php

namespace HDR\Service;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Exception;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use HDR\Entity\Db\HDR;
use HDR\Entity\Db\Repository\HDRRepository;
use HDR\Service\FichierHDR\MembreData;
use HDR\Service\FichierHDR\PdcData;
use Individu\Entity\Db\Individu;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Service\ValidationHDR\ValidationHDRServiceAwareTrait;

class HDRService extends BaseService
{
    use EntityManagerAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use VariableServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use MembreServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use SourceServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;

    public function getRepository(): HDRRepository
    {
        /** @var HDRRepository $qb */
        $qb = $this->getEntityManager()->getRepository(HDR::class);

        return $qb;
    }

    public function newHDR() : HDR
    {
        $hdr = new HDR();
        $hdr->setSource($this->sourceService->fetchApplicationSource());
        $hdr->setSourceCode($this->sourceService->genereateSourceCode());

        return $hdr;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(HDR $hdr) : HDR
    {
        try {
            $this->getEntityManager()->persist($hdr);
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la HDR !");
        }
        return $hdr;
    }

    public function update(HDR $hdr) : HDR
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $hdr->setHistoModificateur($user);
        $hdr->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la HDR !");
        }
        return $hdr;
    }

    /**
     * Cette fonction a pour vocation de récupérer les informations utile pour la génération de la page de couverture.
     * Si une clef est vide cela indique un problème associé à la HDR
     *
     * @param HDR $hdr
     * @return PdcData
     */
    public function fetchInformationsPageDeCouverture(HDR $hdr) : PdcData
    {
        $pdcData = new PdcData();
        $propositions = $hdr->getPropositionsHDR()->toArray();
        /** @var Proposition $proposition */
        $proposition = end($propositions);

        if ($proposition->getDate() !== null) {
            $anneeUniv = $this->anneeUnivService->fromDate($proposition->getDate());
            $pdcData->setAnneeUniversitaire($anneeUniv->getAnneeUnivToString());
        }

        /** informations générales */
        $pdcData->setSpecialite((string)$hdr->getVersionDiplome());
        if ($hdr->getEtablissement()) {
            $pdcData->setEtablissement($hdr->getEtablissement()->getStructure()->getLibelle());
        }
        if ($hdr->getCandidat()) {
            $pdcData->setCandidat(mb_strtoupper($hdr->getCandidat()->getIndividu()->getNomComplet()));
        }
        if ($proposition->getDate()) $pdcData->setDate($proposition->getDate()->format('d/m/Y à H:i'));
        

        /** Huis Clos */
        if ($proposition AND $proposition->isHuitClos()) {
            $pdcData->setHuisClos(true);
        } else {
            $pdcData->setHuisClos(false);
        }

        /** confidentialité */
        $pdcData->setDateFinConfidentialite($hdr->getDateFinConfidentialite());
        /** Jury de thèses */
        $acteurs = $hdr->getActeurs()->toArray();

        $rapporteurs = array_filter($acteurs, function (ActeurHDR $a) {
            return $a->estRapporteur();
        });
        $pdcData->setRapporteurs($rapporteurs);
        $garants = array_values(array_filter($acteurs, function (ActeurHDR $a) {
            return $a->estGarant();
        }));
        $pdcData->setGarants($garants);
        $president = array_filter($acteurs, function (ActeurHDR $a) {
            return $a->estPresidentJury();
        });

        $rapporteurs = array_diff($rapporteurs, $president);
        $membres = array_diff($acteurs, $rapporteurs, $garants, $president);
        $pdcData->setMembres($membres);

        $jury = array_filter($acteurs, function (ActeurHDR $a) {
            return $a->getRole()->getCode() === Role::CODE_MEMBRE_JURY;
        });
        $pdcData->setJury($jury);

        /** associée */
        $pdcData->setAssocie(false);
        /** @var ActeurHDR $garant */
        $garant = $garants[0] ?? null;
        if ($garant?->getEtablissement()) {
            if ($garant->getEtablissement()->estAssocie()) {
                $pdcData->setAssocie(true);
                try {
                    $pdcData->setLogoAssocie($this->fichierStorageService->getFileForLogoStructure($garant->getEtablissement()->getStructure()));
                } catch (StorageAdapterException $e) {
                    $pdcData->setLogoAssocie(null);
                }
                $pdcData->setLibelleAssocie($garant->getEtablissement()->getStructure()->getLibelle());
            }
        }

        $acteursEnCouverture = array_merge($rapporteurs, $garants, $president, $membres);
        usort($acteursEnCouverture, ActeurHDR::getComparisonFunction());
        $acteursEnCouverture = array_unique($acteursEnCouverture);

        /** @var ActeurHDR $acteur */
        foreach ($acteursEnCouverture as $acteur) {
            $individu = $acteur->getIndividu();

            $acteursLies = array_filter($hdr->getActeurs()->toArray(), function (ActeurHDR $a) use ($individu) { return $a->getIndividu() === $individu;});

            $acteurData = new MembreData();
            $acteurData->setDenomination(mb_strtoupper($acteur->getIndividu()->getNomCompletFormatter()->avecCivilite()->f()));

            $estMembre = !empty(array_filter($jury, function (ActeurHDR $a) use ($acteur) {return $a->getIndividu() === $acteur->getIndividu();}));

            /** GESTION DES RÔLES SPÉCIAUX ****************************************************************************/
            if (!$acteur->estPresidentJury()) {
                $acteurData->setRole($acteur->getRole()->getLibelle());

                //patch rapporteur non membre ...
                if ($acteur->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY && !$estMembre) {
                    $acteurData->setRole("Rapporteur non membre du jury");
                }

                if ($acteur->getRole()->getCode() === Role::CODE_HDR_GARANT) {
                    $acteurData->setRole("Examinateur");
                }
            } else {
                $acteurData->setRole("Président du jury");
            }

            /** GESTION DES QUALITES **********************************************************************************/
            $qualite = $acteur->getLibelleQualite();
            if ($qualite && trim($qualite) !== '') {
                $acteurData->setQualite($qualite);
            } else {
                foreach ($acteursLies as $acteurLie) {
                    $membre = $acteurLie->getMembre();
                    if ($membre) {
                        $acteurData->setQualite($membre->getQualite()->getLibelle());
                        break;
                    }
                }
            }

            /** GESTION DES ETABLISSEMENTS ****************************************************************************/
            if ($etab = $acteur->getEtablissement()) {
                $acteurData->setEtablissement((string) $etab);
            } else {
                foreach ($acteursLies as $acteurLie) {
                    $membre = $acteurLie->getMembre();
                    if ($membre) {
                        $acteurData->setEtablissement($membre->getEtablissement());
                        break;
                    }
                }
            }

            if ($estMembre) $pdcData->addActeurEnCouverture($acteurData);
        }

        /** Garant de HDR*/
        $listing = [];
        $structure = $garant?->getUniteRecherche() ?: $hdr->getUniteRecherche();
        if ($structure !== null) $structure = $structure->getStructure()->getLibelle();
        $listing[] = ['individu' => mb_strtoupper($garant?->getIndividu()->getNomComplet()), 'structure' => $structure];
        $pdcData->setListingDirection($listing);

        if ($hdr->getUniteRecherche()) $pdcData->setUniteRecherche($hdr->getUniteRecherche()->getStructure()->getLibelle());

        // chemins vers les logos
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $pdcData->setLogoCOMUE($this->fichierStorageService->getFileForLogoStructure($comue->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoCOMUE(null);
            }
        }
        try {
            $pdcData->setLogoEtablissement($this->fichierStorageService->getFileForLogoStructure($hdr->getEtablissement()->getStructure()));
        } catch (StorageAdapterException $e) {
            $pdcData->setLogoEtablissement(null);
        }
        if ($hdr->getUniteRecherche() !== null) {
            try {
                $pdcData->setLogoUniteRecherche($this->fichierStorageService->getFileForLogoStructure($hdr->getUniteRecherche()->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoUniteRecherche(null);
            }
        }

        return $pdcData;
    }

    public function saveHDR(HDR $hdr): HDR
    {
        /** @var ActeurHDR[] $membres */
        $membres = $hdr->getActeursByRoleCode([
            Role::CODE_MEMBRE_JURY,
        ]);

        foreach ($membres as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        /** @var ActeurHDR[] $direction */
        $direction = $hdr->getActeursByRoleCode([
            Role::CODE_HDR_GARANT
        ]);

        foreach ($direction as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        $candidat = $hdr->getCandidat();
        if($candidat){
            try {
                $this->getEntityManager()->persist($candidat);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        if($hdr->getId() !== null){
            return $this->updateAll($hdr);
        }else{
            return $this->create($hdr);
        }
    }

    public function updateAll(HDR $hdr, $serviceEntityClass = null): HDR
    {
        $entityClass = get_class($hdr);
        $serviceEntityClass = $serviceEntityClass ?: HDR::class;
        if ($serviceEntityClass != $entityClass && !is_subclass_of($hdr, $serviceEntityClass)) {
            throw new \RuntimeException("L'entité transmise doit être de la classe $serviceEntityClass.");
        }
        try {
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $hdr;
    }

    public function historise(HDR $hdr) : HDR
    {
        $hdr->historiser();

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $hdr;
    }

    public function restore(HDR $hdr) : HDR
    {
        $hdr->dehistoriser();

        try {
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $hdr;
    }

    public function delete(HDR $hdr) : HDR
    {
        try {
            $this->getEntityManager()->remove($hdr);
            $this->getEntityManager()->flush($hdr);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la HDR !");
        }
        return $hdr;
    }

    /** PREDICATS *****************************************************************************************************/

    /**
     * @param HDR $hdr
     * @param Individu $individu
     * @return bool
     */
    public function isCandidat(HDR $hdr, Individu $individu): bool
    {
        return ($hdr->getCandidat()->getIndividu() === $individu);
    }

    /**
     * @param HDR $hdr
     * @param Individu $individu
     * @return bool
     */
    public function isGarant(HDR $hdr, Individu $individu): bool
    {
        $garants = $this->getActeurHDRService()->getRepository()->findActeursByHDRAndRole($hdr, 'D');
        foreach ($garants as $garant) {
            if ($garant->getIndividu() === $individu) return true;
        }
        return false;
    }
}