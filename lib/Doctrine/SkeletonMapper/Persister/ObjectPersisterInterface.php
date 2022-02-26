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
     * @param object $object
     *
     * @return mixed[]
     */
    public function preparePersistChangeSet($object): array;

    /**
     * Prepares an object update changeset for update.
     *
     * @param object $object
     *
     * @return mixed[]
     */
    public function prepareUpdateChangeSet($object, ChangeSet $changeSet): array;

    /**
     * Performs operation to write object to the database.
     *
     * @param object $object
     *
     * @return mixed[] $objectData
     */
    public function persistObject($object): array;

    /**
     * Assign identifier to object.
     *
     * @param object  $object
     * @param mixed[] $identifier
     */
    public function assignIdentifier($object, array $identifier): void;

    /**
     * Performs operation to update object in the database.
     *
     * @param object $object
     *
     * @return mixed[] $objectData
     */
    public function updateObject($object, ChangeSet $changeSet): array;

    /**
     * Performs operation to remove object in the database.
     *
     * @param object $object
     */
    public function removeObject($object): void;
}
