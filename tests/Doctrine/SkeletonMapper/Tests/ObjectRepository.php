<?php

namespace Doctrine\SkeletonMapper\Tests;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;

class ObjectRepository extends BasicObjectRepository
{
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
