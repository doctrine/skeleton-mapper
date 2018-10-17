<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\DataSource;

use Doctrine\SkeletonMapper\DataSource\Sorter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function usort;

class SorterTest extends TestCase
{
    public function testSingleAsc() : void
    {
        $sorter = new Sorter(['numComments' => 'asc']);

        $rows = [
            ['numComments' => 2],
            ['numComments' => 1],
        ];

        usort($rows, $sorter);

        self::assertEquals([
            ['numComments' => 1],
            ['numComments' => 2],
        ], $rows);
    }

    public function testSingleDesc() : void
    {
        $sorter = new Sorter(['numComments' => 'desc']);

        $rows = [
            ['numComments' => 1],
            ['numComments' => 2],
        ];

        usort($rows, $sorter);

        self::assertEquals([
            ['numComments' => 2],
            ['numComments' => 1],
        ], $rows);
    }

    public function testMultipleAsc() : void
    {
        $sorter = new Sorter(['numComments' => 'asc', 'name' => 'asc']);

        $rows = [
            [
                'name' => 'Andreas',
                'numComments' => 1,
            ],
            [
                'name' => 'Marco',
                'numComments' => 2,
            ],
            [
                'name' => 'Jon',
                'numComments' => 2,
            ],
        ];

        usort($rows, $sorter);

        self::assertEquals([
            [
                'name' => 'Andreas',
                'numComments' => 1,
            ],
            [
                'name' => 'Jon',
                'numComments' => 2,
            ],
            [
                'name' => 'Marco',
                'numComments' => 2,
            ],
        ], $rows);
    }

    public function testMultipleDesc() : void
    {
        $sorter = new Sorter(['numComments' => 'desc', 'name' => 'desc']);

        $rows = [
            [
                'name' => 'Andreas',
                'numComments' => 2,
            ],
            [
                'name' => 'Marco',
                'numComments' => 1,
            ],
            [
                'name' => 'Jon',
                'numComments' => 1,
            ],
        ];

        usort($rows, $sorter);

        self::assertEquals([
            [
                'name' => 'Andreas',
                'numComments' => 2,
            ],
            [
                'name' => 'Marco',
                'numComments' => 1,
            ],
            [
                'name' => 'Jon',
                'numComments' => 1,
            ],
        ], $rows);
    }

    public function testMultipleMixed() : void
    {
        $sorter = new Sorter(['numComments' => 'desc', 'name' => 'asc']);

        $rows = [
            [
                'name' => 'Andreas',
                'numComments' => 2,
            ],
            [
                'name' => 'Marco',
                'numComments' => 1,
            ],
            [
                'name' => 'Jon',
                'numComments' => 1,
            ],
        ];

        usort($rows, $sorter);

        self::assertEquals([
            [
                'name' => 'Andreas',
                'numComments' => 2,
            ],
            [
                'name' => 'Jon',
                'numComments' => 1,
            ],
            [
                'name' => 'Marco',
                'numComments' => 1,
            ],
        ], $rows);
    }

    public function testInvalidComparisonFieldThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find comparison field username');

        $sorter = new Sorter(['username' => 'asc']);

        $rows = [
            ['email' => 'test1@example.com'],
            ['email' => 'test2@example.com'],
        ];

        usort($rows, $sorter);
    }

    public function testInvalidOrderThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$order value of invalid is not accepted. Only a value of asc or desc is allowed.');

        $sorter = new Sorter(['username' => 'invalid']);
    }

    public function testEmptyOrderByThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Sorter class does not accept an empty $orderBy');

        $sorter = new Sorter([]);
    }
}
