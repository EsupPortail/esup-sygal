<?php

namespace These\Service\These;

use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Financement;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\Repository\TheseRepository;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\FichierThese\MembreData;
use These\Service\FichierThese\PdcData;
use UnicaenApp\Exception\RuntimeException;

class TheseService extends BaseService
{
    use SourceServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;

    /**
     * @return TheseRepository
     */
    public function getRepository(): TheseRepository
    {
        /** @var TheseRepository $repo */
        $repo = $this->entityManager->getRepository(These::class);

        return $repo;
    }

    public function newThese() : These
    {
        $these = new These();
        $these->setSource($this->sourceService->fetchApplicationSource());
        $these->setSourceCode($this->sourceService->genereateSourceCode());

        return $these;
    }

    public function saveThese(These $these): These
    {
        /** @var Acteur[] $direction */
        $direction = $these->getActeursByRoleCode([
            Role::CODE_DIRECTEUR_THESE,
            Role::CODE_CODIRECTEUR_THESE,
        ]);

        foreach ($direction as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        /** @var Acteur[] $direction */
        $coencadrants = $these->getActeursByRoleCode([
            Role::CODE_CO_ENCADRANT,
        ]);

        foreach ($coencadrants as $acteur) {
            try {
                $this->getEntityManager()->persist($acteur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        $titreAcces = $these->getTitreAcces();
        if($titreAcces){
            try {
                $this->getEntityManager()->persist($titreAcces);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        $theseAnneesUniv = $these->getAnneesUnivInscription();
        foreach ($theseAnneesUniv as $theseAnneeUniv) {
            try {
                $this->getEntityManager()->persist($theseAnneeUniv);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !", 0, $e);
            }
        }

        $doctorant = $these->getDoctorant();
        if($doctorant){
            try {
                $this->getEntityManager()->persist($doctorant);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        $financements = $these->getFinancements();
        /** @var Financement $financement */
        foreach ($financements as $financement) {
            try {
                // afin de satisfaire la contrainte d'unicité en BDD
                if($financement->getId() === null) $financement->setSourceCode($this->sourceService->genereateSourceCode());
                $this->getEntityManager()->persist($financement);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
            }
        }

        if($these->getId() !== null){
            return $this->updateAll($these);
        }else{
            return $this->create($these);
        }
    }

    public function updateAll(These $these, $serviceEntityClass = null): These
    {
        $entityClass = get_class($these);
        $serviceEntityClass = $serviceEntityClass ?: These::class;
        if ($serviceEntityClass != $entityClass && !is_subclass_of($these, $serviceEntityClass)) {
            throw new \RuntimeException("L'entité transmise doit être de la classe $serviceEntityClass.");
        }
        try {
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $these;
    }

    public function create(These $these): These
    {
        try {
            $this->getEntityManager()->persist($these);
            $this->getEntityManager()->flush($these);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $these;
    }

    public function update(These $these): These
    {
        try {
            $this->getEntityManager()->flush($these);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en BD !",0,$e);
        }
        return $these;
    }

    /**
     * Cette fonction a pour vocation de récupérer les informations utile pour la génération de la page de couverture.
     * Si une clef est vide cela indique un problème associé à la thèse
     *
     * @param These $these
     * @return PdcData
     */
    public function fetchInformationsPageDeCouverture(These $these) : PdcData
    {
        $pdcData = new PdcData();
        $propositions = $these->getPropositions()->toArray();
        /** @var Proposition $proposition */
        $proposition = end($propositions);

        if ($these->getDateSoutenance() !== null) {
            $mois = (int) $these->getDateSoutenance()->format('m');
            $annee = (int) $these->getDateSoutenance()->format('Y');

            if ($mois > 9)  $anneeUniversitaire = $annee . "/" . ($annee + 1);
            else            $anneeUniversitaire = ($annee - 1) . "/" . $annee;
            $pdcData->setAnneeUniversitaire($anneeUniversitaire);
        }

        /** informations générales */
        $titre = trim($these->getTitre());
        $titre = str_replace("\n",' ', $titre);
        $pdcData->setTitre($titre);
        $pdcData->setSpecialite($these->getLibelleDiscipline());
        if ($these->getEtablissement()) {
            $pdcData->setEtablissement($these->getEtablissement()->getStructure()->getLibelle());
        }
        if ($these->getDoctorant()) {
            $pdcData->setDoctorant(mb_strtoupper($these->getDoctorant()->getIndividu()->getNomComplet()));
        }
        if ($these->getDateSoutenance()) $pdcData->setDate($these->getDateSoutenance()->format("d/m/Y"));

        /** cotutelle */
        $pdcData->setCotutuelle(false);
        if ($these->getLibelleEtabCotutelle() !== null && $these->getLibelleEtabCotutelle() !== "") {
            $pdcData->setCotutuelle(true);
            $pdcData->setCotutuelleLibelle($these->getLibelleEtabCotutelle());
            if ($these->getLibellePaysCotutelle()) $pdcData->setCotutuellePays($these->getLibellePaysCotutelle());
        }

        /** Huis Clos */
        if ($proposition AND $proposition->isHuitClos()) {
            $pdcData->setHuisClos(true);
        } else {
            $pdcData->setHuisClos(false);
        }

        /** confidentialité */
        $pdcData->setDateFinConfidentialite($these->getDateFinConfidentialite());
        /** Jury de thèses */
        $acteurs = $these->getActeurs()->toArray();

        $rapporteurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estRapporteur();
        });
        $pdcData->setRapporteurs($rapporteurs);
        $directeurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estDirecteur();
        });
        $pdcData->setDirecteurs($directeurs);
        $codirecteurs = array_filter($acteurs, function (Acteur $a) {
            return $a->estCodirecteur();
        });
        $pdcData->setCodirecteurs($codirecteurs);
        $coencadrants = array_filter($acteurs, function (Acteur $a) {
            return $a->estCoEncadrant();
        });
        $pdcData->setCoencadrants($coencadrants);
        $president = array_filter($acteurs, function (Acteur $a) {
            return $a->estPresidentJury();
        });

        $rapporteurs = array_diff($rapporteurs, $president);
        $membres = array_diff($acteurs, $rapporteurs, $directeurs, $codirecteurs, $president);
        $pdcData->setMembres($membres);

        $jury = array_filter($acteurs, function (Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_MEMBRE_JURY;
        });
        $pdcData->setJury($jury);

        /** associée */
        $pdcData->setAssocie(false);
        /** @var Acteur $directeur */
        foreach (array_merge($directeurs, $codirecteurs) as $directeur) {
            if ($directeur->getEtablissement()) {
                if ($directeur->getEtablissement()->estAssocie()) {
                    $pdcData->setAssocie(true);
                    try {
                        $pdcData->setLogoAssocie($this->fichierStorageService->getFileForLogoStructure($directeur->getEtablissement()->getStructure()));
                    } catch (StorageAdapterException $e) {
                        $pdcData->setLogoAssocie(null);
                    }
                    $pdcData->setLibelleAssocie($directeur->getEtablissement()->getStructure()->getLibelle());
                }
            }
        }

        $acteursEnCouverture = array_merge($rapporteurs, $directeurs, $codirecteurs, $president, $membres);
        usort($acteursEnCouverture, Acteur::getComparisonFunction());
        $acteursEnCouverture = array_unique($acteursEnCouverture);

        /** @var Acteur $acteur */
        foreach ($acteursEnCouverture as $acteur) {
            $individu = $acteur->getIndividu();

            $acteursLies = array_filter($these->getActeurs()->toArray(), function (Acteur $a) use ($individu) { return $a->getIndividu() === $individu;});

            $acteurData = new MembreData();
            $acteurData->setDenomination(mb_strtoupper($acteur->getIndividu()->getNomCompletFormatter()->avecCivilite()->f()));

            $estMembre = !empty(array_filter($jury, function (Acteur $a) use ($acteur) {return $a->getIndividu() === $acteur->getIndividu();}));

            /** GESTION DES RÔLES SPÉCIAUX ****************************************************************************/
            if (!$acteur->estPresidentJury()) {
                $acteurData->setRole($acteur->getRole()->getLibelle());

                //patch rapporteur non membre ...
                if ($acteur->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY && !$estMembre) {
                    $acteurData->setRole("Rapporteur non membre du jury");
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
                    $membre = $this->getMembreService()->getMembreByActeur($acteurLie);
                    if ($membre) {
                        $acteurData->setQualite($membre->getQualite()->getLibelle());
                        break;
                    }
                }
            }

            /** GESTION DES ETABLISSEMENTS ****************************************************************************/
            if ($etab = ($acteur->getEtablissementForce() ?: $acteur->getEtablissement())) {
                $acteurData->setEtablissement((string) $etab);
            } else {
                foreach ($acteursLies as $acteurLie) {
                    $membre = $this->getMembreService()->getMembreByActeur($acteurLie);
                    if ($membre) {
                        $acteurData->setEtablissement($membre->getEtablissement());
                        break;
                    }
                }
            }

            if ($estMembre) $pdcData->addActeurEnCouverture($acteurData);
        }

        /** Directeurs de thèses */
        $listing = [];
        foreach ($directeurs as $directeur) {
            $current = mb_strtoupper($directeur->getIndividu()->getNomComplet());
            $structure = $directeur->getUniteRecherche() ?: $these->getUniteRecherche();
            if ($structure !== null) $structure = $structure->getStructure()->getLibelle();
            $listing[] = ['individu' => $current, 'structure' => $structure];
        }
        foreach ($codirecteurs as $directeur) {
            $current = mb_strtoupper($directeur->getIndividu()->getNomComplet());
            $structure = $directeur->getUniteRecherche() ?: $directeur->getEtablissementForce() ?: $directeur->getEtablissement();
            if ($structure !== null) $structure = $structure->getStructure()->getLibelle();
            $listing[] = ['individu' => $current, 'structure' => $structure];
        }
        $pdcData->setListingDirection($listing);
        if ($these->getUniteRecherche()) $pdcData->setUniteRecherche($these->getUniteRecherche()->getStructure()->getLibelle());
        if ($these->getEcoleDoctorale()) $pdcData->setEcoleDoctorale($these->getEcoleDoctorale()->getStructure()->getLibelle());

        // chemins vers les logos
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $pdcData->setLogoCOMUE($this->fichierStorageService->getFileForLogoStructure($comue->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoCOMUE(null);
            }
        }
        try {
            $pdcData->setLogoEtablissement($this->fichierStorageService->getFileForLogoStructure($these->getEtablissement()->getStructure()));
        } catch (StorageAdapterException $e) {
            $pdcData->setLogoEtablissement(null);
        }
        if ($these->getEcoleDoctorale() !== null) {
            try {
                $pdcData->setLogoEcoleDoctorale($this->fichierStorageService->getFileForLogoStructure($these->getEcoleDoctorale()->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoEcoleDoctorale(null);
            }
        }
        if ($these->getUniteRecherche() !== null) {
            try {
                $pdcData->setLogoUniteRecherche($this->fichierStorageService->getFileForLogoStructure($these->getUniteRecherche()->getStructure()));
            } catch (StorageAdapterException $e) {
                $pdcData->setLogoUniteRecherche(null);
            }
        }

        return $pdcData;
    }


    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return These
     */
    public function getRequestedThese(AbstractActionController $controller, string $param = 'these')
    {
        $id = $controller->params()->fromRoute($param);

        /** @var These $these */
        $these = $this->getRepository()->find($id);
        return $these;
    }

    /** PREDICATS *****************************************************************************************************/

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isDoctorant(These $these, Individu $individu): bool
    {
        return ($these->getDoctorant()->getIndividu() === $individu);
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isDirecteur(These $these, Individu $individu): bool
    {
        $directeurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, 'D');
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu() === $individu) return true;
        }
        return false;
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return bool
     */
    public function isCoDirecteur(These $these, Individu $individu): bool
    {
        $directeurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, 'K');
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu() === $individu) return true;
        }
        return false;
    }


    public function assertAppartenanceThese(These $these, UserContextService $userContextService): void
    {
        $role = $userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $doctorant = $userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        } elseif ($roleEcoleDoctorale = $userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        } elseif ($roleUniteRech = $userContextService->getSelectedRoleUniteRecherche()) {
            $this->assertTrue(
                $these->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'UR " . $roleUniteRech->getStructure()->getCode()
            );
        } elseif ($userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        } elseif ($userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas codirigée par " . $individuUtilisateur
            );
        } elseif($role->getCode() === Role::CODE_BDD) {
            $structure = $role->getStructure();
            $this->assertTrue(
                $these->getEtablissement()->getStructure() === $structure,
                "La thèse n'appartient pas à la structure  " . $structure
            );
        }
    }
}