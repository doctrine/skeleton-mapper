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
        $this->users[$object->getId()] = $this->objectToArray($object);

        return $this->users[$object->getId()];
    }

    public function updateObject($object)
    {
        $this->users[$object->getId()] = $this->objectToArray($object);

        return $this->users[$object->getId()];
    }

    public function removeObject($object)
    {
        unset($this->users[$object->getId()]);
    }
}
