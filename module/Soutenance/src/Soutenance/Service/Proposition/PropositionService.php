<?php

namespace Soutenance\Service\Proposition;

//TODO faire le repo aussi
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Variable;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PropositionService {
    use EntityManagerAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use VariableServiceAwareTrait;
    use FileServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
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
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
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
            if($membre->getRole() === 'Rapporteur') $rapporteurs[] = $membre;
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
            $this->getNotifierService()->triggerDevalidationProposition($validation);
        }
        $validationED = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
        if ($validationED) {
            $this->getValidationService()->historise($validationED);
            $this->getNotifierService()->triggerDevalidationProposition($validationED);
        }
        $validationUR = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
        if ($validationUR) {
            $this->getValidationService()->historise($validationUR);
            $this->getNotifierService()->triggerDevalidationProposition($validationUR);
        }
        $validationBDD = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
        if ($validationBDD) {
            $this->getValidationService()->historise($validationBDD);
            $this->getNotifierService()->triggerDevalidationProposition($validationBDD);
        }
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function computeIndicateur($proposition)
    {
        $nbMembre       = 0;
        $nbFemme        = 0;
        $nbHomme        = 0;
        $nbRangA        = 0;
        $nbExterieur    = 0;
        $nbRapporteur   = 0;

        $parite_min = $this->getParametreService()->getParametreByCode('JURY_PARITE_RATIO_MIN')->getValeur();
        $membre_min =  $this->getParametreService()->getParametreByCode('JURY_SIZE_MIN')->getValeur();
        $membre_max =  $this->getParametreService()->getParametreByCode('JURY_SIZE_MAX')->getValeur();
        $rapporteur_min = $this->getParametreService()->getParametreByCode('JURY_RAPPORTEUR_SIZE_MIN')->getValeur();
        $rangA_min = $this->getParametreService()->getParametreByCode('JURY_RANGA_RATIO_MIN')->getValeur();
        $exterieur_min = $this->getParametreService()->getParametreByCode('JURY_EXTERIEUR_RATIO_MIN')->getValeur();

        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            $nbMembre++;
            if ($membre->getGenre() === "F") $nbFemme++; else $nbHomme++;
            if ($membre->getRang() === "A") $nbRangA++;
            if ($membre->getExterieur() === "oui") $nbExterieur++;
            if ($membre->getRole() === Membre::RAPPORTEUR || $membre->getRole() === Membre::RAPPORTEUR_ABSENT) $nbRapporteur++;
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
        if ($comue = $this->getEtablissementService()->fetchEtablissementCommunaute()) {
            $logos['COMUE'] = $this->fileService->computeLogoFilePathForStructure($comue);
        }
        $logos['ETAB']  = $this->fileService->computeLogoFilePathForStructure($these->getEtablissement());
        return $logos;
    }
}