<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

use Doctrine\SkeletonMapper\Persister\MongoDBObjectPersister;

class UserPersister extends MongoDBObjectPersister
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function persistObject($object)
    {
        $data = $this->prepareChangeSet($object);

        if (!isset($data['_id'])) {
            $class = $this->objectManager->getClassMetadata(get_class($object));

            $mostRecentDocument = $this->mongoCollection
                ->find(array(), array('_id' => 1))
                ->sort(array('_id' => -1))
                ->limit(1);
            $mostRecentDocument = iterator_to_array($mostRecentDocument, false);
            $mostRecentDocument = $mostRecentDocument[0];

            $nextId = $mostRecentDocument['_id'] + 1;

            $data[$class->identifier[0]] = $nextId;
        }

        $this->mongoCollection->insert($data);

        return $data;
    }
}
