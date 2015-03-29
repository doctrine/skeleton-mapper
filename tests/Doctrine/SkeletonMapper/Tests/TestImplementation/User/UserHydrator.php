<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\SkeletonMapper\Hydrator\ObjectHydrator;

class UserHydrator extends ObjectHydrator
{
    public function hydrate($object, array $data)
    {
        if (isset($data['id'])) {
            $object->id = (int) $data['id'];
        }

        if (isset($data['username'])) {
            $object->username = (string) $data['username'];
        }

        if (isset($data['password'])) {
            $object->password = (string) $data['password'];
        }
    }
}
