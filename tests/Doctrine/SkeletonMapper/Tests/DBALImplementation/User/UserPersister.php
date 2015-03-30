<?php

namespace Doctrine\SkeletonMapper\Tests\DBALImplementation\User;

use Doctrine\SkeletonMapper\Persister\ObjectAction;
use Doctrine\SkeletonMapper\Persister\DBALObjectPersister;

class UserPersister extends DBALObjectPersister
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }

    public function getTableName()
    {
        return 'users';
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
