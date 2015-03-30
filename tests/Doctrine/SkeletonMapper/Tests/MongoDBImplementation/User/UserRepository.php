<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

use Doctrine\SkeletonMapper\Repository\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function getObjectIdentifier($object)
    {
        return array('_id' => $object->id);
    }

    public function merge($object)
    {
        $user = $this->find($object->id);

        $user->username = $object->username;
        $user->password = $object->password;
    }
}
