<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ClassMetadataTest extends TestCase
{

    private $class;

    protected function setUp()
    {
        $this->class = new ClassMetadata('Doctrine\SkeletonMapper\Tests\Functional\ClassMetadataTestModel');
    }

    public function testMapField()
    {
        $this->class->mapField(array('fieldName' => 'name'));

        $this->assertEquals(array('name' => array('fieldName' => 'name', 'name' => 'name')), $this->class->fieldMappings);
    }

    public function testGetName()
    {
        $this->assertEquals('Doctrine\SkeletonMapper\Tests\Functional\ClassMetadataTestModel', $this->class->getName());
    }

    public function testGetIdentifier()
    {
        $this->class->identifier = array('id');
        $this->assertEquals(array('id'), $this->class->getIdentifier());
    }

    public function testGetReflectionClass()
    {
        $this->assertInstanceOf('ReflectionClass', $this->class->getReflectionClass());
    }

    public function testIsIdentifier()
    {
        $this->assertFalse($this->class->isIdentifier('id'));

        $this->class->identifierFieldNames = array('id');

        $this->assertTrue($this->class->isIdentifier('id'));
    }

    public function testHasField()
    {
        $this->assertFalse($this->class->hasField('username'));

        $this->class->mapField(array('fieldName' => 'username'));

        $this->assertTrue($this->class->hasField('username'));
    }

    public function testGetFieldNames()
    {
        $this->class->mapField(array('fieldName' => 'username'));
        $this->assertEquals(array('username'), $this->class->getFieldNames());
    }

    public function testGetAssociationNames()
    {
        $this->assertEquals(array(), $this->class->getAssociationNames());
    }

    public function testGetTypeOfField()
    {
        $this->assertEquals('', $this->class->getTypeOfField('username'));

        $this->class->mapField(array('fieldName' => 'username', 'type' => 'string'));

        $this->assertEquals('string', $this->class->getTypeOfField('username'));
    }

    public function testGetAssociationTargetClass()
    {
        $this->class->mapField(array(
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ));
        $this->assertEquals('Test', $this->class->getAssociationTargetClass('groups'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Association name expected, 'groups' is not an association.
     */
    public function testGetAssociationTargetClassThrowsInvalidArgumentException()
    {
        $this->class->getAssociationTargetClass('groups');
    }

    public function testGetIdentifierValues()
    {
        $this->class->identifier = array('id');
        $this->class->identifierFieldNames = array('id');
        $this->class->mapField(array(
            'fieldName' => 'id',
        ));
        $this->class->mapField(array(
            'fieldName' => 'username',
        ));

        $object = new ClassMetadataTestModel();
        $object->id = 1;

        $this->assertEquals(array('id' => 1), $this->class->getIdentifierValues($object));
    }

    public function testHasAssociation()
    {
        $this->assertFalse($this->class->hasAssociation('groups'));

        $this->class->mapField(array(
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ));

        $this->assertTrue($this->class->hasAssociation('groups'));
    }

    public function testAddingAssociationMappingDoesNotAddFieldMapping()
    {
        $this->assertFalse($this->class->hasAssociation('groups'));

        $this->class->mapField(
            array(
                'fieldName' => 'groups',
                'targetObject' => 'Test',
                'type' => 'many',
            )
        );

        $this->assertFalse($this->class->hasField('groups'));
    }

    public function testIsSingleValuedAssociation()
    {
        $this->assertFalse($this->class->isSingleValuedAssociation('groups'));

        $this->class->mapField(array(
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ));

        $this->assertFalse($this->class->isSingleValuedAssociation('groups'));

        $this->class->mapField(array(
            'fieldName' => 'profile',
            'targetObject' => 'Test',
            'type' => 'one',
        ));

        $this->assertTrue($this->class->isSingleValuedAssociation('profile'));
    }

    public function testIsCollectionValuedAssociation()
    {
        $this->assertFalse($this->class->isCollectionValuedAssociation('profile'));

        $this->class->mapField(array(
            'fieldName' => 'groups',
            'targetObject' => 'Test',
            'type' => 'many',
        ));

        $this->assertTrue($this->class->isCollectionValuedAssociation('groups'));

        $this->class->mapField(array(
            'fieldName' => 'profile',
            'targetObject' => 'Test',
            'type' => 'one',
        ));

        $this->assertFalse($this->class->isCollectionValuedAssociation('profile'));
    }

    public function testInvokeLifecycleCallbacksWithArguments()
    {
        $object = new ClassMetadataTestModel();
        $data = array('test');

        $this->class->lifecycleCallbacks['test'] = array('testEvent');

        $this->class->invokeLifecycleCallbacks('test', $object, array($data));

        $this->assertEquals($data, $object->testEventCalled);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected class "Doctrine\SkeletonMapper\Tests\Functional\ClassMetadataTestModel"; found: "stdClass"
     */
    public function testInvokeLifecycleCallbacksThrowsInvalidArgumentException()
    {
        $this->class->invokeLifecycleCallbacks('test', new \stdClass());
    }

    public function testInvokeLifecycleCallbacksWithoutArguments()
    {
        $object = new ClassMetadataTestModel();
        $data = array('test');

        $this->class->lifecycleCallbacks['test'] = array('testEvent');

        $this->class->invokeLifecycleCallbacks('test', $object);

        $this->assertTrue($object->testEventCalled);
    }

    public function testHasLifecycleCallbacks()
    {
        $this->assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->lifecycleCallbacks['test'] = array('testEvent');

        $this->assertTrue($this->class->hasLifecycleCallbacks('test'));
    }

    public function testGetLifecycleCallbacks()
    {
        $this->assertEquals(array(), $this->class->getLifecycleCallbacks('test'));

        $this->class->lifecycleCallbacks['test'] = array('testEvent');

        $this->assertEquals(array('testEvent'), $this->class->getLifecycleCallbacks('test'));
    }

    public function testAddLifecycleCallback()
    {
        $this->assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->addLifecycleCallback('testEvent', 'test');
        $this->class->addLifecycleCallback('testEvent', 'test');

        $this->assertTrue($this->class->hasLifecycleCallbacks('test'));
        $this->assertCount(1, $this->class->lifecycleCallbacks['test']);
    }

    public function testSetLifecycleCallbacks()
    {
        $this->assertFalse($this->class->hasLifecycleCallbacks('test'));

        $this->class->setLifeCycleCallbacks(array('test' => array('testEvent')));

        $this->assertTrue($this->class->hasLifecycleCallbacks('test'));
    }

    public function testGetIdentifierFieldNames()
    {
        $this->class->identifierFieldNames = array('id');
        $this->assertEquals(array('id'), $this->class->getIdentifierFieldNames());
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Doctrine\SkeletonMapper\Mapping\ClassMetadata::getAssociationMappedByTargetField() is not implemented yet.
     */
    public function testGetAssociationMappedByTargetField()
    {
        $this->class->getAssociationMappedByTargetField('test');
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Doctrine\SkeletonMapper\Mapping\ClassMetadata::isAssociationInverseSide() is not implemented yet.
     */
    public function testIsAssociationInverseSide()
    {
        $this->class->isAssociationInverseSide('test');
    }
}

class ClassMetadataTestModel
{
    public $id;
    public $name;
    public $testEventCalled;

    public function testEvent($args = null)
    {
        if ($args) {
            $this->testEventCalled = $args;
        } else {
            $this->testEventCalled = true;
        }
    }
}
