<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserPersister;

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

        $this->setUpCommon();
    }

    protected function createUserDataRepository()
    {
        return new UserDataRepository(
            $this->users
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
            $this->users
        );
    }
}
