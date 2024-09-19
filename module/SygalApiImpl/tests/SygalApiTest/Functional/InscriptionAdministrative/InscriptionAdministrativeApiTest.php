<?php

namespace SygalApiTest\Functional\InscriptionAdministrative;

use Doctrine\ORM\Query;
use Faker\Factory;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use Locale;
use SygalApiTest\Functional\AbstractApiTest;

class InscriptionAdministrativeApiTest extends AbstractApiTest
{
    const instancePegasePrefix = 'PEGAZ_';
    const anneeUniv1 = 2022;
    const anneeUniv2 = 2023;
    const anneeUniv3 = 2024;

    protected string $applicationConfigPath = __DIR__ . '/../../../../../../config/application.config.php';

    protected string $apiUrl = 'https://localhost/api/v1/inscription-administrative';

    protected string $jsonOriginalFileName = 'inscr_admin';
    protected string $jsonTemplateFilesDir = __DIR__ . '/json';
    protected string $jsonTemplateFileName = 'inscr_admin.json.twig';

    protected bool $cleanAfterTest = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$jsonOutputDirPath = uniqid('/tmp/sygal_unit_tests_');
        mkdir(static::$jsonOutputDirPath);
        //echo "Répertoire des fichiers JSON : " . static::$jsonOutputDirPath . PHP_EOL;

        static::$testSourceCode = static::instancePegasePrefix . rand(1000, 9999);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setUp(): void
    {
        $this->applicationConfigOverrides = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'metadata_cache'   => false,
                        'query_cache'      => false,
                        'result_cache'     => false,
                        'hydration_cache'  => false,
                        'generate_proxies' => false,
                    ],
                ],
            ],
        ];

        parent::setUp();

        $this->createTestSource();
    }

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function tearDown(): void
    {
        if ($this->cleanAfterTest) {
            $this->deleteRecordsInTestSource();
            //rmdir($this->jsonOutputDirPath);
        }

        parent::tearDown();
    }

    public function getInitialContext(): array
    {
        $faker = Factory::create(Locale::getDefault());

        return [
            'instancePegase' => static::$testSourceCode,
            'codeApprenant' => rand(10000, 99999),
            'noCandidat' => 'SYG' . rand(1000, 9999),
            'ine' => rand(100000000, 999999999),
            'annee' => static::anneeUniv1,
            'statutInscription' => InscriptionAdministrative::STATUT_INSCRIPTION_VALIDE,
            'nom' => $faker->lastName(),
            'prenom' => $faker->firstName(),
        ];
    }

