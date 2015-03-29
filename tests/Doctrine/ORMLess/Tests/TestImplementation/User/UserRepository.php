<?php

namespace Doctrine\ORMLess\Tests\TestImplementation\User;

use Doctrine\ORMLess\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'Doctrine\ORMLess\Tests\TestImplementation\User\User';
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
