<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Interface persistable objects must implement.
 */
interface PersistableInterface
{
    /**
     * @return mixed[]
     */
    public function preparePersistChangeSet() : array;

    /**
     *
     * @return mixed[]
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet) : array;
}
