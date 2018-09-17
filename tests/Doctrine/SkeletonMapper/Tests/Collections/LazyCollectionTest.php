<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Collections\LazyCollection;
use PHPUnit\Framework\TestCase;

class LazyCollectionTest extends TestCase
{
    public function testLoad()
    {
        $wrappedCollection = new ArrayCollection();

        $collection = new LazyCollection(function() use ($wrappedCollection) {
            return $wrappedCollection;
        });

        $this->assertSame($wrappedCollection, $collection->getCollection());
    }
}
