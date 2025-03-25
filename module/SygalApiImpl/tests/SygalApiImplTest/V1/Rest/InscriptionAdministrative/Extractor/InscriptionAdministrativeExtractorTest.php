<?php

namespace SygalApiImplTest\V1\Rest\InscriptionAdministrative\Extractor;

use Faker\Factory;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InvalidArgumentException;
use Locale;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use SygalApiImpl\V1\Extractor\InscriptionAdministrativeExtractor;
use SygalApiImplTest\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeJsonDataProvider;

class InscriptionAdministrativeExtractorTest extends TestCase
{
    static protected InscriptionAdministrativeJsonDataProvider $jsonDataProvider;

    protected string $jsonOriginalFileName = 'inscr_admin';
    private string $instancePegase = 'PEGAZZZ';

    private InscriptionAdministrativeExtractor $extractor;

    public static function setUpBeforeClass(): void
    {
        self::$jsonDataProvider = new InscriptionAdministrativeJsonDataProvider(__DIR__ . '/../json');
    }

    public function createExtractor(): void
    {
        $this->extractor = new InscriptionAdministrativeExtractor();
    }

    public function getInitialContext(): array
    {
        $faker = Factory::create(Locale::getDefault());

        return [
            'instancePegase' => $this->instancePegase,
            'codeApprenant' => 'AAA',
//            'noCandidat' => 'SYG' . rand(1000, 9999),
            'ine' => rand(100000000, 999999999),
            'annee' => 2024,
            'statutInscription' => InscriptionAdministrative::STATUT_INSCRIPTION_VALIDE,
            'nom' => $faker->lastName(),
            'prenom' => $faker->firstName(),
        ];
    }

    #[TestWith([null])]
    #[TestWith([''])]
    public function test_exception_is_thrown_if_attribut_nocandidat_vide($emptyValue)
    {
        $this->createExtractor();

        $templateParams = $this->getInitialContext();
        $templateParams['noCandidat'] = $emptyValue;
        $data = self::$jsonDataProvider->generateJsonData($templateParams);

        $this->expectException(InvalidArgumentException::class);
        $this->extractor->extract($data);
    }
}