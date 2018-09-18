<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\UnitOfWork;

use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group unit
 */
class ChangeSetTest extends TestCase
{
    public function testChangeSet() : void
    {
        $object    = new stdClass();
        $changeSet = new ChangeSet($object);

        self::assertSame($object, $changeSet->getObject());

        self::assertFalse($changeSet->hasChangedField('username'));

        $change = new Change('username', 'jwage', 'jonwage');

        $changeSet->addChange($change);

        self::assertTrue($changeSet->hasChangedField('username'));

        self::assertSame($change, $changeSet->getFieldChange('username'));

        self::assertEquals('username', $change->getPropertyName());
        self::assertEquals('jwage', $change->getOldValue());
        self::assertEquals('jonwage', $change->getNewValue());

        $change->setNewValue('jon');

        self::assertEquals('jon', $change->getNewValue());
    }
}
