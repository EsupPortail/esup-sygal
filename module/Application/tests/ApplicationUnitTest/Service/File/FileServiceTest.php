<?php

namespace ApplicationUnitTest\Service\File;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\UniteRecherche;
use Application\Service\File\FileService;
use UnicaenApp\Exception\RuntimeException;

class FileServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileService
     */
    private $fileService;

    protected function setUp()
    {
        $this->fileService = new FileService();
        $this->fileService->setRootDirectoryPathForUploadedFiles('/root/directory/path');
    }

    public function testCanPrependUploadRootDirToRelativePath()
    {
        $path = $this->fileService->prependUploadRootDirToRelativePath('relative/path/to/somewhere');

        $this->assertEquals('/root/directory/path/relative/path/to/somewhere', $path);
    }

    public function testCanComputeLogoFilenameForEtablissementHavingCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn('CODE');

        $filename = $this->fileService->computeLogoFilenameForStructure($etablissement);

        $this->assertEquals('CODE.png', $filename);
    }

    public function testCanComputeLogoFilenameForEtablissementHavingNoCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn(null);
        $etablissement->expects($this->once())->method('generateUniqCode')->willReturn('UNIQID');

        $filename = $this->fileService->computeLogoFilenameForStructure($etablissement);

        $this->assertEquals('UNIQID.png', $filename);
    }

    public function testCanComputeLogoFilenameForOtherStructure()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $structure */
        $structure = $this->createMock(StructureConcreteInterface::class);
        $structure->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $structure->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $filename = $this->fileService->computeLogoFilenameForStructure($structure);

        $this->assertEquals('SOURCE_CODE-SIGLE.png', $filename);
    }

    public function testCanComputeLogoFilepathForEcoleDoctorale()
    {
        /** @var EcoleDoctorale|\PHPUnit_Framework_MockObject_MockObject $ecoleDoctorale */
        $ecoleDoctorale = $this->createMock(EcoleDoctorale::class);
        $ecoleDoctorale->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $ecoleDoctorale->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $path = $this->fileService->computeLogoFilepathForStructure($ecoleDoctorale);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/ED' . '/SOURCE_CODE-SIGLE.png', $path);
    }

    public function testCanComputeLogoFilepathForUniteRecherche()
    {
        /** @var UniteRecherche|\PHPUnit_Framework_MockObject_MockObject $uniteRecherche */
        $uniteRecherche = $this->createMock(UniteRecherche::class);
        $uniteRecherche->expects($this->once())->method('getSourceCode')->willReturn('SOURCE_CODE');
        $uniteRecherche->expects($this->once())->method('getSigle')->willReturn('SIGLE');

        $path = $this->fileService->computeLogoFilepathForStructure($uniteRecherche);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/UR' . '/SOURCE_CODE-SIGLE.png', $path);
    }

    public function testCanComputeLogoFilepathForEtablissementHavingCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn('CODE');

        $path = $this->fileService->computeLogoFilepathForStructure($etablissement);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/Etab' . '/CODE.png', $path);
    }

    public function testCanComputeLogoFilepathForEtablissementHavingNoCode()
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->once())->method('getCode')->willReturn(null);
        $etablissement->expects($this->once())->method('generateUniqCode')->willReturn('UNIQID');

        $path = $this->fileService->computeLogoFilepathForStructure($etablissement);

        $this->assertEquals('/root/directory/path' . '/ressources/Logos' . '/Etab' . '/UNIQID.png', $path);
    }

    public function testComputeLogoFilepathForUnexpectedStructureThrowsException()
    {
        /** @var StructureConcreteInterface|\PHPUnit_Framework_MockObject_MockObject $structure */
        $structure = $this->createMock(StructureConcreteInterface::class);

        $this->expectException(RuntimeException::class);

        $this->fileService->computeLogoFilepathForStructure($structure);
    }
}
