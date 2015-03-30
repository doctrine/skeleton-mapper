<?php

namespace Doctrine\SkeletonMapper\Tests\DBALImplementation\User;

use Doctrine\SkeletonMapper\Repository\DBALObjectDataRepository;

class UserDataRepository extends DBALObjectDataRepository
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
