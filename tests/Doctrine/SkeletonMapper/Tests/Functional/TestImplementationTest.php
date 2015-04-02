<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Tests\TestImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\ObjectPersister;
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
        $this->profiles = new ArrayCollection();

        $this->usersTester = new TestUsersTester($this->users);
        $this->profilesTester = new TestUsersTester($this->profiles);

        $this->setUpCommon();
    }

    protected function createUserDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->users, $this->userClassName
        );
    }

    protected function createUserPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->users, $this->userClassName
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
