<?php

namespace Doctrine\ORMLess\Tests\TestImplementation\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORMLess\ObjectIdentityMap;
use Doctrine\ORMLess\ObjectPersister;

class UserPersister extends ObjectPersister
{
    private $users;

    public function __construct(
        ObjectIdentityMap $objectIdentityMap,
        ArrayCollection $users)
    {
        parent::__construct($objectIdentityMap);
        $this->users = $users;
    }

    public function getClassName()
    {
        return 'Doctrine\ORMLess\Tests\TestImplementation\User\User';
    }

    public function persistObject($object)
    {
        $this->users[$object->id] = $this->objectToArray($object);

        return $this->users[$object->id];
    }

    public function updateObject($object)
    {
        $this->users[$object->id] = $this->objectToArray($object);

        return $this->users[$object->id];
    }

    public function removeObject($object)
    {
        unset($this->users[$object->id]);
    }

    public function objectToArray($object)
    {
        return array(
            'id' => $object->id,
            'username' => $object->username,
            'password' => $object->password,
        );
    }
}
