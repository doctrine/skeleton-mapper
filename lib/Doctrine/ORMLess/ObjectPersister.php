<?php

namespace Doctrine\ORMLess;

abstract class ObjectPersister implements ObjectPersisterInterface
{
    /**
     * @var \Doctrine\ORMLess\ObjectIdentityMap
     */
    protected $objectIdentityMap;

    /**
     * @var array
     */
    protected $objectsToPersist = array();

    /**
     * @var array
     */
    protected $objectsToUpdate = array();

    /**
     * @var array
     */
    protected $objectsToRemove = array();

    /**
     * @param \Doctrine\ORMLess\ObjectIdentityMap $objectIdentityMap
     */
    public function __construct(ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * @param object $object
     */
    public function persist($object)
    {
        $this->objectsToPersist[] = $object;
    }

    /**
     * @param object $object
     */
    public function update($object)
    {
        $this->objectsToUpdate[] = $object;
    }

    /**
     * @param object $object
     */
    public function remove($object)
    {
        $this->objectsToRemove[] = $object;
    }

    /**
     */
    public function clear()
    {
        $this->objectsToPersist = array();
        $this->objectsToRemove = array();
    }

    /**
     */
    public function commit()
    {
        foreach ($this->objectsToPersist as $object) {
            $objectData = $this->persistObject($object);

            $this->objectIdentityMap->addToIdentityMap($object, $objectData);
        }

        foreach ($this->objectsToUpdate as $object) {
            $this->updateObject($object);
        }

        foreach ($this->objectsToRemove as $object) {
            $this->removeObject($object);

            $this->objectIdentityMap->detach($object);
        }

        $this->clear();
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForPersist($object)
    {
        return in_array($object, $this->objectsToPersist);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForUpdate($object)
    {
        return in_array($object, $this->objectsToUpdate);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForRemove($object)
    {
        return in_array($object, $this->objectsToRemove);
    }
}
