<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Collections\LazyCollection;
use PHPUnit\Framework\TestCase;

class LazyCollectionTest extends TestCase
{
    public function testLoad(): void
    {
        $wrappedCollection = new ArrayCollection();

        /** @return ArrayCollection<mixed, mixed> $collection */
        $collection = new LazyCollection(static function () use ($wrappedCollection): ArrayCollection {
            return $wrappedCollection;
        });

        self::assertSame($wrappedCollection, $collection->getCollection());
    }
}
