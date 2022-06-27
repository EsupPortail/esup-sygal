<?php

namespace ImportTest\Service;

use Structure\Entity\Db\Etablissement;
use Import\Service\DbServiceJSONHelper;

class DbServiceJSONHelperTest extends \PHPUnit_Framework_TestCase
{
    public function getPropertyNames()
    {
        return [
            // property name,         expected value
            ['etablissementId',       'ETAB'],
            ['sourceCode',            'ETAB::ID'],

            ['sourceId',              'ETAB::SOURCEID'],
            ['individuId',            'ETAB::INDIVIDUID'],
            ['roleId',                'ETAB::ROLEID'],
            ['theseId',               'ETAB::THESEID'],
            ['doctorantId',           'ETAB::DOCTORANTID'],
            ['structureId',           'ETAB::STRUCTUREID'],
            ['ecoleDoctId',           'ETAB::ECOLEDOCTID'],
            ['uniteRechId',           'ETAB::UNITERECHID'],
            ['acteurEtablissementId', 'ETAB::ACTEURETABLISSEMENTID'],

            ['origineFinancementId',  'COMUE::ORIGINEFINANCEMENTID'],

            ['autre',                 'AUTRE'],

            ['unknown',               null],
        ];
    }

    /**
     * @param string $propertyName
     * @param string $expectedValue
     * @dataProvider getPropertyNames
     */
    public function testExtractPropertyValue($propertyName, $expectedValue)
    {
        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $etablissement = $this->createMock(Etablissement::class);
        $etablissement->expects($this->any())->method('getCode')->willReturn('ETAB');

        $jsonEntity = new \stdClass();
        $jsonEntity->{'id'}                    = 'ID';
        $jsonEntity->{'etablissementId'}       = 'ETABLISSEMENTID';
        $jsonEntity->{'sourceCode'}            = 'SOURCECODE';
        $jsonEntity->{'sourceId'}              = 'SOURCEID';
        $jsonEntity->{'individuId'}            = 'INDIVIDUID';
        $jsonEntity->{'roleId'}                = 'ROLEID';
        $jsonEntity->{'theseId'}               = 'THESEID';
        $jsonEntity->{'doctorantId'}           = 'DOCTORANTID';
        $jsonEntity->{'structureId'}           = 'STRUCTUREID';
        $jsonEntity->{'ecoleDoctId'}           = 'ECOLEDOCTID';
        $jsonEntity->{'uniteRechId'}           = 'UNITERECHID';
        $jsonEntity->{'acteurEtablissementId'} = 'ACTEURETABLISSEMENTID';
        $jsonEntity->{'origineFinancementId'}  = 'ORIGINEFINANCEMENTID';
        $jsonEntity->{'autre'}                 = 'AUTRE';

        $helper = new DbServiceJSONHelper();
        $helper->setEtablissement($etablissement);
        $value = $helper->extractPropertyValue($propertyName, $jsonEntity);

        $this->assertEquals($expectedValue, $value);
    }
}
