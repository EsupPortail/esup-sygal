<?php

namespace DepotUnitTest\Filter;

use Depot\Filter\MotsClesFilter;

class MotsClesFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MotsClesFilter
     */
    protected $filter;

    protected function setUp(): void
    {
        $this->filter = new MotsClesFilter(['separator' => ';']);
    }

    public function getDataset()
    {
        return [
            [
                "one",
                "one",
            ],
            [
                " one  ",
                "one",
            ],
            [
                "one;",
                "one",
            ],
            [
                "one;two",
                "one ; two",
            ],
            [
                " one   ; two  ",
                "one ; two",
            ],
            [
                ["one", "two"],
                "one ; two",
            ],
            [
                [" one; ", "two"],
                "one ; two",
            ],
        ];
    }

    /**
     * @dataProvider getDataset
     * @param string|string[] $value
     * @param string          $expected
     */
    public function test_filtering($value, $expected)
    {
        $this->assertEquals($expected, $this->filter->filter($value));
    }

    /**
     * @expectedException \LogicException
     */
    public function test_unexpected_input_value_throws_exception()
    {
        $this->filter->filter(12);
    }
}
