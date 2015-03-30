<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\Persister\ObjectAction;
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
        return 'Doctrine\SkeletonMapper\Tests\TestImplementation\User\User';
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

    public function executeObjectAction(ObjectAction $objectAction)
    {
        $object = $objectAction->getObject();

        switch ($objectAction->getName()) {
            case 'register':
                $object->password = md5($object->password);
            break;
        }

        $objectAction->setResult(array('success' => true));
    }

    public function objectToArray($object)
    {
        return array(
            '_id' => $object->id,
            'username' => $object->username,
            'password' => $object->password,
        );
    }
}
