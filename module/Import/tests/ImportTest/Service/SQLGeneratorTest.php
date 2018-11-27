<?php

namespace ImportTest\Service;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Import\Service\SQLGenerator;

class SQLGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SQLGenerator
     */
    private $sqlGenerator;

    protected function setUp()
    {
        $this->sqlGenerator = new SQLGenerator();
    }

    public function test_generateSQLQueryForClearingExistingData()
    {
        $filters = [];
        $sql = $this->sqlGenerator->generateSQLQueryForClearingExistingData('table_basse', $filters);
        $this->assertEquals("DELETE FROM table_basse", $sql);

        $filters = ['tiroir_id' => 1234];
        $sql = $this->sqlGenerator->generateSQLQueryForClearingExistingData('table_basse', $filters);
        $this->assertEquals("DELETE FROM table_basse WHERE TIROIR_ID = '1234'", $sql);

        $filters = ['tiroir_id' => 1234, 'couleur' => 'bleue'];
        $sql = $this->sqlGenerator->generateSQLQueryForClearingExistingData('table_basse', $filters);
        $this->assertEquals("DELETE FROM table_basse WHERE TIROIR_ID = '1234' AND COULEUR = 'bleue'", $sql);
    }

    public function test_generateSQLQueryForSavingData()
    {
        $tableColumns = ['largeur', 'hauteur', 'couleur'];
        $columnsValues = [12, '30', "'bleue'"];
        $sql = $this->sqlGenerator->generateSQLQueryForSavingData('table_basse', $tableColumns, $columnsValues);
        $this->assertEquals("INSERT INTO table_basse (largeur, hauteur, couleur) VALUES (12, 30, 'bleue')", $sql);
    }

    public function test_wrapSQLQueriesInBeginEnd()
    {
        $queries = ['insert ceci', 'insert cela'];
        $sql = $this->sqlGenerator->wrapSQLQueriesInBeginEnd($queries);
        $expected = <<<EOS
BEGIN
  insert ceci;
  insert cela;
END;
EOS;
        $this->assertEquals($expected, $sql);
    }

    public function getDataset()
    {
        $date = (new \stdClass());
        $date->date = '2018-11-15 08:00:00';

        return [
            [null,      'string',   "'quoted'"],
            ['valeur',  'string',   "'quoted'"],
            [12,        'string',   "'quoted'"],
            [null,      'date',     "NULL"],
            [$date,     'date',     "TO_DATE('2018-11-15', 'YYYY-MM-DD')"],
            ['valeur',  '??????',   'valeur'],
            [12,        '??????',   12],
        ];
    }

    /**
     * @param $value
     * @param $type
     * @param $expected
     * @dataProvider getDataset
     */
    public function test_formatValueForPropertyType($value, $type, $expected)
    {
        /** @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $platform */
        $platform = $this->createMock(AbstractPlatform::class);
        $this->sqlGenerator->setDatabasePlatform($platform);

        $platform->expects($this->any())->method('quoteStringLiteral')->with($value)->willReturn("quoted");
        $res = $this->sqlGenerator->formatValueForPropertyType($value, $type);
        $this->assertEquals($expected, $res);
    }
}
