<?php

namespace ApplicationUnitTest\Service\ImportObservResultService;

use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Application\Entity\Db\These;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\ImportObservResult\ImportObservResultService;
use Application\Service\Notification\NotificationService;
use Application\Service\These\TheseService;
use ApplicationUnitTest\Test\Asset\EntityAsset;
use ApplicationUnitTest\Test\Provider\MockProviderAwareTrait;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;

class NotifCorrectionAttendueTest extends \PHPUnit_Framework_TestCase
{
    use MockProviderAwareTrait;

    /**
     * @var ImportObservResultService
     */
    protected $service;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|NotificationDepotVersionCorrigeeAttenduRule
     */
    protected $ruleMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|NotificationService
     */
    protected $notificationServiceMock;


    public function getTypeCorrectionAttendue()
    {
        return [
            [These::CORRECTION_MINEURE],
            [These::CORRECTION_MAJEURE],
        ];
    }

    /**
     * @dataProvider getTypeCorrectionAttendue
     * @param string $typeCorrectionAttendue
     */
    public function test_notifie_si_la_regle_renvoie_aujourdhui($typeCorrectionAttendue)
    {
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn($aujourdhui);
        $this->notificationServiceMock
            ->expects($this->exactly(1)) // 1 fois par record
            ->method('notifierCorrectionAttendue');

        if ($typeCorrectionAttendue === These::CORRECTION_MINEURE) {
            $this->service->handleImportObservResultsForCorrectionMineure();
        }
        else {
            $this->service->handleImportObservResultsForCorrectionMajeure();
        }

        $this->assertEquals($aujourdhui, $record->getDateNotif()->setTime(0, 0, 0));
    }

    /**
     * @dataProvider getTypeCorrectionAttendue
     * @param string $typeCorrectionAttendue
     */
    public function test_ne_notifie_pas_si_la_regle_renvoie_une_date_autre_qu_aujourdhui($typeCorrectionAttendue)
    {
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0
        $hier = new DateTime('yesterday'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn($hier);
        $this->notificationServiceMock
            ->expects($this->never())
            ->method('notifierCorrectionAttendue');

        if ($typeCorrectionAttendue === These::CORRECTION_MINEURE) {
            $this->service->handleImportObservResultsForCorrectionMineure();
        }
        else {
            $this->service->handleImportObservResultsForCorrectionMajeure();
        }

        $this->assertNotEquals($aujourdhui, $record->getDateNotif());
    }

    /**
     * @dataProvider getTypeCorrectionAttendue
     * @param string $typeCorrectionAttendue
     */
    public function test_ne_notifie_pas_si_la_regle_renvoie_une_date_null($typeCorrectionAttendue)
    {
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn(null);
        $this->notificationServiceMock
            ->expects($this->never())
            ->method('notifierCorrectionAttendue');

        if ($typeCorrectionAttendue === These::CORRECTION_MINEURE) {
            $this->service->handleImportObservResultsForCorrectionMineure();
        }
        else {
            $this->service->handleImportObservResultsForCorrectionMajeure();
        }

        $this->assertNotEquals($aujourdhui, $record->getDateNotif());
    }

    public function test_directeurs_de_these_ne_sont_pas_en_copie_de_la_notif_correction_mineure_attendue()
    {
        $typeCorrectionAttendue = These::CORRECTION_MINEURE;
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn($aujourdhui);
        $this->notificationServiceMock
            ->expects($this->exactly(1)) // 1 fois par record
            ->method('notifierCorrectionAttendue')
            ->with($this->anything(), $this->anything(), $directeursTheseEnCopie = $this->isFalse());

        $this->service->handleImportObservResultsForCorrectionMineure();
    }

    public function test_directeurs_de_these_ne_sont_pas_en_copie_de_la_1ere_notif_correction_majeure_attendue()
    {
        $typeCorrectionAttendue = These::CORRECTION_MAJEURE;
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn($aujourdhui);
        $this->ruleMock
            ->method('estPremiereNotif')
            ->willReturn(true);
        $this->notificationServiceMock
            ->expects($this->exactly(1)) // 1 fois par record
            ->method('notifierCorrectionAttendue')
            ->with($this->anything(), $this->anything(), $directeursTheseEnCopie = $this->isFalse());

        $this->service->handleImportObservResultsForCorrectionMajeure();
    }

    public function test_directeurs_de_these_sont_en_copie_de_la_2eme_notif_correction_majeure_attendue()
    {
        $typeCorrectionAttendue = These::CORRECTION_MAJEURE;
        $record = $this->createImportObservResult($typeCorrectionAttendue);
        $aujourdhui = new DateTime('today'); // set time to 0

        $this->_init_test_pour_correction_attendue($record);
        $this->ruleMock
            ->method('getDateProchaineNotif')
            ->willReturn($aujourdhui);
        $this->ruleMock
            ->method('estPremiereNotif')
            ->willReturn(false);
        $this->notificationServiceMock
            ->expects($this->exactly(1)) // 1 fois par record
            ->method('notifierCorrectionAttendue')
            ->with($this->anything(), $this->anything(), $directeursTheseEnCopie = $this->isTrue());

        $this->service->handleImportObservResultsForCorrectionMajeure();
    }


    /**
     * Crée un résultat d'observation montrant que le flag "correction attendue" vient de passer à 'mineure' ou 'majeure'.
     *
     * @param string $typeCorrectionAttendue 'mineure' ou 'majeure'
     * @return ImportObservResult
     */
    protected function createImportObservResult($typeCorrectionAttendue)
    {
        return EntityAsset::newImportObservResult(EntityAsset::newImportObserv()->setToValue($typeCorrectionAttendue));
    }

    /**
     * @param ImportObservResult $record
     * @return ImportObservResultService
     */
    protected function _init_test_pour_correction_attendue(ImportObservResult $record)
    {
        $em = $this->mp()->entityManagerMock();
        $theseService = $this->createTheseServiceMock();
        $this->notificationServiceMock = $this->createNotificationServiceMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|ImportObservResultRepository $repository */
        $repository = $this->mp()->entityRepositoryMock(ImportObservResultRepository::class);
        $repository
            ->method('fetchImportObservResultsForCorrectionMineure')
            ->willReturn([$record]);
        $repository
            ->method('fetchImportObservResultsForCorrectionMajeure')
            ->willReturn([$record]);

        /** @var PHPUnit_Framework_MockObject_MockObject|NotificationDepotVersionCorrigeeAttenduRule $rule */
        $this->ruleMock = $this->getMockBuilder(NotificationDepotVersionCorrigeeAttenduRule::class)
            ->setMethods(['estPremiereNotif', 'getDateProchaineNotif'])
            ->getMock();

        $this->service = new ImportObservResultService();
        $this->service->setEntityManager($em);
        $this->service->setTheseService($theseService);
        $this->service->setNotificationService($this->notificationServiceMock);
        $this->service->setRepository($repository);
        $this->service->setNotificationDepotVersionCorrigeeAttenduRule($this->ruleMock);
    }

    private function createTheseServiceMock()
    {
        $source = EntityAsset::newSource();

        // création de la thèse
        $doctorant = EntityAsset::newDoctorant($source);
        $these = EntityAsset::newThese($doctorant, $source);

        $theseRepository = $this->mp()->entityRepositoryMock();
        $theseRepository
            ->method('findOneBy')
            ->willReturn($these);

        /** @var PHPUnit_Framework_MockObject_MockObject|TheseService $theseService */
        $theseService = $this->mp()->theseServiceMock($theseRepository);

        return $theseService;
    }

    private function createNotificationServiceMock()
    {
        return $this->mp()->notificationServiceMock();
    }
}
