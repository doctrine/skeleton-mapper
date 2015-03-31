<?php

namespace Doctrine\SkeletonMapper\Tests\DBALImplementation\User;

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
}
