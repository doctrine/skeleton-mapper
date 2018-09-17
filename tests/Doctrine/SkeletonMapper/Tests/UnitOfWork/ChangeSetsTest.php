<?php

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSets;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ChangeSetsTest extends TestCase
{
    public function testChangeSet()
    {
        $object = new \stdClass();
        $changeSets = new ChangeSets($object);

        $change = new Change('username', 'jonwage', 'jwage');
        $changeSets->addObjectChange($object, $change);

        $changeSet = new ChangeSet($object, array('username' => $change));

        $this->assertEquals($changeSet, $changeSets->getObjectChangeSet($object));
    }

    public function testGetObjectChangeSet()
    {
        $object = new \stdClass();
        $changeSets = new ChangeSets($object);

        $changeSet = $changeSets->getObjectChangeSet($object);
        $this->assertInstanceOf('Doctrine\SkeletonMapper\UnitOfWork\ChangeSet', $changeSet);
    }
}
