<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Persister\ObjectPersister;

class UserPersister extends ObjectPersister
{
    private $users;

    public function __construct(ArrayCollection $users)
    {
        $this->users = $users;
    }

    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function persistObject($object)
    {
        $data = $this->prepareChangeSet($object);

        $this->users[$object->getId()] = $data;

        return $data;
    }

    public function updateObject($object, array $changeSet)
    {
        $data = $this->prepareChangeSet($object, $changeSet);

        foreach ($data as $key => $value) {
            $user = $this->users[$object->getId()];
            $user[$key] = $value;

            $this->users[$object->getId()] = $user;
        }

        return $data;
    }

    public function removeObject($object)
    {
        unset($this->users[$object->getId()]);
    }
}
