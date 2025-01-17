<?php

namespace Soutenance\Service\Proposition;

use Application\Constants;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Entity\Db\Variable;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Horodatage\Service\Horodatage\HorodatageServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Laminas\Cache\Exception\LogicException;
use Laminas\Mvc\Controller\AbstractActionController;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Parametre\SoutenanceParametres;
use Soutenance\Provider\Validation\TypeValidation as TypeValidationSoutenance;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class PropositionService extends BaseService
{
    use EntityManagerAwareTrait;
    use ActeurServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use VariableServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use MembreServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $qb */
        $qb = $this->getEntityManager()->getRepository(Proposition::class);

        return $qb;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    public function create(These $these) : Proposition
    {
        $proposition = new Proposition($these);
        $proposition->setEtat($this->findPropositionEtatByCode(Etat::EN_COURS));

        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $proposition->setHistoCreateur($user);
        $proposition->setHistoCreation($date);
        $proposition->setHistoModificateur($user);
        $proposition->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($proposition);
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la proposition de thèse !");
        }
        return $proposition;
    }

    public function update(Proposition $proposition) : Proposition
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $proposition->setHistoModificateur($user);
        $proposition->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
        return $proposition;
    }

    public function historise(Proposition $proposition) : Proposition
    {
        $proposition->historiser();

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $proposition;
    }

    public function restore(Proposition $proposition) : Proposition
    {
        $proposition->dehistoriser();

        try {
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $proposition;
    }

    public function delete(Proposition $proposition) : Proposition
    {
        try {
            $this->getEntityManager()->remove($proposition);
            $this->getEntityManager()->flush($proposition);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
        return $proposition;
    }

    /** REQUETES ******************************************************************************************************/

    public function createQueryBuilder(): DefaultQueryBuilder
    {
        return $this->getRepository()->createQueryBuilder("proposition")
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('unite')->leftJoin('these.uniteRecherche', 'unite')
            ->addSelect('structure_ur')->leftJoin('unite.structure', 'structure_ur')
            ->addSelect('ecole')->leftJoin('these.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->addSelect('etablissement')->leftJoin('these.etablissement', 'etablissement')
            ->addSelect('structure_etab')->leftJoin('etablissement.structure', 'structure_etab')
            ->addSelect('membre')->leftJoin('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur')
            ->addSelect('amembre')->leftJoin('acteur.membre', 'amembre')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('proposition.histoDestruction is null')
            //->addSelect('validation')->leftJoin('proposition.validations', 'validation')
        ;
    }

    public function find(?int $id): ?Proposition
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("proposition.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [".$id."] ont été trouvées !");
        }

        return $result;
    }

    public function getRequestedProposition(AbstractActionController $controller, string $param = 'proposition') : ?Proposition
    {
        $id = $controller->params()->fromRoute($param);

        return $this->find($id);
    }

    public function findOneForThese(These $these): ?Proposition
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("proposition.these = :these")
            ->setParameter("these", $these)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la thèse [".$these->getId()."] ont été trouvées !");
        }

        return $result;
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteurs(Proposition $proposition): array
    {
        $rapporteurs = [];
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if($membre->estRapporteur()) $rapporteurs[] = $membre;
        }

        return $rapporteurs;
    }

    /**
     * Fonction annulant toutes les validations associés à la proposition de soutenances
     *
     * @param Proposition $proposition
     */
    public function annulerValidationsForProposition(Proposition $proposition)
    {
        $these = $proposition->getThese();
        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validation);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationED = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
        if ($validationED) {
            $this->getValidationService()->historise($validationED);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationED);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationUR = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
        if ($validationUR) {
            $this->getValidationService()->historise($validationUR);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationUR);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        $validationBDD = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
        if ($validationBDD) {
            $this->getValidationService()->historise($validationBDD);
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationDevalidationProposition($these, $validationBDD);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
    }

    /**
     * @param Proposition|null $proposition
     * @return array
     */
    public function computeIndicateurForProposition(?Proposition $proposition): array
    {
        if ($proposition === null) return [];

        $nbMembre       = 0;
        $nbFemme        = 0;
        $nbHomme        = 0;
        $nbRangA        = 0;
        $nbExterieur    = 0;
        $nbEmerites     = 0;
        $nbRapporteur   = 0;

        $membre_min     =  $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::NB_MIN_MEMBRE_JURY);
        $membre_max     =  $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::NB_MAX_MEMBRE_JURY);
        $rapporteur_min =  $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::NB_MIN_RAPPORTEUR);
        $rangA_min      =  ((float) $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::RATIO_MIN_RANG_A));
        $exterieur_min  =  ((float) $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::RATIO_MIN_EXTERIEUR));
        $emerites_max   =  (float) $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::RATIO_MAX_EMERITES);
        $parite_min     =  ((float) $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::EQUILIBRE_FEMME_HOMME));

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            $nbMembre++;
            if ($membre->getGenre() === "F") $nbFemme++; else $nbHomme++;
            if ($membre->getRang() === "A") $nbRangA++;
            if ($membre->isExterieur()) $nbExterieur++;
            if ($membre->getQualite()->isEmeritat()) $nbEmerites++;
            if ($membre->estRapporteur()) $nbRapporteur++;
        }

        $indicateurs = [];

        /** Bad rapporteur */
        $nbRapporteursBad = 0;
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->estRapporteur() AND $membre->getQualite()->isRangB() AND $membre->getQualite()->getHdr() !== 'O') {
                $nbRapporteursBad++;
            }
        }
        if ($nbRapporteursBad > 0) {
            $indicateurs["bad-rapporteur"]["valide"] = false;
            $indicateurs["bad-rapporteur"]["alerte"] = "Des rapporteurs de rang B ne sont pas titulaires d'une HDR";
        } else {
            $indicateurs["bad-rapporteur"]["valide"] = true;
        }
        $indicateurs["bad-rapporteur"]["Nombre"] = $nbRapporteursBad;

        /**  Il faut essayer de maintenir la parité Homme/Femme*/
        $ratioFemme = ($nbMembre)?$nbFemme / $nbMembre:0;
        $ratioHomme = ($nbMembre)?(1 - $ratioFemme):0;
        $indicateurs["parité"]      = ["Femme" => $ratioFemme, "Homme" => $ratioHomme];
        if (min($ratioFemme,$ratioHomme) < $parite_min) {
            $indicateurs["parité"]["valide"]    = false;
            $indicateurs["parité"]["alerte"] = "La parité n'est pas respectée";
        } else {
            $indicateurs["parité"]["valide"]    = true;
        }

        /** entre 4 et 8 membres */
        $indicateurs["membre"]      = ["Nombre" => $nbMembre, "Ratio" => ($nbMembre)?$nbMembre/10:0];

        if ($nbMembre < $membre_min OR $nbMembre > $membre_max) {
            $indicateurs["membre"]["valide"]    = false;
            $indicateurs["membre"]["alerte"] = "Le jury doit être composé de $membre_min à $membre_max personnes";
        } else {
            $indicateurs["membre"]["valide"]    = true;
        }

        /** Au moins deux rapporteurs */
        $indicateurs["rapporteur"]      = ["Nombre" => $nbRapporteur, "Ratio" => ($nbMembre)?$nbRapporteur/$nbMembre:0];

        if ($nbRapporteur < $rapporteur_min) {
            $indicateurs["rapporteur"]["valide"]    = false;
            $indicateurs["rapporteur"]["alerte"] = "Le nombre minimum de rapporteurs attendu est de $rapporteur_min";
        } else {
            $indicateurs["rapporteur"]["valide"]    = true;
        }

        /** Au moins la motié du jury de rang A */
        $ratioRangA = ($nbMembre)?($nbRangA / $nbMembre):0;
        $indicateurs["rang A"]      = ["Nombre" => $nbRangA, "Ratio" => $ratioRangA];
        if ($ratioRangA < $rangA_min || !$nbMembre)  {
            $indicateurs["rang A"]["valide"]    = false;
            $indicateurs["rang A"]["alerte"] = "Le nombre de membres de rang A doit représenter au minimum " . ($ratioRangA*100) . '%';
        } else {
            $indicateurs["rang A"]["valide"]    = true;
        }

        /** Au moins la motié du jury exterieur*/
        $ratioExterieur = ($nbMembre)?($nbExterieur / $nbMembre):0;
        $indicateurs["exterieur"]      = ["Nombre" => $nbExterieur, "Ratio" => $ratioExterieur];
        if ($ratioExterieur < $exterieur_min || !$nbMembre)  {
            $indicateurs["exterieur"]["valide"]    = false;
            $indicateurs["exterieur"]["alerte"] = "Le nombre de membres extérieurs doit représenter au minimum " . ($ratioRangA*100) . '%';
        } else {
            $indicateurs["exterieur"]["valide"]    = true;
        }

        /** ratio minimum d'émérites */
        $ratioEmerites = $nbMembre ? ($nbEmerites / $nbMembre) : 0;
        $indicateurs["emerites"] = ["Nombre" => $nbEmerites, "Ratio" => $ratioEmerites];
        if ($ratioEmerites > $emerites_max) {
            $indicateurs["emerites"]["valide"] = false;
            $indicateurs["emerites"]["alerte"] = "Le nombre d'émérites ne doit pas dépasser " . ($emerites_max * 100.0) . '%';
        } else {
            $indicateurs["emerites"]["valide"] = true;
        }

        $valide = $indicateurs["parité"]["valide"] && $indicateurs["membre"]["valide"] && $indicateurs["rapporteur"]["valide"]
            && $indicateurs["rang A"]["valide"] && $indicateurs["exterieur"]["valide"] && $indicateurs["bad-rapporteur"]["valide"]
            && $indicateurs["emerites"]["valide"];

        $indicateurs["valide"] = $valide;

        return $indicateurs;
    }

    /**
     * @param Proposition|null $proposition
     * @param array $indicateurs
     * @return boolean
     */
    public function isJuryPropositionOk(?Proposition $proposition, array $indicateurs = []): bool
    {
        if ($proposition === null) return false;
        if ($indicateurs === []) $indicateurs = $this->computeIndicateurForProposition($proposition);
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            $email = $membre->getEmail();
            $res = ($membre->getEmail() === null);
            if ($membre->getEmail() === null) {
                return false;
            }
        }
        if (!$indicateurs["valide"]) return false;
        return true;
    }

    /**
     * @param Proposition $proposition
     * @param array $indicateurs
     * @return boolean
     */
    public function isPropositionOk(Proposition $proposition, array $indicateurs = []) : bool
    {
        if ($indicateurs === []) $indicateurs = $this->computeIndicateurForProposition($proposition);
        if (!$indicateurs["valide"]) return false;
        if(! $proposition->getDate() || ! $proposition->getLieu()) return false;
        return true;
    }

    /**
     * @param These $these
     * @return \Application\Entity\Db\Validation[]
     */
    public function findValidationSoutenanceForThese(These $these): array
    {
        $validations = [];

        /** Recuperation de la validation du directeur de thèse */
        $doctorants = [ $these->getDoctorant() ];
        $validations[Role::CODE_DOCTORANT] = [];
        foreach ($doctorants as $doctorant) {
            $validation = $this->getValidationService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $doctorant->getIndividu());
            if ($validation) $validations[Role::CODE_DOCTORANT][] = $validation;
        }


        /** Recuperation de la validation du directeur de thèse */
        $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $validations[Role::CODE_DIRECTEUR_THESE] = [];
        foreach ($directeurs as $directeur) {
            $validation = $this->getValidationService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $directeur->getIndividu());
            if ($validation) $validations[Role::CODE_DIRECTEUR_THESE][] = $validation;
        }

        /** Recuperation de la validation du codirecteur de thèse */
        $codirecteurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $validations[Role::CODE_CODIRECTEUR_THESE] = [];
        foreach ($codirecteurs as $codirecteur) {
            $validation = $this->getValidationService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these,TypeValidation::CODE_PROPOSITION_SOUTENANCE, $codirecteur->getIndividu());
            if ($validation) $validations[Role::CODE_CODIRECTEUR_THESE][] = $validation;
        }

        /** Recuperation de la validation de l'unite de recherche */
        $validations[Role::CODE_RESP_UR] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
        if (!empty($validation)) $validations[Role::CODE_RESP_UR][] = current($validation);

        /** Recuperation de la validation de l'école doctorale */
        $validations[Role::CODE_RESP_ED] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
        if (!empty($validation)) $validations[Role::CODE_RESP_ED][] = current($validation);

        /** Recuperation de la validation du bureau des doctorats */
        $validations[Role::CODE_BDD] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        if (!empty($validation)) $validations[Role::CODE_BDD][] = current($validation);

        /** Recuperation des engagement d'impartialite */
        $validations['Impartialite'] = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these);
        $validations['Avis']        = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_AVIS_SOUTENANCE, $these);

        $validations[TypeValidationSoutenance::CODE_VALIDATION_DECLARATION_HONNEUR] = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidationSoutenance::CODE_VALIDATION_DECLARATION_HONNEUR, $these);
        $validations[TypeValidationSoutenance::CODE_REFUS_DECLARATION_HONNEUR] = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidationSoutenance::CODE_REFUS_DECLARATION_HONNEUR, $these);

        return $validations;
    }

    /**
     * Genere la texte "M Pierre Denis, Le président de l'Universite de Caen Normandie"
     * @var These $these
     * @return string
     */
    public function generateLibelleSignaturePresidenceForThese(These $these): string
    {
        $ETB_LIB_NOM_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_NOM_RESP, $these->getEtablissement());
        $ETB_LIB_TIT_RESP = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB_TIT_RESP, $these->getEtablissement());
        $ETB_ART_ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_ART_ETB_LIB, $these->getEtablissement());
        $ETB_LIB = $this->variableService->getRepository()->findOneByCodeAndEtab(Variable::CODE_ETB_LIB, $these->getEtablissement());

        $libelle  = "";
        $libelle .= $ETB_LIB_NOM_RESP ? $ETB_LIB_NOM_RESP->getValeur() : "";
        $libelle .= ", ";
        $libelle .= $ETB_LIB_TIT_RESP ? $ETB_LIB_TIT_RESP->getValeur() : "(Variable ETB_LIB_TIT_RESP introuvable)";
        $libelle .= " de ";
        $libelle .= $ETB_ART_ETB_LIB ? $ETB_ART_ETB_LIB->getValeur() : "";
        $libelle .= $ETB_LIB ? $ETB_LIB->getValeur() : "(Variable ETB_LIB introuvable)";

        return $libelle;
    }

    /**
     * @var These $these
     * @return string[]
     */
    public function findLogosForThese(These $these): array
    {
        $logos = [];
        $logos['COMUE'] = null;
        if ($comue = $this->getEtablissementService()->fetchEtablissementComue()) {
            try {
                $logos['COMUE'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                $logos['COMUE'] = null;
            }
        }

        try {
            $logos['ETAB'] = $this->fichierStorageService->getFileForLogoStructure($these->getEtablissement()->getStructure());
        } catch (StorageAdapterException $e) {
            $logos['ETAB'] = null;
        }

        return $logos;
    }

    /**
     * @param These $these
     * @param Individu $currentIndividu
     * @param Role $currentRole
     * @return boolean
     */
    public function isValidated(These $these, Individu $currentIndividu, Role $currentRole): bool
    {
        $validations = [];
        switch($currentRole->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
                $validations = array_filter($validations, function (Validation $v) use ($currentIndividu) { return $v->getIndividu() === $currentIndividu;});
                break;
            case Role::CODE_RESP_UR :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                break;
            case Role::CODE_BDD :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
                break;
        }
        return !(empty($validations));
    }

    /**
     * @param Role $role
     * @return Proposition[]
     */
    public function findPropositionsByRole(Role $role): array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('these.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->orderBy('proposition.date', 'ASC')
        ;

        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $qb = $qb
                    ->andWhere('structure_ur.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $qb = $qb
                    ->andWhere('structure_ed.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            case Role::CODE_BDD :
                $qb = $qb
                    ->andWhere('structure_etab.id = :structure')
                    ->setParameter('structure', $role->getStructure(/*false*/)->getId())
                ;
                break;
            default:
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Soutenance\Entity\Proposition|null $proposition
     * @return Membre[]
     */
    public function extractMembresAsOptionsFromProposition(?Proposition $proposition): array
    {
        $array = [];

        if ($proposition !== null) {
            /** @var Membre[] $membres */
            $membres = $proposition->getMembres();
            foreach ($membres as $membre) {
                $array[$membre->getId()] = $membre->getDenomination();
            }
        }

        return $array;
    }

    /**
     * @param EcoleDoctorale $ecole
     * @return \Soutenance\Entity\Proposition[]
     */
    public function findSoutenancesAutoriseesByEcoleDoctorale(EcoleDoctorale $ecole) : array
    {
        $qb = $this->getRepository()->createQueryBuilder("proposition")
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('ecole')->leftJoin('these.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->andWhereStructureIs($ecole->getStructure(), 'structure_ed')
            ->andWhere('etat.code = :autorise')
            ->andWhere('DATE_ADD(these.dateSoutenance, 1, \'YEAR\') > :date')
            ->setParameter('autorise', Etat::VALIDEE)
            ->setParameter('date', new DateTime())
            ->orderBy('these.dateSoutenance', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /** PROPOSTITION ETAT  ********************************************************************************************/

    /**
     * @return Etat[]
     */
    public function findPropositionEtats() : array
    {
        $qb = $this->getEntityManager()->getRepository(Etat::class)->createQueryBuilder('etat')
            ->orderBy('etat.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $code
     * @return \Soutenance\Entity\Etat
     */
    public function findPropositionEtatByCode(string $code): Etat
    {
        $qb = $this->getEntityManager()->getRepository(Etat::class)->createQueryBuilder('etat')
            ->andWhere('etat.code = :code')
            ->setParameter('code', $code);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (ORMException $e) {
            throw new RuntimeException("Plusieurs ".Etat::class." partagent le même CODE [".$code."]", $e);
        }
    }

    /**
     * Les directeurs et co-directeurs sont des membres par défauts du jury d'une thèse. Cette fonction permet d'ajouter
     * ceux-ci à une proposition.
     * NB: La proposition doit être liée à une thèse.
     * NB: Les directeurs et codirecteurs sans mail ne sont pas ajoutés automatiquement
     *
     * @param Proposition $proposition
     */
    public function addDirecteursAsMembres(Proposition $proposition)
    {
        $these = $proposition->getThese();
        if ($these === null) throw new LogicException("Impossible d'ajout les directeurs comme membres : Aucun thèse de lié à la proposition id:" . $proposition->getId());

        $encadrements = $this->getActeurService()->getRepository()->findEncadrementThese($these);
        foreach ($encadrements as $encadrement) {
            $this->getMembreService()->createMembre($proposition, $encadrement);
        }
    }

    public function initialisationDateRetour(Proposition $proposition)
    {
        if ($proposition->getDate() === null) throw new RuntimeException("Aucune date de soutenance de renseignée !");
        try {
            $renduRapport = $proposition->getDate();
            $deadline = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
            $renduRapport = $renduRapport->sub(new DateInterval('P'. $deadline.'D'));

            $date = DateTime::createFromFormat('d/m/Y H:i:s', $renduRapport->format('d/m/Y') . " 23:59:59");
        } catch (Exception $e) {
            throw new RuntimeException("Un problème a été rencontré lors du calcul de la date de rendu des rapport.");
        }
        $proposition->setRenduRapport($date);
        $this->update($proposition);
    }

}