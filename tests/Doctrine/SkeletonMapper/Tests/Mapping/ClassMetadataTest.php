<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Mapping;

use BadMethodCallException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group unit
 */
class ClassMetadataTest extends TestCase
{
    /** @var ClassMetadata<object> */
    private $class;

    public function testMapField(): void
    {
        $this->class->mapField(['fieldName' => 'name']);

        self::assertEquals(['name' => ['fieldName' => 'name', 'name' => 'name']], $this->class->fieldMappings);
    }

    public function testGetName(): void
    {
        self::assertEquals(ClassMetadataTestModel::class, $this->class->getName());
    }

    public function testGetIdentifier(): void
    {
        $this->class->identifier = ['id'];
        self::assertEquals(['id'], $this->class->getIdentifier());
    }

    public function testGetReflectionClass(): void
    {
        self::assertSame(ClassMetadataTestModel::class, $this->class->getReflectionClass()->getName());
    }

    public function testIsIdentifier(): void
    {
        self::assertFalse($this->class->isIdentifier('id'));

        $this->class->identifierFieldNames = ['id'];

        self::assertTrue($this->class->isIdentifier('id'));
    }

    public function testHasField(): void
    {
        self::assertFalse($this->class->hasField('username'));

        $this->class->mapField(['fieldName' => 'username']);

        self::assertTrue($this->class->hasField('username'));
    }

    public function testGetFieldNames(): void
    {
        $this->class->mapField(['fieldName' => 'username']);
        self::assertEquals(['username'], $this->class->getFieldNames());
    }

    public function testGetAssociationNames(): void
    {
        self::assertEquals([], $this->class->getAssociationNames());
    }

    public function testGetTypeOfField(): void
    {
        self::assertEquals('', $this->class->getTypeOfField('username'));

        $this->class->mapField(['fieldName' => 'username', 'type' => 'string']);

        self::assertEquals('string', $this->class->getTypeOfField('username'));
    }

    public function testGetAssociationTargetClass(): void
    {
        $this->class->mapField([
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ]);
        self::assertEquals('Test', $this->class->getAssociationTargetClass('groups'));
    }

    public function testGetAssociationTargetClassThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Association name expected, 'groups' is not an association.");

        $this->class->getAssociationTargetClass('groups');
    }

    public function testGetIdentifierValues(): void
    {
        $this->class->identifier           = ['id'];
        $this->class->identifierFieldNames = ['id'];
        $this->class->mapField(['fieldName' => 'id']);
        $this->class->mapField(['fieldName' => 'username']);

        $object     = new ClassMetadataTestModel();
        $object->id = 1;

        self::assertEquals(['id' => 1], $this->class->getIdentifierValues($object));
    }

    public function testHasAssociation(): void
    {
        self::assertFalse($this->class->hasAssociation('groups'));

        $this->class->mapField([
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ]);

        self::assertTrue($this->class->hasAssociation('groups'));
    }

    public function testAddingAssociationMappingDoesNotAddFieldMapping(): void
    {
        self::assertFalse($this->class->hasAssociation('groups'));

        $this->class->mapField(
            [
                'fieldName' => 'groups',
                'targetObject' => 'Test',
                'type' => 'many',
            ]
        );

        self::assertFalse($this->class->hasField('groups'));
    }

    public function testIsSingleValuedAssociation(): void
    {
        self::assertFalse($this->class->isSingleValuedAssociation('groups'));

        $this->class->mapField([
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ]);

        self::assertFalse($this->class->isSingleValuedAssociation('groups'));

        $this->class->mapField([
            'fieldName' => 'profile',
            'targetObject' => 'Test',
            'type' => 'one',
        ]);

        self::assertTrue($this->class->isSingleValuedAssociation('profile'));
    }

    public function testIsCollectionValuedAssociation(): void
    {
        self::assertFalse($this->class->isCollectionValuedAssociation('profile'));

        $this->class->mapField([
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ]);

        self::assertTrue($this->class->isCollectionValuedAssociation('groups'));

        $this->class->mapField([
            'fieldName' => 'profile',
            'targetObject' => 'Test',
            'type' => 'one',
        ]);

        self::assertFalse($this->class->isCollectionValuedAssociation('profile'));
    }

    public function testInvokeLifecycleCallbacksWithArguments(): void
    {
        $object = new ClassMetadataTestModel();
        $data   = ['test'];

        $this->class->lifecycleCallbacks['test'] = ['testEvent'];

        $this->class->invokeLifecycleCallbacks('test', $object, [$data]);

        self::assertEquals($data, $object->testEventCalled);
    }

    public function testInvokeLifecycleCallbacksThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected class "Doctrine\SkeletonMapper\Tests\Mapping\ClassMetadataTestModel"; found: "stdClass"');

        $this->class->invokeLifecycleCallbacks('test', new stdClass());
    }

    public function testInvokeLifecycleCallbacksWithoutArguments(): void
    {
        $object = new ClassMetadataTestModel();
        $data   = ['test'];

        $this->class->lifecycleCallbacks['test'] = ['testEvent'];

        $this->class->invokeLifecycleCallbacks('test', $object);

        self::assertTrue($object->testEventCalled);
    }

    public function testHasLifecycleCallbacks(): void
    {
        self::assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->lifecycleCallbacks['test'] = ['testEvent'];

        self::assertTrue($this->class->hasLifecycleCallbacks('test'));
    }

    public function testGetLifecycleCallbacks(): void
    {
        self::assertEquals([], $this->class->getLifecycleCallbacks('test'));

        $this->class->lifecycleCallbacks['test'] = ['testEvent'];

        self::assertEquals(['testEvent'], $this->class->getLifecycleCallbacks('test'));
    }

    public function testAddLifecycleCallback(): void
    {
        self::assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->addLifecycleCallback('testEvent', 'test');
        $this->class->addLifecycleCallback('testEvent', 'test');

        self::assertTrue($this->class->hasLifecycleCallbacks('test'));
        self::assertCount(1, $this->class->lifecycleCallbacks['test']);
    }

    public function testSetLifecycleCallbacks(): void
    {
        self::assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->setLifecycleCallbacks(['test' => ['testEvent']]);

        self::assertTrue($this->class->hasLifecycleCallbacks('test'));
    }

    public function testGetIdentifierFieldNames(): void
    {
        $this->class->identifierFieldNames = ['id'];
        self::assertEquals(['id'], $this->class->getIdentifierFieldNames());
    }

    public function testGetAssociationMappedByTargetField(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\SkeletonMapper\Mapping\ClassMetadata::getAssociationMappedByTargetField() is not implemented yet.');

        $this->class->getAssociationMappedByTargetField('test');
    }

    public function testIsAssociationInverseSide(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\SkeletonMapper\Mapping\ClassMetadata::isAssociationInverseSide() is not implemented yet.');

        $this->class->isAssociationInverseSide('test');
    }

    protected function setUp(): void
    {
        $this->class = new ClassMetadata(ClassMetadataTestModel::class);
    }
}

class ClassMetadataTestModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var LifecycleEventArgs|true */
    public $testEventCalled;

    /**
     * @param LifecycleEventArgs|true $args
     */
    public function testEvent($args = null): void
    {
        if ($args !== null) {
            $this->testEventCalled = $args;
        } else {
            $this->testEventCalled = true;
        }
    }
}
