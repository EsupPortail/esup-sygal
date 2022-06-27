<?php

namespace ApplicationUnitTest\Service\File;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\UniteRecherche;
use Fichier\Service\Fichier\FichierStorageService;
use UnicaenApp\Exception\RuntimeException;

class FileServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Fichier\Service\Fichier\FichierStorageService
     */
    private $fileService;

    protected function setUp()
    {
        $this->fileService = new \Fichier\Service\Fichier\FichierStorageService();
//        $this->fileService->setRootDirectoryPathForUploadedFiles('/root/directory/path');
    }

    public function testCanPrependUploadRootDirToRelativePath()
    {
        $path = $this->fileService->computeStorageDirectoryPath('relative/path/to/somewhere');

        $this->assertEquals('/root/directory/path/relative/path/to/somewhere', $path);
    }

    public function testCanComputeLogoFilenameForEtablissementHavingCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn('CODE');

        $filename = $this->fileService->computeFileNameForLogoStructure($etablissement);

        $this->assertEquals('CODE.png', $filename);
    }

    public function testCanComputeLogoFilenameForEtablissementHavingNoCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn(null);
        $etablissement->expects($this->once())->method('generateUniqCode')->willReturn('UNIQID');

        $filename = $this->fileService->computeFileNameForLogoStructure($etablissement);

        $this->assertEquals('UNIQID.png', $filename);
    }

    public function testCanComputeLogoFilenameForOtherStructure()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $structure */
        $structure = $this->createMock(StructureConcreteInterface::class);
        $structure->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $structure->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $filename = $this->fileService->computeFileNameForLogoStructure($structure);

        $this->assertEquals('SOURCE_CODE-SIGLE.png', $filename);
    }

    public function testCanComputeLogoDirectoryPathForEcoleDoctorale()
    {
        /** @var EcoleDoctorale|\PHPUnit_Framework_MockObject_MockObject $ecoleDoctorale */
        $ecoleDoctorale = $this->createMock(EcoleDoctorale::class);

        $path = $this->fileService->computeDirectoryPathForLogoStructure($ecoleDoctorale);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/ED', $path);
    }

    public function testCanComputeLogoDirectoryPathForUniteRecherche()
    {
        /** @var UniteRecherche|\PHPUnit_Framework_MockObject_MockObject $uniteRecherche */
        $uniteRecherche = $this->createMock(UniteRecherche::class);

        $path = $this->fileService->computeDirectoryPathForLogoStructure($uniteRecherche);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/UR', $path);
    }

    public function testCanComputeLogoDirectoryPathForEtablissement()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);

        $path = $this->fileService->computeDirectoryPathForLogoStructure($etablissement);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/Etab', $path);
    }

    public function testCanComputeLogoFilepathForEcoleDoctorale()
    {
        /** @var \Fichier\Service\Fichier\FichierStorageService|\PHPUnit_Framework_MockObject_MockObject $fileService */
        $fileService = $this->getMockBuilder(\Fichier\Service\Fichier\FichierStorageService::class)
            ->setMethods(['computeLogoDirectoryPathForStructure'])
            ->getMock();
        $fileService->expects($this->once())->method('computeLogoDirectoryPathForStructure')->willReturn('LOGO_DIR_PATH');

        /** @var EcoleDoctorale|\PHPUnit_Framework_MockObject_MockObject $ecoleDoctorale */
        $ecoleDoctorale = $this->createMock(EcoleDoctorale::class);
        $ecoleDoctorale->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $ecoleDoctorale->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $path = $fileService->computeLogoFilePathForStructure($ecoleDoctorale);

        $this->assertEquals('LOGO_DIR_PATH' . '/SOURCE_CODE-SIGLE.png', $path);
    }

    public function testCanComputeLogoFilepathForUniteRecherche()
    {
        /** @var \Fichier\Service\Fichier\FichierStorageService|\PHPUnit_Framework_MockObject_MockObject $fileService */
        $fileService = $this->getMockBuilder(\Fichier\Service\Fichier\FichierStorageService::class)
            ->setMethods(['computeLogoDirectoryPathForStructure'])
            ->getMock();
        $fileService->expects($this->once())->method('computeLogoDirectoryPathForStructure')->willReturn('LOGO_DIR_PATH');

        /** @var UniteRecherche|\PHPUnit_Framework_MockObject_MockObject $uniteRecherche */
        $uniteRecherche = $this->createMock(UniteRecherche::class);
        $uniteRecherche->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $uniteRecherche->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $path = $fileService->computeLogoFilePathForStructure($uniteRecherche);

        $this->assertEquals('LOGO_DIR_PATH' . '/SOURCE_CODE-SIGLE.png', $path);
    }

    public function testCanComputeLogoFilepathForEtablissementHavingCode()
    {
        /** @var \Fichier\Service\Fichier\FichierStorageService|\PHPUnit_Framework_MockObject_MockObject $fileService */
        $fileService = $this->getMockBuilder(\Fichier\Service\Fichier\FichierStorageService::class)
            ->setMethods(['computeLogoDirectoryPathForStructure'])
            ->getMock();
        $fileService->expects($this->once())->method('computeLogoDirectoryPathForStructure')->willReturn('LOGO_DIR_PATH');

        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn('CODE');

        $path = $fileService->computeLogoFilePathForStructure($etablissement);

        $this->assertEquals('LOGO_DIR_PATH' . '/CODE.png', $path);
    }

    public function testCanComputeLogoFilepathForEtablissementHavingNoCode()
    {
        /** @var FichierStorageService|\PHPUnit_Framework_MockObject_MockObject $fileService */
        $fileService = $this->getMockBuilder(\Fichier\Service\Fichier\FichierStorageService::class)
            ->setMethods(['computeLogoDirectoryPathForStructure'])
            ->getMock();
        $fileService->expects($this->once())->method('computeLogoDirectoryPathForStructure')->willReturn('LOGO_DIR_PATH');

        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn(null);
        $etablissement->expects($this->once())->method('generateUniqCode')->willReturn('UNIQID');

        $path = $fileService->computeLogoFilePathForStructure($etablissement);

        $this->assertEquals('LOGO_DIR_PATH' . '/UNIQID.png', $path);
    }

    public function testComputeLogoFilepathForUnexpectedStructureThrowsException()
    {
        /** @var StructureConcreteInterface|\PHPUnit_Framework_MockObject_MockObject $structure */
        $structure = $this->createMock(StructureConcreteInterface::class);

        $this->expectException(RuntimeException::class);

        $this->fileService->computeLogoFilePathForStructure($structure);
    }
}
