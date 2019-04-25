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
     * @return array<string, mixed>
     */
    public function preparePersistChangeSet() : array;

    /**
     * @return array<string, mixed>
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet) : array;
}
