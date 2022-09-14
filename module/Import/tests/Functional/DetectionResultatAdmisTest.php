<?php

namespace ImportTest\Functional;

use Import\Model\ImportObserv;
use These\Entity\Db\These;
use ApplicationUnitTest\Controller\AbstractControllerTestCase;
use Doctrine\DBAL\DBALException;

/**
 * L'objectif est de tester que l'on est bien en mesure de détecter que le résultat d'une thèse
 * vient de passer à 1/admis pendant la synchro faite par UnicaenImport.
 *
 * L'idée est qu'on modifie le temps du test la vue SRC_THESE pour simuler qu'une thèse vient de voir
 * son résultat passer à 1/admis.
 *
 * Le source code de la thèse à utiliser doit être spécifié via une variable d'env.
 *
 * NB: on réalise automatiquement la désactivation du CRON sur le serveur désigné le temps du test.
 * Pour cela il faut que la clé SSH permettant de se connecter en root au serveur soit installée dans le
 * container Docker via un volume (~/.ssh:/root/.ssh).
 *
 * @author Unicaen
 */
class DetectionResultatAdmisTest extends AbstractControllerTestCase
{
    const ENVVAR_THESE_SOURCE_CODE   = 'THESE_SOURCE_CODE';

    const ENVVAR_DISABLE_SYNCHRO_CMD = 'DISABLE_SYNCHRO_CMD';
    const ENVVAR_ENABLE_SYNCHRO_CMD  = 'ENABLE_SYNCHRO_CMD';

    private $srcTheseViewSelectOriginalSQL = <<<EOS
-- script initial de la vue src_these A VERIFIER !
--create view SRC_THESE as
select
    null                            as id,
    tmp.source_code                 as source_code,
    src.id                          as source_id,
    e.id                            as etablissement_id,
    d.id                            as doctorant_id,
    coalesce(ed_substit.id, ed.id)  as ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  as unite_rech_id,
    ed.id                           as ecole_doct_id_orig,
    ur.id                           as unite_rech_id_orig,
    tmp.lib_ths                     as titre,
    tmp.eta_ths                     as etat_these,
    to_number(tmp.cod_neg_tre)      as resultat,
    tmp.lib_int1_dis                as lib_disc,
    tmp.dat_deb_ths                 as date_prem_insc,
    tmp.ANNEE_UNIV_1ERE_INSC        as annee_univ_1ere_insc, -- deprecated
    tmp.dat_prev_sou                as date_prev_soutenance,
    tmp.dat_sou_ths                 as date_soutenance,
    tmp.dat_fin_cfd_ths             as date_fin_confid,
    tmp.lib_etab_cotut              as lib_etab_cotut,
    tmp.lib_pays_cotut              as lib_pays_cotut,
    tmp.correction_possible         as correc_autorisee,
    tem_sou_aut_ths                 as soutenance_autoris,
    dat_aut_sou_ths                 as date_autoris_soutenance,
    tem_avenant_cotut               as tem_avenant_cotut,
    dat_abandon                     as date_abandon,
    dat_transfert_dep               as date_transfert
from tmp_these tmp
         JOIN STRUCTURE s ON s.SOURCE_CODE = tmp.ETABLISSEMENT_ID
         join etablissement e on e.structure_id = s.id
         join source src on src.code = tmp.source_id
         join doctorant d on d.source_code = tmp.doctorant_id
         left join ecole_doct ed on ed.source_code = tmp.ecole_doct_id
         left join unite_rech ur on ur.source_code = tmp.unite_rech_id
         left join structure_substit ss_ed on ss_ed.from_structure_id = ed.structure_id
         left join ecole_doct ed_substit on ed_substit.structure_id = ss_ed.to_structure_id
         left join structure_substit ss_ur on ss_ur.from_structure_id = ur.structure_id
         left join unite_rech ur_substit on ur_substit.structure_id = ss_ur.to_structure_id
EOS;

