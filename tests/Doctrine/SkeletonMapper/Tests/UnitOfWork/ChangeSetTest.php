<?php

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ChangeSetTest extends TestCase
{
    public function testChangeSet()
    {
        $object = new \stdClass();
        $changeSet = new ChangeSet($object);

        $this->assertSame($object, $changeSet->getObject());

        $this->assertFalse($changeSet->hasChangedField('username'));

        $change = new Change('username', 'jwage', 'jonwage');

        $changeSet->addChange($change);

        $this->assertTrue($changeSet->hasChangedField('username'));

        $this->assertSame($change, $changeSet->getFieldChange('username'));

        $this->assertEquals('username', $change->getPropertyName());
        $this->assertEquals('jwage', $change->getOldValue());
        $this->assertEquals('jonwage', $change->getNewValue());

        $change->setNewValue('jon');

        $this->assertEquals('jon', $change->getNewValue());
    }
}
