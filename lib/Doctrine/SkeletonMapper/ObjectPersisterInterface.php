<?php

namespace Doctrine\SkeletonMapper;

interface ObjectPersisterInterface
{
    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     */
    public function persist($object);

    /**
     * Schedules the object to be updated.
     *
     * The object will be updated in the database as a result of the flush operation.
     *
     * @param object $object The instance to update
     */
    public function update($object);

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object The object instance to remove.
     */
    public function remove($object);

    /**
     * Commits any changes scheduled in the persister.
     */
    public function commit();

    /**
     * Converts an object to an array.
     *
     * @param object $object
     *
     * @return array
     */
    public function objectToArray($object);

    /**
     * Performs operation to write object to the database.
     *
     * @param object $object
     *
     * @return array $objectData
     */
    public function persistObject($object);

    /**
     * Performs operation to update object in the database.
     *
     * @param object $object
     *
     * @return array $objectData
     */
    public function updateObject($object);

    /**
     * Performs operation to remove object in the database.
     *
     * @param object $object
     */
    public function removeObject($object);

    /**
     * Clears any changes scheduled in the persister.
     */
    public function clear();

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForPersist($object);

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForUpdate($object);

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForRemove($object);
}
