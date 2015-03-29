<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\Persister\ObjectPersister;
use MongoCollection;

class UserPersister extends ObjectPersister
{
    private $mongoCollection;

    public function __construct(
        ObjectIdentityMap $objectIdentityMap,
        MongoCollection $mongoCollection)
    {
        parent::__construct($objectIdentityMap);
        $this->mongoCollection = $mongoCollection;
    }

    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\User';
    }

    public function persistObject($object)
    {
        $data = $this->objectToArray($object);

        $this->mongoCollection->insert($data);

        return $data;
    }

    public function updateObject($object)
    {
        $data = $this->objectToArray($object);

        $this->mongoCollection->save($data);

        return $data;
    }

    public function removeObject($object)
    {
        $this->mongoCollection->remove(array('_id' => $object->id));
    }

    public function objectToArray($object)
    {
        return array(
            '_id' => (int) $object->id,
            'username' => $object->username,
            'password' => $object->password,
        );
    }
}