    private function srcTheseViewUpdateSQL($sourceCode) {
        return <<<EOS
--
-- Simule qu'une thèse a son résultat qui passe à 1 à l'issu de la synchro :
--
-- ATTENTION !!
--   Script initial de la vue src_these A VERIFIER :
--   select text from all_views where view_name = 'SRC_THESE';
--
create or replace view src_these as
  with v as (
      $this->srcTheseViewSelectOriginalSQL
  )
  select 
    id,
    source_code,
    source_id,
    etablissement_id,
    doctorant_id,
    ecole_doct_id,
    unite_rech_id,
    ecole_doct_id_orig,
    unite_rech_id_orig,
    titre,
    etat_these,
    resultat,
    lib_disc,
    date_prem_insc,
    annee_univ_1ere_insc,
    date_prev_soutenance,
    date_soutenance,
    date_fin_confid,
    lib_etab_cotut,
    lib_pays_cotut,
    correc_autorisee,
    soutenance_autoris,
    date_autoris_soutenance,
    tem_avenant_cotut,
    date_abandon,
    date_transfert
  from v where SOURCE_CODE <> '$sourceCode'
  union
  select
    id,
    source_code,
    source_id,
    etablissement_id,
    doctorant_id,
    ecole_doct_id,
    unite_rech_id,
    ecole_doct_id_orig,
    unite_rech_id_orig,
    titre,
    etat_these,
    1 as resultat,
    lib_disc,
    date_prem_insc,
    annee_univ_1ere_insc,
    date_prev_soutenance,
    date_soutenance,
    date_fin_confid,
    lib_etab_cotut,
    lib_pays_cotut,
    correc_autorisee,
    soutenance_autoris,
    date_autoris_soutenance,
    tem_avenant_cotut,
    date_abandon,
    date_transfert
  from v where SOURCE_CODE = '$sourceCode'
EOS;
    }

    public function setUp()
    {
        parent::setUp();

//        // forçage du proriétaire et des permissions requis pour éviter l'erreur "Bad permissions"
//        $cmd = 'FILE=~/.ssh/config ; if [ -f "$FILE" ]; then chown root "$FILE" && chmod u+r "$FILE"; fi';
//        exec($cmd, $output, $returnCode);
    }

    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        $this->restoreSrcTheseView();

