<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserPersister;

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
    }

    protected function createUserDataRepository()
    {
        return new UserDataRepository(
            $this->objectManager, $this->users
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
            $this->objectManager, $this->users
        );
    }
}
