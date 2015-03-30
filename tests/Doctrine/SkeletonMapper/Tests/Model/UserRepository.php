<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Repository\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function getObjectIdentifier($object)
    {
        return array('_id' => (int) $object->id);
    }

    public function getObjectIdentifierFromData(array $data)
    {
        return array('_id' => (int) $data['_id']);
    }

    public function merge($object)
    {
        $user = $this->find($object->id);

        $user->username = $object->username;
        $user->password = $object->password;
    }
}
