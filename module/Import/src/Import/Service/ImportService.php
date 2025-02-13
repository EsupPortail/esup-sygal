<?php

namespace Import\Service;

use These\Entity\Db\These;
use Import\Service\Traits\SynchroServiceAwareTrait;
use Laminas\Log\LoggerAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 * Service d'import des données provenant d'un établissement.
 *
 * Les données sont d'abord obtenues en interrogeant le web service de l'établissement en question.
 * Elles sont stockées dans les tables TMP_*.
 *
 * Ensuite, la synchronisation entre les vues SRC_* (basées sur les tables TMP_*) et les tables finales
 * est demandée au package UnicaenImport.
 *
 * @author Unicaen
 */
class ImportService
{
    use EtablissementServiceAwareTrait;
    use EntityManagerAwareTrait;
    use SynchroServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * Lance l'import pour la mise à jour d'une thèse déjà présente dans la base de données, et de ses données liées.
     *
     * Pour l'instant, les données liées se limitent à celles concernées par la génération de la page de couverture.
     *
     * @param These $these
     */
    public function updateThese(These $these)
    {
        throw new \BadMethodCallException("À réécrire pour unicaen/db-import !");

//        $this->fetcherService->setEtablissement($these->getEtablissement());
//
//        /**
//         * Appel du WS pour mettre à jour les tables TMP_*.
//         */
//        // these
//        $sourceCodeThese = $these->getSourceCode();
//        $this->fetcherService->fetchRows('these', ['source_code' => $sourceCodeThese]);
//        /** @var TmpThese $tmpThese */
//        $tmpThese = $this->entityManager->getRepository(TmpThese::class)->findOneBy(['sourceCode' => $sourceCodeThese]);
//        // doctorant
//        $sourceCodeDoctorant = $tmpThese->getDoctorantId();
//        $this->fetcherService->fetchRows('doctorant', ['source_code' => $sourceCodeDoctorant]);
//        /** @var TmpDoctorant $tmpDoctorant */
//        $tmpDoctorant = $this->entityManager->getRepository(TmpDoctorant::class)->findOneBy(['sourceCode' => $sourceCodeDoctorant]);
//        // individu doctorant
//        $sourceCodeIndividu = $tmpDoctorant->getIndividuId();
//        $this->fetcherService->fetchRows('individu', ['source_code' => $sourceCodeIndividu]);
//        // acteurs
//        $theseId = $these->getId();
//        $this->fetcherService->fetchRows('acteur', ['these' => $these]);
//        /** @var TmpActeur[] $tmpActeurs */
//        $tmpActeurs = $this->entityManager->getRepository(TmpActeurThese::class)->findBy(['theseId' => $sourceCodeThese]);
//        // individus acteurs
//        $sourceCodeIndividus = [];
//        foreach ($tmpActeurs as $tmpActeur) {
//            $sourceCodeIndividus[] = $sourceCodeIndividu = $tmpActeur->getIndividuId();
//            $this->fetcherService->fetchRows('individu', ['source_code' => $sourceCodeIndividu]);
//        }
//        // ed
//        $sourceCodeEcoleDoct = $tmpThese->getEcoleDoctId();
//        $this->fetcherService->fetchRows('ecole-doctorale', ['source_code' => $sourceCodeEcoleDoct]);
//        // ur
//        $sourceCodeUniteRech = $tmpThese->getUniteRechId();
//        $this->fetcherService->fetchRows('unite-recherche', ['source_code' => $sourceCodeUniteRech]);
//
//        /**
//         * Synchro UnicaenImport pour mettre à jour les tables finales.
//         */
//        $quotifier = function($v) { return "'$v'"; };
//        $sqlFilterIndividu = sprintf("SOURCE_CODE IN (%s)", implode(', ', array_map($quotifier, $sourceCodeIndividus)));
//        $serviceNameSuffix = '-' . $these->getEtablissement()->getCode();
//        $this->synchroService->addService('these' . $serviceNameSuffix,
//            ['sql_filter' => "SOURCE_CODE = '$sourceCodeThese'"]
//        );
//        $this->synchroService->addService('doctorant' . $serviceNameSuffix,
//            ['sql_filter' => "SOURCE_CODE = '$sourceCodeDoctorant'"]
//        );
//        $this->synchroService->addService('individu' . $serviceNameSuffix,
//            ['sql_filter' => "SOURCE_CODE = '$sourceCodeIndividu'"]
//        );
//        $this->synchroService->addService('acteur' . $serviceNameSuffix,
//            ['sql_filter' => "THESE_ID = '$theseId'"]
//        );
//        $this->synchroService->addService('individu' . $serviceNameSuffix,
//            ['sql_filter' => $sqlFilterIndividu]
//        );
//        $this->synchroService->addService('ecole-doctorale' . $serviceNameSuffix,
//            ['sql_filter' => "SOURCE_CODE = '$sourceCodeEcoleDoct'"]
//        );
//        $this->synchroService->addService('unite-recherche' . $serviceNameSuffix,
//            ['sql_filter' => "SOURCE_CODE = '$sourceCodeUniteRech'"]
//        );
//        $this->synchroService->synchronize();
//
//        // On met à jour le HISTO_MODIFICATION de la thèse pour mémoriser la date de l'import forcé qu'on vient de faire.
//        // Pas super parce que normalement HISTO_MODIFICATION n'est modifiée que si l'import a mis à jour la thèse).
//        try {
//            $these->setHistoModification(new \DateTime());
//        } catch (\Exception $e) {
//            throw new RuntimeException("C'est le bouquet!");
//        }
//        try {
//            $this->getEntityManager()->flush($these);
//        } catch (OptimisticLockException $e) {
//            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en base de données", null, $e);
//        }
    }
}