<?php

namespace ApplicationTest\Rule;

use These\Entity\Db\These;
use Depot\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use DateTime;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class NotificationDepotVersionCorrigeeAttenduRuleTest extends \PHPUnit_Framework_TestCase
{
    public function test_retourne_date_null_si_date_butoire_null()
    {
        $correctionAutorisee = These::CORRECTION_AUTORISEE_FACULTATIVE;

        $these = $this->theseMock($correctionAutorisee, $dateButoir = null);

        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule->setThese($these);
        $rule->setDateAujourdhui(new DateTime());
        $rule->execute();

        $this->assertNull($rule->getDateProchaineNotif());
        $this->assertTrue($rule->estPremiereNotif());
    }

    public function getDataSetMineure()
    {
        $aujourdhui = $this->zeroTimeDate('05/03/2017');

        return [
            'date butoir dépassée, 1ere notif' => [
                $aujourdhui,
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('01/03/2017'),
                //
                $expectedDateProchaineNotif = null, // notif inutile
                $expectedEstPremiereNotif = true,
            ],

            'date butoir dépassée, pas 1ere notif' => [
                $aujourdhui,
                $dateDerniereNotif = $this->zeroTimeDate('01/02/2017'),
                $dateButoir        = $this->zeroTimeDate('01/03/2017'),
                //
                $expectedDateProchaineNotif = null, // notif inutile
                $expectedEstPremiereNotif = false,
            ],

            'date butoir non dépassée, 1ere notif' => [
                $aujourdhui,
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('11/05/2017'),
                //
                $expectedDateProchaineNotif = $aujourdhui,
                $expectedEstPremiereNotif = true,
            ],

            'date butoir non dépassée, pas 1ere notif' => [
                $aujourdhui,
                $dateDerniereNotif = $this->zeroTimeDate('01/02/2017'),
                $dateButoir        = $this->zeroTimeDate('11/05/2017'),
                //
                $expectedDateProchaineNotif = null,
                $expectedEstPremiereNotif = false,
            ],

            'notif dejà faite' => [
                $aujourdhui,
                $dateDerniereNotif = $this->zeroTimeDate('01/03/2017'),
                $dateButoir        = $this->zeroTimeDate('11/05/2017'),
                //
                $expectedDateProchaineNotif = null, // plus de notif
                $expectedEstPremiereNotif = false,
            ],
        ];
    }

    /**
     * @param DateTime $aujourdhui
     * @param DateTime $dateDerniereNotif
     * @param DateTime $dateButoir
     * @param DateTime $expectedDateProchaineNotif
     * @param bool      $expectedEstPremiereNotif
     *
     * @dataProvider getDataSetMineure
     */
    public function test_correction_mineure($aujourdhui, $dateDerniereNotif, $dateButoir, $expectedDateProchaineNotif, $expectedEstPremiereNotif)
    {
        $correctionAutorisee = These::CORRECTION_AUTORISEE_FACULTATIVE;

        $these = $this->theseMock($correctionAutorisee, $dateButoir);

        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule->setThese($these);
        $rule->setDateDerniereNotif($dateDerniereNotif);
        $rule->setDateAujourdhui($aujourdhui);
        $rule->execute();

        $this->assertEquals($expectedDateProchaineNotif, $rule->getDateProchaineNotif());
        $this->assertEquals($expectedEstPremiereNotif, $rule->estPremiereNotif());
    }

    /**
     * @param string    $correctionAutorisee
     * @param DateTime $dateButoir
     * @return These
     */
    private function theseMock($correctionAutorisee, $dateButoir)
    {
        $theseProphecy = $this->theseProphecy();
        $theseProphecy->getCorrectionAutorisee()->willReturn($correctionAutorisee);
        $theseProphecy->getDateButoirDepotVersionCorrigeeFromDateSoutenance($theseProphecy->getDateSoutenance())->willReturn($dateButoir);
        $these = $theseProphecy->reveal();

        return $these;
    }

    public function test_interval_entre_date_notif_et_butoire_est_toujours_1_mois()
    {
        $this->assertEquals(
            'P1M',
            NotificationDepotVersionCorrigeeAttenduRule::SPEC_INTERVAL_ENTRE_DATE_NOTIF_ET_BUTOIRE,
            "L'interval entre la date de notif et la date butoire n'est plus 1 mois, il faut corriger les tests " .
            "concernant la correction majeure");
    }

    public function getDataSetMajeure()
    {
        $aujourdhui = $this->zeroTimeDate('05/03/2017');

        return [
            'date butoir dépassée, 1ere notif' => [
                $aujourdhui,
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('01/03/2017'),
                //
                $expectedDateProchaineNotif = null, // notif inutile
                $expectedEstPremiereNotif = true,
            ],

            'date butoir dépassée, 1ere notif faite' => [
                $aujourdhui,
                $dateDerniereNotif = $this->zeroTimeDate('01/02/2017'),
                $dateButoir        = $this->zeroTimeDate('01/03/2017'),
                //
                $expectedDateProchaineNotif = null, // notif inutile
                $expectedEstPremiereNotif = false,
            ],

            '1ere notif, aujourdhui < date de prochaine notif theorique' => [
                $aujourdhui,
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = $aujourdhui, // 1ere notif aujourdhui
                $expectedEstPremiereNotif = true,
            ],

            '1ere notif faite, aujourdhui < date de prochaine notif theorique' => [
                $aujourdhui,
                $dateDerniereNotif = $this->zeroTimeDate('01/03/2017'),
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = $this->zeroTimeDate('11/03/2017'), // 1 mois avant la date butoire
                $expectedEstPremiereNotif = false,
            ],

            '1ere notif, aujourdhui == date de prochaine notif theorique' => [
                $aujourdhui        = $this->zeroTimeDate('11/03/2017'),
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = $aujourdhui,
                $expectedEstPremiereNotif = true,
            ],

            '1ere notif faite, aujourdhui == date de prochaine notif theorique' => [
                $aujourdhui        = $this->zeroTimeDate('11/03/2017'),
                $dateDerniereNotif = $this->zeroTimeDate('01/03/2017'),
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = $aujourdhui,
                $expectedEstPremiereNotif = false,
            ],

            '1ere notif, aujourdhui > date de prochaine notif theorique' => [
                $aujourdhui        = $this->zeroTimeDate('20/03/2017'),
                $dateDerniereNotif = null,
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = $aujourdhui,
                $expectedEstPremiereNotif = true,
            ],

            '1ere notif faite, aujourdhui > date de prochaine notif theorique' => [
                $aujourdhui        = $this->zeroTimeDate('20/03/2017'),
                $dateDerniereNotif = $this->zeroTimeDate('01/03/2017'),
                $dateButoir        = $this->zeroTimeDate('11/04/2017'),
                //
                $expectedDateProchaineNotif = null,
                $expectedEstPremiereNotif = false,
            ],
        ];
    }

    /**
     * @param DateTime $aujourdhui
     * @param DateTime $dateDerniereNotif
     * @param DateTime $dateButoir
     * @param DateTime $expectedDateProchaineNotif
     * @param bool      $expectedEstPremiereNotif
     *
     * @dataProvider getDataSetMajeure
     */
    public function test_correction_majeure($aujourdhui, $dateDerniereNotif, $dateButoir, $expectedDateProchaineNotif, $expectedEstPremiereNotif)
    {
        $correctionAutorisee = These::CORRECTION_AUTORISEE_OBLIGATOIRE;

        $these = $this->theseMock($correctionAutorisee, $dateButoir);

        $rule = new \Depot\Rule\NotificationDepotVersionCorrigeeAttenduRule();
        $rule->setThese($these);
        $rule->setDateDerniereNotif($dateDerniereNotif);
        $rule->setDateAujourdhui($aujourdhui);
        $rule->execute();

        $this->assertEquals($expectedDateProchaineNotif, $rule->getDateProchaineNotif());
        $this->assertEquals($expectedEstPremiereNotif, $rule->estPremiereNotif());
    }

    /**
     * @return These|ObjectProphecy
     */
    private function theseProphecy()
    {
        $prophet = new Prophet();

        /** @var These|ObjectProphecy $prophecy */
        $prophecy = $prophet->prophesize(These::class);

        return $prophecy;
    }

    /**
     * @param string|DateTime $date
     * @return DateTime|null
     */
    private function zeroTimeDate($date)
    {
        if ($date === null) {
            return null;
        }
        if (is_string($date)) {
            $date = DateTime::createFromFormat('d/m/Y', $date);
        }

        return $date->setTime(0, 0, 0);
    }
}
