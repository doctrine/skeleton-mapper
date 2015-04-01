<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\UsersTesterInterface;

class TestImplementationTest extends BaseImplementationTest
{
    protected function setUpImplementation()
    {
        $this->users = new ArrayCollection(array(
            1 => array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            2 => array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $this->usersTester = new TestUsersTester($this->users);

        $this->setUpCommon();
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

class TestUsersTester implements UsersTesterInterface
{
    private $users;

    public function __construct(ArrayCollection $users)
    {
        $this->users = $users;
    }

    public function find($id)
    {
        foreach ($this->users as $user) {
            if ($user['_id'] === $id) {
                return $user;
            }
        }
    }

    public function set($id, $key, $value)
    {
        $user = $this->users[$id];
        $user[$key] = $value;
        $this->users[$id] = $user;
    }

    public function count()
    {
        return $this->users->count();
    }
}
