<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\SkeletonMapper\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\TestImplementation\User\User';
    }

    public function getObjectIdentifier($object)
    {
        return array('id' => $object->id);
    }

    public function getIdentifierFieldNames()
    {
        return array('id');
    }

    public function merge($object)
    {
        $user = $this->find($object->id);

        $user->username = $object->username;
        $user->password = $object->password;
    }
}
