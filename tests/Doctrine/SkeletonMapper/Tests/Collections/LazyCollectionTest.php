<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Collections\LazyCollection;
use PHPUnit_Framework_TestCase;

class LazyCollectionTest extends PHPUnit_Framework_TestCase
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
