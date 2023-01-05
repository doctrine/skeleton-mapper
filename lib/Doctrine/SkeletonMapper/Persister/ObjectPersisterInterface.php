<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Interface that object persisters must implement.
 *
 * @template T of object
 */
interface ObjectPersisterInterface
{
    /**
     * Prepares an object persist changeset for persistence.
     *
     * @return mixed[]
     */
    public function preparePersistChangeSet(object $object): array;

    /**
     * Prepares an object update changeset for update.
     *
     * @return mixed[]
     */
    public function prepareUpdateChangeSet(object $object, ChangeSet $changeSet): array;

    /**
     * Performs operation to write object to the database.
     *
     * @return mixed[] $objectData
     */
    public function persistObject(object $object): array;

    /**
     * Assign identifier to object.
     *
     * @param mixed[] $identifier
     */
    public function assignIdentifier(object $object, array $identifier): void;

    /**
     * Performs operation to update object in the database.
     *
     * @return mixed[] $objectData
     */
    public function updateObject(object $object, ChangeSet $changeSet): array;

    /**
     * Performs operation to remove object in the database.
     */
    public function removeObject(object $object): void;
}
