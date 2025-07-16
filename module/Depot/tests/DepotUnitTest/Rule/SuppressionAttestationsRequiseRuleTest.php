<?php

namespace DepotUnitTest\Rule;

use Depot\Entity\Db\Attestation;
use Depot\Entity\Db\Diffusion;
use Depot\Rule\RemiseExemplairePapierRule;
use Depot\Rule\SuppressionAttestationsRequiseRule;
use Fichier\Entity\Db\VersionFichier;
use PHPUnit\Framework\TestCase;
use These\Entity\Db\These;
use UnicaenApp\Exception\LogicException;

class SuppressionAttestationsRequiseRuleTest extends TestCase
{
    public function test_execute_returns_this()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        self::assertSame($rule->execute(), $rule);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_compute_suppression_requise_returns_false_when_no_attestation()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        /** @var These $these */
        $these = self::createMock(These::class);
        $rule->setThese($these);

        /** @var VersionFichier $versionFichier */
        $versionFichier = self::createMock(VersionFichier::class);
        $rule->setVersionFichier($versionFichier);

        // pas d'attetstaion existante
        $these->method('getAttestationForVersion')->willReturn(null);

        self::assertFalse($rule->isSuppressionAttestationRequired());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_compute_suppression_requise_throws_exception_when_no_diffusion()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        /** @var These $these */
        $these = self::createMock(These::class);
        $rule->setThese($these);

        /** @var VersionFichier $versionFichier */
        $versionFichier = self::createMock(VersionFichier::class);
        $rule->setVersionFichier($versionFichier);

        // attestation trouvée
        $these->method('getAttestationForVersion')->willReturn(self::createStub(Attestation::class));

        // aucune diffusion trouvée
        $these->method('getDiffusionForVersion')->willReturn(null);

        self::expectException(LogicException::class);
        $rule->isSuppressionAttestationRequired();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_compute_suppression_requise_returns_true_1()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        /** @var These $these */
        $these = self::createMock(These::class);
        $rule->setThese($these);

        /** @var VersionFichier $versionFichier */
        $versionFichier = self::createMock(VersionFichier::class);
        $rule->setVersionFichier($versionFichier);

        /** @var \Depot\Rule\RemiseExemplairePapierRule $autorisationDiffusionRule */
        $autorisationDiffusionRule = self::createMock(RemiseExemplairePapierRule::class);
        $rule->setRemiseExemplairePapierRule($autorisationDiffusionRule);

        // attestation trouvée
        $attestation = self::createMock(Attestation::class);
        $these->method('getAttestationForVersion')->willReturn($attestation);
        $these->method('getDiffusionForVersion')->willReturn(self::createStub(Diffusion::class));

        // la remise d'un exemplaire papier est requise
        $autorisationDiffusionRule->method('isRemiseExemplairePapierRequired')->willReturn(true);

        // la question "exemplaire papier conforme" n'avait PAS été posée
        $attestation->method('getExemplaireImprimeConformeAVersionDeposee')->willReturn(null /* ou false */);

        self::assertTrue($rule->isSuppressionAttestationRequired());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_compute_suppression_requise_returns_true_2()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        /** @var These $these */
        $these = self::createMock(These::class);
        $rule->setThese($these);

        /** @var VersionFichier $versionFichier */
        $versionFichier = self::createMock(VersionFichier::class);
        $rule->setVersionFichier($versionFichier);

        /** @var \Depot\Rule\RemiseExemplairePapierRule $autorisationDiffusionRule */
        $autorisationDiffusionRule = self::createMock(RemiseExemplairePapierRule::class);
        $rule->setRemiseExemplairePapierRule($autorisationDiffusionRule);

        // attestation trouvée
        $attestation = self::createMock(Attestation::class);
        $these->method('getAttestationForVersion')->willReturn($attestation);
        $these->method('getDiffusionForVersion')->willReturn(self::createStub(Diffusion::class));

        // la remise d'un exemplaire papier est requise
        $autorisationDiffusionRule->method('isRemiseExemplairePapierRequired')->willReturn(false);

        // la question "exemplaire papier conforme" avait été posée
        $attestation->method('getExemplaireImprimeConformeAVersionDeposee')->willReturn(true);

        self::assertTrue($rule->isSuppressionAttestationRequired());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_compute_suppression_requise_returns_false()
    {
        $rule = new SuppressionAttestationsRequiseRule();

        /** @var These $these */
        $these = self::createMock(These::class);
        $rule->setThese($these);

        /** @var VersionFichier $versionFichier */
        $versionFichier = self::createMock(VersionFichier::class);
        $rule->setVersionFichier($versionFichier);

        /** @var \Depot\Rule\RemiseExemplairePapierRule $autorisationDiffusionRule */
        $autorisationDiffusionRule = self::createMock(RemiseExemplairePapierRule::class);
        $rule->setRemiseExemplairePapierRule($autorisationDiffusionRule);

        // attestation trouvée
        $attestation = self::createMock(Attestation::class);
        $these->method('getAttestationForVersion')->willReturn($attestation);

        // aucune diffusion trouvée
        $these->method('getDiffusionForVersion')->willReturn(self::createStub(Diffusion::class));

        // remise exemplaire papier NON requise
        $autorisationDiffusionRule->method('isRemiseExemplairePapierRequired')->willReturn(false);
        // la question "exemplaire papier conforme" avait été posée
        $attestation->method('getExemplaireImprimeConformeAVersionDeposee')->willReturn(true);

        self::assertTrue($rule->isSuppressionAttestationRequired());
    }
}