//    /**
//     * @throws \Doctrine\ORM\Exception\NotSupported
//     */
//    public function testCanReceiveInscriptionAnneeN(): array
//    {
//        $context = $this->getInitialContext();
//
//        $noCandidat = $context['noCandidat'];
//        $annee = $context['annee'];
//
//        /*******************************************************************
//         * inscription 2022
//         *******************************************************************/
//
//        $jsonFileNameNew = $this->jsonOriginalFileName . "_$annee";
//        $this->generateJson($jsonFileNameNew, $context);
//        $this->callApi($jsonFileNameNew);
//
//        $ia = $this->em()->getRepository(InscriptionAdministrative::class)->findOneBy([
//            'noCandidat' => $noCandidat,
//            'periodeAnneeUniversitaire' => $annee,
//        ]);
//        $this->assertNotNull($ia, "IA $annee non trouvée");
//        $iaa = $this->em()->getRepository(InscriptionAdministrativeApprenant::class)->findOneBy(['inscriptionAdministrative' => $ia->getId()]);
//        $this->assertNotNull($iaa, "IA $annee non trouvé");
//
//        return $context;
//    }
//
//    /**
//     * @depends testCanReceiveInscriptionAnneeN
//     * @throws \Doctrine\ORM\Exception\NotSupported
//     */
//    public function testCanReceiveInscriptionAnneeNp1(array $context): array
//    {
//        $noCandidat = $context['noCandidat'];
//        $annee = $context['annee'];
//
//        /*******************************************************************
//         * inscription 2023
//         *******************************************************************/
//
//        $anneeNew = static::anneeUniv2;
//        $context['annee'] = $anneeNew;
//
//        $jsonFileNameNew = $this->jsonOriginalFileName . "_$anneeNew";
//        $this->generateJson($jsonFileNameNew, $context);
//        $this->callApi($jsonFileNameNew);
//
//        $ia = $this->em()->getRepository(InscriptionAdministrative::class)->findOneBy([
//            'noCandidat' => $noCandidat,
//            'periodeAnneeUniversitaire' => $anneeNew,
//        ]);
//        $this->assertNotNull($ia, "IA $anneeNew non trouvée");
//        $iaa = $this->em()->getRepository(InscriptionAdministrativeApprenant::class)->findOneBy(['inscriptionAdministrative' => $ia->getId()]);
//        $this->assertNotNull($iaa, "IA $anneeNew non trouvé");
//
//        return $context;
//    }
//
//    /**
//     * @depends testCanReceiveInscriptionAnneeNp1
//     * @throws \Doctrine\ORM\Exception\NotSupported
//     */
//    public function testCanReceiveInscriptionAnneeNp2(array $context)
//    {
//        $noCandidat = $context['noCandidat'];
//
//        /*******************************************************************
//         * inscription 2024
//         *******************************************************************/
//
//        $anneeNew = static::anneeUniv3;
//        $context['annee'] = $anneeNew;
//
//        $jsonFileNameNew = $this->jsonOriginalFileName . "_$anneeNew";
//        $this->generateJson($jsonFileNameNew, $context);
//        $this->callApi($jsonFileNameNew);
//
//        $ia = $this->em()->getRepository(InscriptionAdministrative::class)->findOneBy([
//            'noCandidat' => $noCandidat,
//            'periodeAnneeUniversitaire' => $anneeNew,
//        ]);
//        $this->assertNotNull($ia, "IA $anneeNew non trouvée");
//        $iaa = $this->em()->getRepository(InscriptionAdministrativeApprenant::class)->findOneBy(['inscriptionAdministrative' => $ia->getId()]);
//        $this->assertNotNull($iaa, "IA $anneeNew non trouvé");
//
//        return $context;
//    }
//
//    /**
//     * @throws \Doctrine\ORM\Exception\NotSupported
//     * @throws \Doctrine\ORM\NonUniqueResultException
//     */
//    public function testChangementCodeApprenantCreeUneNouvelleInscription()
//    {
//        $context = $this->getInitialContext();
//
//        $noCandidat = $context['noCandidat'];
//        $codeApprenant = $context['codeApprenant'];
//
//        $context['annee'] = $annee = static::anneeUniv3;
//
//        /*******************************************************************
//         * inscription 2024
//         *******************************************************************/
//
//        $jsonFileNameNew = $this->jsonOriginalFileName . "_{$annee}_avant_chg_code_apprenant";
//        $this->generateJson($jsonFileNameNew, $context);
//        $this->callApi($jsonFileNameNew);
//
//        /** @var \InscriptionAdministrative\Entity\Db\Repository\InscriptionAdministrativeRepository $repo */
//        $repo = $this->em()->getRepository(InscriptionAdministrative::class);
//
//        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $ia1 */
//        $ia1 = $repo->createQueryBuilder('ia')
//            ->andWhere('ia.noCandidat = :noCandidat')->setParameter('noCandidat', $noCandidat)
//            ->andWhere('ia.periodeAnneeUniversitaire = :periodeAnneeUniversitaire')->setParameter('periodeAnneeUniversitaire', $annee)
//            ->andWhere('iaa.code = :codeApprenant')->setParameter('codeApprenant', $codeApprenant)
//            ->getQuery()->getOneOrNullResult();
//        if ($ia1 === null) {
//            $this->markTestSkipped("IA de départ introuvable !");
//        }
//
//        /*******************************************************************************************
//         * inscription sur la même année mais avec un autre code apprenant (cas rare)
//         *******************************************************************************************/
//
//        $codeApprenantNew = $codeApprenant . 'X';
//        $context['codeApprenant'] = $codeApprenantNew;
//
//        $jsonFileNameNew = $this->jsonOriginalFileName . "_{$annee}_chg_code_apprenant";
//        $this->generateJson($jsonFileNameNew, $context);
//        $this->callApi($jsonFileNameNew);
//
//        // refetch IA 2024
//        $ia1 = $repo->createQueryBuilder('ia')
//            ->andWhere('ia.noCandidat = :noCandidat')->setParameter('noCandidat', $noCandidat)
//            ->andWhere('ia.periodeAnneeUniversitaire = :periodeAnneeUniversitaire')->setParameter('periodeAnneeUniversitaire', $annee)
//            ->andWhere('iaa.code = :codeApprenant')->setParameter('codeApprenant', $codeApprenant)
//            ->getQuery()->getOneOrNullResult();
//        $this->assertNotNull(
//            $ia1,
//            "L'IA $annee ayant le code apprenant $codeApprenant et le numero candidat $noCandidat devrait toujours exister"
//        );
//        $this->assertNull(
//            $ia1->getHistoModification(),
//            "L'IA $annee ayant le code apprenant $codeApprenant et le numero candidat $noCandidat ne devrait pas avoir été modifiée"
//        );
//
//        // fetch IA 2
//        $ia2 = $repo->createQueryBuilder('ia')
//            ->andWhere('ia.noCandidat = :noCandidat')->setParameter('noCandidat', $noCandidat)
//            ->andWhere('ia.periodeAnneeUniversitaire = :periodeAnneeUniversitaire')->setParameter('periodeAnneeUniversitaire', $annee)
//            ->andWhere('iaa.code = :codeApprenant')->setParameter('codeApprenant', $codeApprenantNew)
//            ->getQuery()->getOneOrNullResult();
//        $this->assertNotNull(
//            $ia2,
//            "On devrait trouver une IA $annee ayant le code apprenant $codeApprenantNew et le numero candidat $noCandidat ne devrait pas avoir été modifiée"
//        );
//    }

    /**
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testChangementINEUpdatesDoctorant()
    {
        $context = $this->getInitialContext();

        $noCandidat = $context['noCandidat'];
        $codeApprenant = $context['codeApprenant'];
        $ine = $context['ine'];

        $context['annee'] = $annee = static::anneeUniv3;

        /*******************************************************************
         * inscription 2024
         *******************************************************************/

        $jsonFileNameNew = $this->jsonOriginalFileName . "_{$annee}_chg_ine_avant";
        $this->generateJson($jsonFileNameNew, $context);
        $this->callApi($jsonFileNameNew);

        /** @var \InscriptionAdministrative\Entity\Db\Repository\InscriptionAdministrativeRepository $repo */
        $repo = $this->em()->getRepository(InscriptionAdministrative::class);

        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $ia */
        $ia = $repo->createQueryBuilder('ia')
            ->andWhere('ia.noCandidat = :noCandidat')->setParameter('noCandidat', $noCandidat)
            ->andWhere('ia.periodeAnneeUniversitaire = :periodeAnneeUniversitaire')->setParameter('periodeAnneeUniversitaire', $annee)
            ->andWhere('iaa.code = :codeApprenant')->setParameter('codeApprenant', $codeApprenant)
            ->andWhere('iaa.INE = :ine')->setParameter('ine', $ine)
            ->getQuery()->getOneOrNullResult();
        if ($ia === null) {
            $this->markTestSkipped("IA de départ introuvable !");
        }
        $iaId = $ia->getId();

        /*******************************************************************
         * modif de l'INE de l'apprenant
         *******************************************************************/

        $ineNew = $ine . 'Z';
        $context['ine'] = $ineNew;

        $jsonFileNameNew = $this->jsonOriginalFileName . "_{$annee}_chg_ine_apres";
        $this->generateJson($jsonFileNameNew, $context);
        $this->callApi($jsonFileNameNew);

        // refetch IA pour verif modif INE
        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $ia */
        $ia = $repo->createQueryBuilder('ia')
            ->andWhere('ia = :id')->setParameter('id', $iaId)
            ->getQuery()->setHint(Query::HINT_REFRESH, true)->getOneOrNullResult();
        $this->assertEquals(
            $ineNew,
            $ia->getInscriptionAdministrativeApprenant()->getINE(),
            "L'IA $annee ayant le code apprenant $codeApprenant, le numero candidat $noCandidat et le nouvel ine apprenant $ineNew devrait exister"
        );

        // pas d'autre IA avec ce nouvel INE
        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $ia */
        $ias = $repo->createQueryBuilder('ia')
            ->andWhere('ia.noCandidat = :noCandidat')->setParameter('noCandidat', $noCandidat)
            ->andWhere('ia.periodeAnneeUniversitaire = :periodeAnneeUniversitaire')->setParameter('periodeAnneeUniversitaire', $annee)
            ->andWhere('iaa.code = :codeApprenant')->setParameter('codeApprenant', $codeApprenant)
            ->andWhere('iaa.INE = :ine')->setParameter('ine', $ineNew)
            ->getQuery()->getArrayResult();
        $this->assertCount(
            1,
            $ias,
            "Il ne devrait exister qu'1 IA $annee ayant le code apprenant $codeApprenant, le numero candidat $noCandidat et le nouvel ine apprenant $ineNew"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createTestSource(): void
    {
        $testSourceCode = static::$testSourceCode;

        $conn = $this->em()->getConnection();

        $sql = <<<SQL
-- Création d'une source de données de test
insert into source (id, code, libelle, importable, synchro_delete_enabled, etablissement_id)
select nextval('source_id_seq'), '$testSourceCode', 'Instance Pégase UCN de Test', true, false/*delete interdit*/, e.id 
from etablissement e where e.source_code = 'UCN'
on conflict do nothing;
SQL;
        $conn->executeQuery($sql);

        $sql = "select id from source where code = '$testSourceCode'";
        static::$testSourceId = $conn->executeQuery($sql)->fetchNumeric()[0];
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    protected function deleteRecordsInTestSource(): void
    {
        $sqls = [
            sprintf("delete from tmp_inscription_administrative_apprenant where source_id = %d", static::$testSourceId),
            sprintf("delete from tmp_inscription_administrative where source_id = %d", static::$testSourceId),
//            sprintf("delete from tmp_doctorant where source_id = %s", static::$testSourceId),
//            sprintf("delete from tmp_individu where source_id = %s", static::$testSourceId),

            sprintf("delete from inscription_administrative_apprenant where source_id = %d", static::$testSourceId),
            sprintf("delete from inscription_administrative where source_id = %d", static::$testSourceId),
//            sprintf("delete from substit_doctorant si where exists (select from doctorant i where i.id = si.from_id and i.source_id = %s)", static::$testSourceId),
//            sprintf("delete from doctorant where source_id = %s", static::$testSourceId),
//            sprintf("delete from substit_individu si where exists (select from individu i where i.id = si.from_id and i.source_id = %s)", static::$testSourceId),
//            sprintf("delete from individu where source_id = %s", static::$testSourceId),
            sprintf("delete from source where id = %s", static::$testSourceId),
        ];
        foreach ($sqls as $sql) {
            $this->em()->getConnection()->executeQuery($sql);
            $this->em()->flush();
        }
    }
}