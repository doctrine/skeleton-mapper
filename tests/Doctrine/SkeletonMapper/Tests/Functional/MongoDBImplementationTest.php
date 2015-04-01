<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\UsersTesterInterface;

class MongoDBImplementationTest extends BaseImplementationTest
{
    protected function setUpImplementation()
    {
        $mongo = version_compare(phpversion('mongo'), '1.3.0', '<')
            ? new \Mongo()
            : new \MongoClient();

        $this->users = $mongo->selectDb('test')->selectCollection('users');

        $this->users->drop();

        $this->users->batchInsert(array(
            array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $this->usersTester = new MongoDBUsersTester($this->users);
    }

    protected function createUserDataRepository()
    {
        return new UserDataRepository(
            $this->objectManager, $this->users, $this->testClassName, 'users'
        );
    }

    protected function createUserRepository()
    {
        return new UserRepository(
            $this->objectManager,
            $this->userDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager
        );
    }

    protected function createUserPersister()
    {
        return new UserPersister(
            $this->objectManager, $this->users, $this->testClassName, 'users'
        );
    }
}

class MongoDBUsersTester implements UsersTesterInterface
{
    private $collection;

    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    public function find($id)
    {
        return $this->collection->findOne(array('_id' => $id));
    }

    public function set($id, $key, $value)
    {
        $this->collection->update(array('_id' => $id), array('$set' => array($key => $value)));
    }

    public function count()
    {
        return $this->collection->count();
    }
}
