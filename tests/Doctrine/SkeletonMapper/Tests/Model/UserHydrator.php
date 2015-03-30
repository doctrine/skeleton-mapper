<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\ObjectHydrator;

class UserHydrator extends ObjectHydrator
{
    public function hydrate($object, array $data)
    {
        if (isset($data['_id'])) {
            $object->id = (int) $data['_id'];
        }

        if (isset($data['username'])) {
            $object->username = (string) $data['username'];
        }

        if (isset($data['password'])) {
            $object->password = (string) $data['password'];
        }
    }
}
