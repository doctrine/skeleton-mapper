<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Tests\ArrayImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\ArrayImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;

/**
 * @group functional
 */
class ArrayImplementationTest extends BaseImplementationTest
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
        $this->groups = new ArrayCollection();

        $this->usersTester = new ArrayTester($this->users);
        $this->profilesTester = new ArrayTester($this->profiles);
        $this->groupsTester = new ArrayTester($this->groups);

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
            $this->objectManager, $this->profiles, $this->profileClassName
        );
    }

    protected function createProfilePersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->profiles, $this->profileClassName
        );
    }

    protected function createGroupDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->groups, $this->groupClassName
        );
    }

    protected function createGroupPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->groups, $this->groupClassName
        );
    }
}

class ArrayTester implements DataTesterInterface
{
    private $objects;

    public function __construct(ArrayCollection $objects)
    {
        $this->objects = $objects;
    }

    public function find($id)
    {
        foreach ($this->objects as $object) {
            if ($object['_id'] === $id) {
                return $object;
            }
        }
    }

    public function set($id, $key, $value)
    {
        $object = $this->objects[$id];
        $object[$key] = $value;
        $this->objects[$id] = $object;
    }

    public function count()
    {
        return $this->objects->count();
    }
}
