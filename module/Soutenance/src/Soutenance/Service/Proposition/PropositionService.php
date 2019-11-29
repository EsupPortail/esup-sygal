<?php

namespace Soutenance\Service\Proposition;

//TODO faire le repo aussi
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Entity\Db\Variable;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PropositionService {
    use EntityManagerAwareTrait;
    use ValidatationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use VariableServiceAwareTrait;
    use FileServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    /**
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(/*$alias = 'proposition'*/)
    {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('unite')->leftJoin('these.uniteRecherche', 'unite')
            ->addSelect('structure_ur')->leftJoin('unite.structure', 'structure_ur')
            ->addSelect('ecole')->leftJoin('these.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->addSelect('etablissement')->leftJoin('these.etablissement', 'etablissement')
            ->addSelect('structure_etab')->leftJoin('etablissement.structure', 'structure_etab')
            ->addSelect('membre')->join('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            //->addSelect('validation')->leftJoin('proposition.validations', 'validation')



            ;
        return $qb;
    }

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
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

    /**
     * @param These $these
     * @return Proposition
     */
    public function findByThese($these) {
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
     * @return Proposition[]
     */
    public function findAll() {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Proposition $proposition
     */
    public function update($proposition)
    {
        try {
            $this->getEntityManager()->flush($proposition);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
    }

    public function findMembre($idMembre)
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->andWhere("membre.id = :id")
            ->setParameter("id", $idMembre)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiple membres sont associés à l'identifiant [".$idMembre."] !");
        }
        return $result;
    }

    public function create($proposition)
    {
        $this->getEntityManager()->persist($proposition);
        try {
            $this->getEntityManager()->flush($proposition);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la proposition de thèse !");
        }
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteurs($proposition) {
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
    public function annulerValidations($proposition)
    {
        $these = $proposition->getThese();
        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
            $this->getNotifierSoutenanceService()->triggerDevalidationProposition($validation);
        }
        $validationED = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
        if ($validationED) {
            $this->getValidationService()->historise($validationED);
            $this->getNotifierSoutenanceService()->triggerDevalidationProposition($validationED);
        }
        $validationUR = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
        if ($validationUR) {
            $this->getValidationService()->historise($validationUR);
            $this->getNotifierSoutenanceService()->triggerDevalidationProposition($validationUR);
        }
        $validationBDD = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
        if ($validationBDD) {
            $this->getValidationService()->historise($validationBDD);
            $this->getNotifierSoutenanceService()->triggerDevalidationProposition($validationBDD);
        }
//        $proposition->setSurcis(false);
//        $this->update($proposition);
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function computeIndicateur($proposition)
    {
        if ($proposition === null) return [];

        $nbMembre       = 0;
        $nbFemme        = 0;
        $nbHomme        = 0;
        $nbRangA        = 0;
        $nbExterieur    = 0;
        $nbRapporteur   = 0;

        $parameters     =  $this->getParametreService()->getParametresAsArray();
        $parite_min     =  $parameters['JURY_PARITE_RATIO_MIN'];
        $membre_min     =  $parameters['JURY_SIZE_MIN'];
        $membre_max     =  $parameters['JURY_SIZE_MAX'];
        $rapporteur_min =  $parameters['JURY_RAPPORTEUR_SIZE_MIN'];
        $rangA_min      =  $parameters['JURY_RANGA_RATIO_MIN'];
        $exterieur_min  =  $parameters['JURY_EXTERIEUR_RATIO_MIN'];

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            $nbMembre++;
            if ($membre->getGenre() === "F") $nbFemme++; else $nbHomme++;
            if ($membre->getRang() === "A") $nbRangA++;
            if ($membre->isExterieur()) $nbExterieur++;
            if ($membre->estRapporteur()) $nbRapporteur++;
        }

        $indicateurs = [];

        /**  Il faut essayer de maintenir la parité Homme/Femme*/
        $ratioFemme = ($nbMembre)?$nbFemme / $nbMembre:0;
        $ratioHomme = ($nbMembre)?(1 - $ratioFemme):0;
        $indicateurs["parité"]      = ["Femme" => $ratioFemme, "Homme" => $ratioHomme];
        if (min($ratioFemme,$ratioHomme) < $parite_min) {
            $indicateurs["parité"]["valide"]    = false;
        } else {
            $indicateurs["parité"]["valide"]    = true;
        }

        /** entre 4 et 8 membres */
        $indicateurs["membre"]      = ["Nombre" => $nbMembre, "Ratio" => ($nbMembre)?$nbMembre/10:0];

        if ($nbMembre < $membre_min OR $nbMembre > $membre_max) {
            $indicateurs["membre"]["valide"]    = false;
        } else {
            $indicateurs["membre"]["valide"]    = true;
        }

        /** Au moins deux rapporteurs */
        $indicateurs["rapporteur"]      = ["Nombre" => $nbRapporteur, "Ratio" => ($nbMembre)?$nbRapporteur/$nbMembre:0];

        if ($nbRapporteur < $rapporteur_min) {
            $indicateurs["rapporteur"]["valide"]    = false;
        } else {
            $indicateurs["rapporteur"]["valide"]    = true;
        }

        /** Au moins la motié du jury de rang A */
        $ratioRangA = ($nbMembre)?($nbRangA / $nbMembre):0;
        $indicateurs["rang A"]      = ["Nombre" => $nbRangA, "Ratio" => $ratioRangA];
        if ($ratioRangA < $rangA_min || !$nbMembre)  {
            $indicateurs["rang A"]["valide"]    = false;
        } else {
            $indicateurs["rang A"]["valide"]    = true;
        }

        /** Au moins la motié du jury exterieur*/
        $ratioExterieur = ($nbMembre)?($nbExterieur / $nbMembre):0;
        $indicateurs["exterieur"]      = ["Nombre" => $nbExterieur, "Ratio" => $ratioExterieur];
        if ($ratioExterieur < $exterieur_min || !$nbMembre)  {
            $indicateurs["exterieur"]["valide"]    = false;
        } else {
            $indicateurs["exterieur"]["valide"]    = true;
        }

        $valide = $indicateurs["parité"]["valide"] && $indicateurs["membre"]["valide"] && $indicateurs["rapporteur"]["valide"]
            && $indicateurs["rang A"]["valide"] && $indicateurs["exterieur"]["valide"];

        $indicateurs["valide"] = $valide;

        return $indicateurs;
    }

    /**
     * @param Proposition $proposition
     * @return boolean
     */
    public function juryOk($proposition)
    {
        $indicateurs = $this->computeIndicateur($proposition);
        if (!$indicateurs["valide"]) return false;
        return true;
    }

    /**
     * @param Proposition $proposition
     * @return boolean
     */
    public function isOk($proposition)
    {
        $indicateurs = $this->computeIndicateur($proposition);
        if (!$indicateurs["valide"]) return false;
        if(! $proposition->getDate() || ! $proposition->getLieu()) return false;
        return true;
    }

    /**
     * @param These $these
     * @return array
     */
    public function getValidationSoutenance($these)
    {
        $validations = [];

        /** Recuperation de la validation du directeur de thèse */
        $doctorants = [ $these->getDoctorant() ];
        $validations[Role::CODE_DOCTORANT] = [];
        foreach ($doctorants as $doctorant) {
            $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $doctorant->getIndividu());
            if ($validation) $validations[Role::CODE_DOCTORANT][] = current($validation);
        }


        /** Recuperation de la validation du directeur de thèse */
        $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $validations[Role::CODE_DIRECTEUR_THESE] = [];
        foreach ($directeurs as $directeur) {
            $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $directeur->getIndividu());
            if ($validation) $validations[Role::CODE_DIRECTEUR_THESE][] = current($validation);
        }

        /** Recuperation de la validation du codirecteur de thèse */
        $codirecteurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $validations[Role::CODE_CODIRECTEUR_THESE] = [];
        foreach ($codirecteurs as $codirecteur) {
            $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $codirecteur->getIndividu());
            if ($validation) $validations[Role::CODE_CODIRECTEUR_THESE][] = current($validation);
        }

        /** Recuperation de la validation de l'unite de recherche */
        $validations[Role::CODE_UR] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
        if (!empty($validation)) $validations[Role::CODE_UR][] = current($validation);

        /** Recuperation de la validation de l'école doctorale */
        $validations[Role::CODE_ED] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
        if (!empty($validation)) $validations[Role::CODE_ED][] = current($validation);

        /** Recuperation de la validation du bureau des doctorats */
        $validations[Role::CODE_BDD] = [];
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        if (!empty($validation)) $validations[Role::CODE_BDD][] = current($validation);

        /** Recuperation des engagement d'impartialite */
        $validations['Impartialite'] = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these);
        $validations['Avis']        = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_AVIS_SOUTENANCE, $these);


        return $validations;
    }

    /**
     * Genere la texte "M Pierre Denis, Le président de l'Universite de Caen Normandie"
     * @var These $these
     * @return string
     */
    public function generateLibelleSignaturePresidence($these)
    {
        $libelle  = "";
        $libelle .= $this->variableService->getRepository()->findByCodeAndEtab(Variable::CODE_ETB_LIB_NOM_RESP, $these->getEtablissement())->getValeur();
        $libelle .= ", ";
        $libelle .= $this->variableService->getRepository()->findByCodeAndEtab(Variable::CODE_ETB_LIB_TIT_RESP, $these->getEtablissement())->getValeur();
        $libelle .= " de ";
        $libelle .= $this->variableService->getRepository()->findByCodeAndEtab(Variable::CODE_ETB_ART_ETB_LIB, $these->getEtablissement())->getValeur();
        $libelle .= $this->variableService->getRepository()->findByCodeAndEtab(Variable::CODE_ETB_LIB, $these->getEtablissement())->getValeur();

        return $libelle;
    }

    /**
     * @var These $these
     * @return array
     */
    public function getLogos($these)
    {
        $logos = [];
        $logos['COMUE'] = null;
        if ($comue = $this->getEtablissementService()->fetchEtablissementComue()) {
            $logos['COMUE'] = $this->fileService->computeLogoFilePathForStructure($comue);
        }
        $logos['ETAB']  = $this->fileService->computeLogoFilePathForStructure($these->getEtablissement());
        return $logos;
    }

    /**
     * @param These $these
     * @param Individu $currentIndividu
     * @param Role $currentRole
     * @return boolean
     */
    public function isValidated($these, $currentIndividu, $currentRole)
    {
        $validations = [];
        switch($currentRole->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
                $validations = array_filter($validations, function (Validation $v) use ($currentIndividu) { return $v->getIndividu() === $currentIndividu;});
                break;
            case Role::CODE_UR :
                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                break;
            case Role::CODE_ED :
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
    public function getPropositionsByRole($role)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('these.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS)
        ;

        switch ($role->getCode()) {
            case Role::CODE_UR :
                $qb = $qb ->andWhere('structure_ur.id = :unite')
                    ->setParameter('unite', $role->getStructure()->getId())
                ;
                break;
            case Role::CODE_ED :
                $qb = $qb->andWhere('structure_ed.id = :ecole')
                    ->setParameter('ecole', $role->getStructure()->getId())
                ;
                break;
            case Role::CODE_BDD :
                $qb = $qb->andWhere('structure_etab.id = :etablissement')
                    ->setParameter('etablissement', $role->getStructure()->getId())
                ;
                break;
            default:
                break;
        }

        $propositions = $qb->getQuery()->getResult();
        return $propositions;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function getMembresAsOptions($proposition)
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

    /** PROPOSTITION ETAT  ********************************************************************************************/

    /**
     * @param string $code
     * @return Etat
     */
    public function getPropositionEtatByCode($code)
    {
        $qb = $this->getEntityManager()->getRepository(Etat::class)->createQueryBuilder('etat')
            ->andWhere('etat.code = :code')
            ->setParameter('code', $code)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
            return $result;
        } catch (ORMException $e) {
            throw new RuntimeException("Plusieurs ".Etat::class." partagent le même CODE [".$code."]", $e);
        }
    }
}