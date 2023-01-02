<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\DataSource;

use Doctrine\SkeletonMapper\DataSource\CriteriaMatcher;
use PHPUnit\Framework\TestCase;

class CriteriaMatcherTest extends TestCase
{
    public function testEqualsTrue(): void
    {
        $criteriaMatcher = new CriteriaMatcher(
            ['username' => 'jwage'],
            ['username' => 'jwage'],
        );

        self::assertTrue($criteriaMatcher->matches());
    }

    public function testEqualsFalse(): void
    {
        $criteriaMatcher = new CriteriaMatcher(
            ['username' => 'jwage'],
            ['username' => 'jonwage'],
        );

        self::assertFalse($criteriaMatcher->matches());
    }

    public function testContainsTrue(): void
    {
        $criteriaMatcher = new CriteriaMatcher(
            ['projects' => ['$contains' => 'dbal']],
            ['projects' => ['orm', 'dbal']],
        );

        self::assertTrue($criteriaMatcher->matches());
    }

    public function testContainsFalse(): void
    {
        $criteriaMatcher = new CriteriaMatcher(
            ['projects' => ['$contains' => 'mongodb-odm']],
            ['projects' => ['orm', 'dbal']],
        );

        self::assertFalse($criteriaMatcher->matches());
    }
}
