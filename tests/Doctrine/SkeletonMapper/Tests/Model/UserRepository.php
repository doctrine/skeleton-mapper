<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function getObjectIdentifier($object)
    {
        return array('_id' => (int) $object->getId());
    }

    public function getObjectIdentifierFromData(array $data)
    {
        return array('_id' => (int) $data['_id']);
    }

    public function merge($object)
    {
        $user = $this->find($object->getId());

        $user->setUsername($object->getUsername());
        $user->setPassword($object->getPassword());
    }
}
