<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\UnitOfWork;

use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSets;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group unit
 */
class ChangeSetsTest extends TestCase
{
    public function testChangeSet() : void
    {
        $object     = new stdClass();
        $changeSets = new ChangeSets();

        $change = new Change('username', 'jonwage', 'jwage');
        $changeSets->addObjectChange($object, $change);

        $changeSet = new ChangeSet($object, ['username' => $change]);

        self::assertEquals($changeSet, $changeSets->getObjectChangeSet($object));
    }

    public function testGetObjectChangeSet() : void
    {
        $object     = new stdClass();
        $change     = new Change('username', 'jonwage', 'jwage');
        $changeSets = new ChangeSets();
        $changeSets->addObjectChange($object, $change);

        $changeSet = $changeSets->getObjectChangeSet($object);

        self::assertCount(1, $changeSet->getChanges());
        self::assertSame($change, $changeSet->getChanges()['username']);
    }
}
