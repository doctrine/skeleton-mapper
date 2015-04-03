<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\SkeletonMapper\Tests\CacheImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\CacheImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;

class CacheImplementationTest extends BaseImplementationTest
{
    protected function setUpImplementation()
    {
        $users = array(
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
        );

        $this->users = new ArrayCache();

        foreach ($users as $user) {
            $this->users->save($user['_id'], $user);
        }

        $this->users->save('incrementedId', 2);
        $this->users->save('numObjects', 2);
        $this->users->save('objectIds', array(1, 2));

        $this->profiles = new ArrayCache();
        $this->groups = new ArrayCache();

        $this->usersTester = new CacheTester($this->users);
        $this->profilesTester = new CacheTester($this->profiles);
        $this->groupsTester = new CacheTester($this->groups);

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

class CacheTester implements DataTesterInterface
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function find($id)
    {
        return $this->cache->fetch($id);
    }

    public function set($id, $key, $value)
    {
        $object = $this->cache->fetch($id);
        $object[$key] = $value;
        $this->cache->save($id, $object);
    }

    public function count()
    {
        return (int) $this->cache->fetch('numObjects');
    }
}
