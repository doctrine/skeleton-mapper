<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Persister\ObjectAction;
use PHPUnit_Framework_TestCase;

abstract class BaseImplementationTest extends PHPUnit_Framework_TestCase
{
    protected $objectManager;
    protected $objectIdentityMap;
    protected $users;
    protected $testClassName;
    protected $eventTester;

    public function testGetClassMetadata()
    {
        $class = $this->objectManager->getClassMetadata($this->testClassName);

        $fieldMappings = array(
            'id' => array(
                'name' => '_id',
                'fieldName' => 'id',
            ),
            'username' => array(
                'name' => 'username',
                'fieldName' => 'username',
            ),
            'password' => array(
                'name' => 'password',
                'fieldName' => 'password',
            ),
        );

        $this->assertEquals($this->testClassName, $class->getName());
        $this->assertEquals(array('_id'), $class->getIdentifier());
        $this->assertEquals(array('id'), $class->getIdentifierFieldNames());
        $this->assertInstanceOf('ReflectionClass', $class->getReflectionClass());

        $this->assertTrue($class->isIdentifier('id'));
        $this->assertFalse($class->isIdentifier('username'));

        $this->assertTrue($class->hasField('username'));
        $this->assertFalse($class->hasField('nope'));

        $this->assertEquals(array('id', 'username', 'password'), $class->getFieldNames());
        $this->assertEquals($fieldMappings, $class->getFieldMappings());
    }

    public function testFind()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->assertInstanceOf($this->testClassName, $user1);

        $this->assertEquals(1, $user1->id);
        $this->assertEquals('jwage', $user1->username);
        $this->assertEquals('password', $user1->password);

        $user2 = $this->objectManager->find($this->testClassName, 2);

        $this->assertInstanceOf($this->testClassName, $user2);

        $this->assertSame($user2, $this->objectManager->find($this->testClassName, 2));

        $this->assertEquals(2, $user2->id);
        $this->assertEquals('romanb', $user2->username);
        $this->assertEquals('password', $user2->password);
    }

    public function testFindAll()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);
        $user2 = $this->objectManager->find($this->testClassName, 2);

        $users = $this->objectManager
            ->getRepository($this->testClassName)
            ->findAll();

        $this->assertSame(array($user1, $user2), $users);
    }

    public function testFindBy()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $users = $this->objectManager
            ->getRepository($this->testClassName)
            ->findBy(array('username' => 'jwage'));

        $this->assertSame(array($user1), $users);
    }

    public function testIdentityMap()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);
        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertSame($user1, $user2);
    }

    public function testPersist()
    {
        $user = $this->createTestObject();
        $user->id = 3;
        $user->username = 'benjamin';
        $user->password = 'password';

        $this->assertEquals(2, $this->users->count());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertEquals(3, $this->users->count());
        $this->assertSame($user, $this->objectManager->find($this->testClassName, 3));
    }

    public function testUpdates()
    {
        $user = $this->objectManager->find($this->testClassName, 1);
        $user->username = 'jonwage';

        $this->objectManager->update($user);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertEquals('jonwage', $user2->username);
    }

    public function testRemove()
    {
        $user = $this->objectManager->find($this->testClassName, 2);

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $this->assertEquals(1, $this->users->count());

        $this->assertNull($this->objectManager->find($this->testClassName, 2));
    }

    public function testRefresh()
    {
        $user = $this->objectManager->find($this->testClassName, 1);

        $user->password = 'yeehaw';

        $this->objectManager->refresh($user);

        $this->assertEquals('password', $user->password);
    }

    public function testClear()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->clear($this->testClassName);

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user1, $user2);

        $this->objectManager->clear();

        $user3 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user2, $user3);

        $user = $this->createTestObject();
        $user->id = 10;

        $this->objectManager->persist($user);
        $this->objectManager->clear($this->testClassName);
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->testClassName, 10));

        $user = $this->createTestObject();
        $user->id = 10;

        $this->objectManager->persist($user);
        $this->objectManager->clear();
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->testClassName, 10));
    }

    public function testDetach()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->detach($user1);

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user1, $user2);
    }

    public function testMerge()
    {
        $user1 = $this->createTestObject();
        $user1->id = 1;
        $user1->username = 'jonwage';
        $user1->password = 'password';

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->merge($user1);

        $this->assertEquals('jonwage', $user2->username);
    }

    public function testContains()
    {
        $user = $this->createTestObject();
        $user->id = 3;

        $this->assertFalse($this->objectManager->contains($user));

        $this->objectManager->persist($user);

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->flush();

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->clear();

        $this->assertFalse($this->objectManager->contains($user));
    }

    public function testPersisterActions()
    {
        $user = $this->createTestObject();
        $user->id = 3;
        $user->username = 'jmikola';
        $user->password = 'password';

        $action = new ObjectAction($user, 'register', array(
            'key' => 'value',
        ));

        $this->objectManager->action($action);
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertEquals(md5('password'), $user->password);
        $this->assertEquals(array('success' => true), $action->getResult());
    }

    public function testEvents()
    {
        $user = $this->createTestObject();
        $user->id = 3;

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $expected = array(
            Events::prePersist,
            Events::preFlush,
            Events::onFlush,
            Events::postPersist,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $user->username = 'jmikola';
        $this->objectManager->update($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preUpdate,
            Events::preFlush,
            Events::onFlush,
            Events::postUpdate,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();
        $this->objectManager->clear();

        $expected = array(
            Events::onClear,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preRemove,
            Events::preFlush,
            Events::onFlush,
            Events::postRemove,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $user = $this->objectManager->find($this->testClassName, 1);

        $expected = array(
            Events::preLoad,
            Events::postLoad,
        );

        $this->assertEquals($expected, $this->eventTester->called);
    }

    private function createTestObject()
    {
        $className = $this->testClassName;

        return new $className();
    }
}

class EventTester
{
    public $called = array();

    public function __call($method, array $arguments)
    {
        $this->called[] = $method;
    }
}
