<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\SkeletonMapper\Persister\ArrayObjectPersister;

class UserPersister extends ArrayObjectPersister
{
    public function getClassName()
    {
        return 'Doctrine\SkeletonMapper\Tests\Model\User';
    }
}
