<?php

namespace DepotUnitTest\Rule;

use DateTime;
use Depot\Entity\Db\Diffusion;
use Depot\Feature\FournirExemplairePapierTheseFeature;
use Depot\Rule\RemiseExemplairePapierRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RemiseExemplairePapierTest extends TestCase
{
    public function test_execute_returns_this()
    {
        $rule = new RemiseExemplairePapierRule();

        self::assertSame($rule->execute(), $rule);
    }

    public static function isRemiseExemplairePapierRequiredProvider(): array
    {
        return [
            'autoris: oui' => [
                Diffusion::AUTORISATION_OUI_IMMEDIAT,
                false,
            ],
            'autoris: oui+emb' => [
                Diffusion::AUTORISATION_OUI_EMBARGO,
                true,
            ],
            'autoris: non' => [
                Diffusion::AUTORISATION_NON,
                true,
            ],
        ];
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('isRemiseExemplairePapierRequiredProvider')]
    public function test_isRemiseExemplairePapierRequired_when_feature_enabled(int $autorisMel, bool $expected)
    {
        $rule = new RemiseExemplairePapierRule();

        /** @var Diffusion $diffusion */
        $diffusion = self::createMock(Diffusion::class);
        $diffusion->method('getAutorisMel')->willReturn($autorisMel);
        $rule->setDiffusion($diffusion);

        /** @var FournirExemplairePapierTheseFeature $feature */
        $feature = self::createMock(FournirExemplairePapierTheseFeature::class);
        $feature->method('getCode')->willReturn(FournirExemplairePapierTheseFeature::CODE);
        $rule->addFeature($feature);

        // feature activée
        $feature->method('isEnabled')->willReturn(true);

        $dateObservation = new DateTime();

        self::assertSame($rule->isRemiseExemplairePapierRequired($dateObservation), $expected);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('isRemiseExemplairePapierRequiredProvider')]
    public function test_isRemiseExemplairePapierRequired_when_feature_disabled(int $autorisMel, bool $expected)
    {
        $rule = new RemiseExemplairePapierRule();

        /** @var Diffusion $diffusion */
        $diffusion = self::createMock(Diffusion::class);
        $diffusion->method('getAutorisMel')->willReturn($autorisMel);
        $rule->setDiffusion($diffusion);

        /** @var FournirExemplairePapierTheseFeature $feature */
        $feature = self::createMock(FournirExemplairePapierTheseFeature::class);
        $feature->method('getCode')->willReturn(FournirExemplairePapierTheseFeature::CODE);
        $rule->addFeature($feature);

        // feature désactivée
        $feature->method('isEnabled')->willReturn(false);

        $dateObservation = new DateTime();

        self::assertSame($rule->isRemiseExemplairePapierRequired($dateObservation), false);
    }

    public static function isChangementAutorisMelImpacteRemiseExemplairePapierProvider(): array
    {
        return [
            'autoris: oui => oui' => [
                Diffusion::AUTORISATION_OUI_IMMEDIAT, // from
                Diffusion::AUTORISATION_OUI_IMMEDIAT, // to
                false // attendu
            ],
            'autoris: oui => oui+emb' => [
                Diffusion::AUTORISATION_OUI_IMMEDIAT,
                Diffusion::AUTORISATION_OUI_EMBARGO,
                true
            ],
            'autoris: oui => non' => [
                Diffusion::AUTORISATION_OUI_IMMEDIAT,
                Diffusion::AUTORISATION_NON,
                true
            ],
            'autoris: oui+emb => oui' => [
                Diffusion::AUTORISATION_OUI_EMBARGO,
                Diffusion::AUTORISATION_OUI_IMMEDIAT,
                true
            ],
            'autoris: oui+emb => oui+emb' => [
                Diffusion::AUTORISATION_OUI_EMBARGO,
                Diffusion::AUTORISATION_OUI_EMBARGO,
                false
            ],
            'autoris: oui+emb => non' => [
                Diffusion::AUTORISATION_OUI_EMBARGO,
                Diffusion::AUTORISATION_NON,
                false
            ],
            'autoris: non => oui' => [
                Diffusion::AUTORISATION_NON,
                Diffusion::AUTORISATION_OUI_IMMEDIAT,
                true
            ],
            'autoris: non => oui+emb' => [
                Diffusion::AUTORISATION_NON,
                Diffusion::AUTORISATION_OUI_EMBARGO,
                false
            ],
            'autoris: non => non' => [
                Diffusion::AUTORISATION_NON,
                Diffusion::AUTORISATION_NON,
                false
            ],
        ];
    }

    #[DataProvider('isChangementAutorisMelImpacteRemiseExemplairePapierProvider')]
    public function test_isChangementAutorisMelImpacteRemiseExemplairePapier(int $autorisMelOld,
                                                                             int $autorisMelNew,
                                                                             bool $expected)
    {
        $rule = new RemiseExemplairePapierRule();

        self::assertSame(
            $rule->isChangementAutorisMelImpacteRemiseExemplairePapier($autorisMelOld, $autorisMelNew),
            $expected
        );
    }
}
