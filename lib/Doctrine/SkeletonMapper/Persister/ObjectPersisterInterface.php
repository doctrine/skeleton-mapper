<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Interface that object persisters must implement.
 */
interface ObjectPersisterInterface
{
    /**
     * Prepares an object persist changeset for persistence.
     *
     * @param object $object
     *
     * @return array
     */
    public function preparePersistChangeSet($object);

    /**
     * Prepares an object update changeset for update.
     *
     * @param object                                        $object
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet($object, ChangeSet $changeSet);

    /**
     * Performs operation to write object to the database.
     *
     * @param object $object
     *
     * @return array $objectData
     */
    public function persistObject($object);

    /**
     * Assign identifier to object.
     *
     * @param object $object
     * @param array  $identifier
     */
    public function assignIdentifier($object, array $identifier);

    /**
     * Performs operation to update object in the database.
     *
     * @param object                                        $object
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet $changeSet
     *
     * @return array $objectData
     */
    public function updateObject($object, ChangeSet $changeSet);

    /**
     * Performs operation to remove object in the database.
     *
     * @param object $object
     */
    public function removeObject($object);
}
