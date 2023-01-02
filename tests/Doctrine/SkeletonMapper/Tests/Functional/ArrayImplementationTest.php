<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepository;
use Doctrine\SkeletonMapper\Persister\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\ArrayImplementation\ObjectDataRepository as ArrayObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\ArrayImplementation\ObjectPersister as ArrayObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;
use Doctrine\SkeletonMapper\Tests\Model\Group;
use Doctrine\SkeletonMapper\Tests\Model\Profile;
use Doctrine\SkeletonMapper\Tests\Model\User;

class ArrayImplementationTest extends BaseImplementationTest
{
    protected function setUpImplementation(): void
    {
        $this->users    = new ArrayCollection([
            1 => [
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ],
            2 => [
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ],
        ]);
        $this->profiles = new ArrayCollection();
        $this->groups   = new ArrayCollection();

        $this->usersTester    = new ArrayTester($this->users);
        $this->profilesTester = new ArrayTester($this->profiles);
        $this->groupsTester   = new ArrayTester($this->groups);

        $this->setUpCommon();
    }

    protected function createUserDataRepository(): ObjectDataRepository
    {
        return new ArrayObjectDataRepository(
            $this->objectManager,
            $this->users,
            User::class,
        );
    }

    protected function createUserPersister(): ObjectPersister
    {
        return new ArrayObjectPersister(
            $this->objectManager,
            $this->users,
            User::class,
        );
    }

    protected function createProfileDataRepository(): ObjectDataRepository
    {
        return new ArrayObjectDataRepository(
            $this->objectManager,
            $this->profiles,
            Profile::class,
        );
    }

    protected function createProfilePersister(): ObjectPersister
    {
        return new ArrayObjectPersister(
            $this->objectManager,
            $this->profiles,
            Profile::class,
        );
    }

    protected function createGroupDataRepository(): ObjectDataRepository
    {
        return new ArrayObjectDataRepository(
            $this->objectManager,
            $this->groups,
            Group::class,
        );
    }

    protected function createGroupPersister(): ObjectPersister
    {
        return new ArrayObjectPersister(
            $this->objectManager,
            $this->groups,
            Group::class,
        );
    }
}

class ArrayTester implements DataTesterInterface
{
    /** @var ArrayCollection<mixed, mixed> */
    private $objects;

    /** @param ArrayCollection<mixed, mixed> $objects */
    public function __construct(ArrayCollection $objects)
    {
        $this->objects = $objects;
    }

    /** @return mixed[] */
    public function find(int $id): array|null
    {
        foreach ($this->objects as $object) {
            if ($object['_id'] === $id) {
                return $object;
            }
        }

        return null;
    }

    /** @param mixed $value */
    public function set(int $id, string $key, $value): void
    {
        $object             = $this->objects[$id];
        $object[$key]       = $value;
        $this->objects[$id] = $object;
    }

    public function count(): int
    {
        return $this->objects->count();
    }
}