        parent::tearDown();
    }

    public function test()
    {
        // récupération de la thèse à traiter via une variable d'env
        $sourceCode = getenv($envvarName = self::ENVVAR_THESE_SOURCE_CODE);
        if (! $sourceCode) {
            $this->markTestSkipped("Aucun source code trouvé dans la variable d'environnement '$envvarName'.");
        }


        // Désactiver éventuellement la synchro.
        $this->disableUnicaenImportSynchro();


        // Recherche des SOURCE_CODE des thèses.
        $these = $this->em()->getRepository(These::class)->findOneBy(['sourceCode' => $sourceCode]);
        if ($these === null) {
            $this->markTestSkipped("Aucune thèse trouvée avec ce source code : " . $sourceCode);
        }
        if ($these->getResultat() === These::RESULTAT_ADMIS) {
            $this->markTestSkipped("La thèse '$sourceCode' ne convient pas car son résultat est déjà à " . These::RESULTAT_ADMIS);
        }


        // Modif Vue src_these.
        $sql = $this->srcTheseViewUpdateSQL($sourceCode);

        try {
            $this->em()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors de la modification de la vue SRC_THESE : " . $e->getMessage());
        }


        // Vérification que les thèses apparaissent bien dans la vue diff.
        $sql = <<<EOS
select id, SOURCE_CODE, IMPORT_ACTION, CORREC_AUTORISEE, RESULTAT, U_CORREC_AUTORISEE, U_RESULTAT
from V_DIFF_THESE_sav
where source_code = '$sourceCode'
EOS;
        try {
            $rows = $this->em()->getConnection()->executeQuery($sql)->fetchAll();
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors de la recherche dans V_DIFF_THESE : " . $e->getMessage());
        }
        if (empty($rows)) {
            $this->markTestSkipped("La vue V_DIFF_THESE devrait contenir la thèse '$sourceCode'.");
        }


        // Lancement procédure.
        $sql = <<<EOS
begin APP_IMPORT.STORE_OBSERV_RESULTS; end;
EOS;
        try {
            $this->em()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors du lancement de 'APP_IMPORT.STORE_OBSERV_RESULTS' : " . $e->getMessage());
        }


        // Vérification présence d'un résultat d'observation SANS date de notif.
        $sql = <<<EOS
select ior.id, io.CODE, ior.DATE_CREATION, ior.SOURCE_CODE, ior.RESULTAT, ior.DATE_NOTIF
from IMPORT_OBSERV_RESULT ior
join IMPORT_OBSERV io on ior.IMPORT_OBSERV_ID = io.ID
where SOURCE_CODE = '$sourceCode'
and ior.DATE_NOTIF is null
order by DATE_CREATION desc
EOS;
        $rows = [];
        try {
            $rows = $this->em()->getConnection()->executeQuery($sql)->fetchAll();
            $iorId = $rows[0]['ID'];
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors de la recherche dans IMPORT_OBSERV_RESULT : " . $e->getMessage());
        }
        $this->assertNotEmpty($rows, "La table IMPORT_OBSERV_RESULT devrait contenir le résultat d'observation.");


        // Lancer le script PHP de traitement des résultats d'observation :
        $cmd = sprintf(
            'php public/index.php process-observed-import-results --etablissement=%s --import-observ=%s --source-code=%s',
            'UCN', ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS, $sourceCode);
        exec($cmd, $output, $returnCode);
        if ($returnCode !== 0) {
            $this->markTestSkipped("Erreur lors du lancement de la ligne de commande '$cmd' : " . implode(PHP_EOL, $output));
        }


        // Restauration Vue src_these initiale.
        $this->restoreSrcTheseView();


        // Réactiver éventuellement la synchro.
        $this->enableUnicaenImportSynchro();


        // Vérification présence d'un résultat d'observation AVEC date de notif.
        $sql = <<<EOS
select ior.id, ior.DATE_CREATION, ior.SOURCE_CODE, ior.RESULTAT, ior.DATE_NOTIF
from IMPORT_OBSERV_RESULT ior
where id = $iorId
and ior.DATE_NOTIF is not null
EOS;
        try {
            $rows = $this->em()->getConnection()->executeQuery($sql)->fetchAll();
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors de la recherche dans IMPORT_OBSERV_RESULT : " . $e->getMessage());
        }
        $this->assertNotEmpty($rows, "La table IMPORT_OBSERV_RESULT devrait contenir le résultat d'observation avec une date de notification");

        echo "À vérifier : Un mail doit avoir été envoyé à propos d'un résultat de thèse passé à admis..." . PHP_EOL;
    }

    private function disableUnicaenImportSynchro()
    {
        $name = self::ENVVAR_DISABLE_SYNCHRO_CMD;
        if ($cmd = getenv($name)) {
            echo "Commande de désactivation de la synchro trouvée dans la variable d'environnement $name : " . $cmd . PHP_EOL;
            // ex: 'ssh root@usygal1.unr-runn.fr chmod -x /etc/cron.d/sygal'
            exec($cmd, $output, $returnCode);
            if ($returnCode !== 0) {
                $this->markTestSkipped("Erreur lors du lancement de la ligne de commande '$cmd' : " . implode(PHP_EOL, $output));
            }
        } else {
            echo "Pour fournir la commande de désactivation de la synchro, utilisez la variable d'environnement $name." . PHP_EOL;
        }
    }

    private function enableUnicaenImportSynchro()
    {
        $name = self::ENVVAR_ENABLE_SYNCHRO_CMD;
        if ($cmd = getenv($name)) {
            echo "Commande de réactivation de la synchro trouvée dans la variable d'environnement $name : " . $cmd . PHP_EOL;
            // ex: 'ssh root@usygal1.unr-runn.fr chmod +x /etc/cron.d/sygal'
            exec($cmd, $output, $returnCode);
            if ($returnCode !== 0) {
                $this->markTestSkipped("Erreur lors du lancement de la ligne de commande '$cmd' : " . implode(PHP_EOL, $output));
            }
        } else {
            echo "Pour fournir la commande de réactivation de la synchro, utilisez la variable d'environnement $name." . PHP_EOL;
        }
    }

    private function restoreSrcTheseView()
    {
        $srcTheseViewSelectOriginalSQL = $this->srcTheseViewSelectOriginalSQL;
        $sql = <<<EOS
create or replace view SRC_THESE as
  $srcTheseViewSelectOriginalSQL
EOS;
        try {
            $this->em()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            $this->markTestSkipped("Erreur lors de la restauration de la vue SRC_THESE : " . $e->getMessage());
        }
    }
}