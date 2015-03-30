<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

use Doctrine\SkeletonMapper\Persister\ObjectAction;
use Doctrine\SkeletonMapper\Persister\MongoDBObjectPersister;

class UserPersister extends MongoDBObjectPersister
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function executeObjectAction(ObjectAction $objectAction)
    {
        $object = $objectAction->getObject();

        switch ($objectAction->getName()) {
            case 'register':
                $object->password = md5($object->password);
            break;
        }

        $objectAction->setResult(array('success' => true));
    }
}
