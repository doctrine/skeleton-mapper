<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;

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

        $this->profiles = $mongo->selectDb('test')->selectCollection('profiles');
        $this->profiles->drop();

        $this->groups = $mongo->selectDb('test')->selectCollection('groups');
        $this->groups->drop();

        $this->usersTester = new MongoDBTester($this->users);
        $this->profilesTester = new MongoDBTester($this->profiles);
        $this->groupsTester = new MongoDBTester($this->groups);
    }

    protected function createUserDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->users, $this->userClassName, 'users'
        );
    }

    protected function createUserPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->users, $this->userClassName, 'users'
        );
    }

    protected function createProfileDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->profiles, $this->profileClassName, 'profiles'
        );
    }

    protected function createProfilePersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->profiles, $this->profileClassName, 'profiles'
        );
    }

    protected function createGroupDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->groups, $this->groupClassName, 'groups'
        );
    }

    protected function createGroupPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->groups, $this->groupClassName, 'groups'
        );
    }
}

class MongoDBTester implements DataTesterInterface
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
