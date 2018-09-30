<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\EventManager;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactoryInterface;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSets;
use Doctrine\SkeletonMapper\UnitOfWork\EventDispatcher;
use Doctrine\SkeletonMapper\UnitOfWork\Persister;
use InvalidArgumentException;
use function array_merge;
use function get_class;
use function spl_object_hash;

/**
 * Class for managing the persistence of objects.
 */
class UnitOfWork implements PropertyChangedListener
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ObjectPersisterFactoryInterface */
    private $objectPersisterFactory;

    /** @var ObjectIdentityMap */
    private $objectIdentityMap;

    /** @var EventDispatcher */
    private $eventDispatcher;

    /** @var Persister */
    private $persister;

    /** @var object[] */
    private $objectsToPersist = [];

    /** @var object[] */
    private $objectsToUpdate = [];

    /** @var object[] */
    private $objectsToRemove = [];

    /** @var ChangeSets */
    private $objectChangeSets;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectPersisterFactoryInterface $objectPersisterFactory,
        ObjectIdentityMap $objectIdentityMap,
        EventManager $eventManager
    ) {
        $this->objectManager          = $objectManager;
        $this->objectPersisterFactory = $objectPersisterFactory;
        $this->objectIdentityMap      = $objectIdentityMap;

        $this->eventDispatcher = new EventDispatcher(
            $objectManager,
            $eventManager
        );
        $this->persister       = new Persister(
            $this,
            $this->eventDispatcher,
            $this->objectIdentityMap
        );

        $this->objectChangeSets = new ChangeSets();
    }

    /**
     * @param object $object
     */
    public function merge($object) : void
    {
        $this->getObjectRepository($object)->merge($object);
    }

    /**
     * @param object $object
     */
    public function persist($object) : void
    {
        if ($this->isScheduledForPersist($object)) {
            throw new InvalidArgumentException('Object is already scheduled for persist.');
        }

        $this->eventDispatcher->dispatchPrePersist($object);

        $this->objectsToPersist[spl_object_hash($object)] = $object;

        if (! ($object instanceof NotifyPropertyChanged)) {
            return;
        }

        $object->addPropertyChangedListener($this);
    }

    /**
     * @param object $object The instance to update
     */
    public function update($object) : void
    {
        if ($this->isScheduledForUpdate($object)) {
            throw new InvalidArgumentException('Object is already scheduled for update.');
        }

        $this->eventDispatcher->dispatchPreUpdate(
            $object,
            $this->getObjectChangeSet($object)
        );

        $this->objectsToUpdate[spl_object_hash($object)] = $object;
    }

    /**
     * @param object $object The object instance to remove.
     */
    public function remove($object) : void
    {
        if ($this->isScheduledForRemove($object)) {
            throw new InvalidArgumentException('Object is already scheduled for remove.');
        }

        $this->eventDispatcher->dispatchPreRemove($object);

        $this->objectsToRemove[spl_object_hash($object)] = $object;
    }

    public function clear(?string $objectName = null) : void
    {
        $this->objectIdentityMap->clear($objectName);

        $this->objectsToPersist = [];
        $this->objectsToUpdate  = [];
        $this->objectsToRemove  = [];
        $this->objectChangeSets = new ChangeSets();

        $this->eventDispatcher->dispatchOnClearEvent($objectName);
    }

    /**
     * @param object $object
     */
    public function detach($object) : void
    {
        $this->objectIdentityMap->detach($object);
    }

    /**
     * @param object $object
     */
    public function refresh($object) : void
    {
        $this->getObjectRepository($object)->refresh($object);
    }

    /**
     * @param object $object
     */
    public function contains($object) : bool
    {
        return $this->objectIdentityMap->contains($object)
            || $this->isScheduledForPersist($object);
    }

    /**
     * Commit the contents of the unit of work.
     */
    public function commit() : void
    {
        $this->eventDispatcher->dispatchPreFlush();

        if ($this->objectsToPersist === [] &&
            $this->objectsToUpdate === [] &&
            $this->objectsToRemove === []
        ) {
            return; // Nothing to do.
        }

        $objects = array_merge(
            $this->objectsToPersist,
            $this->objectsToUpdate,
            $this->objectsToRemove
        );
        $this->eventDispatcher->dispatchPreFlushLifecycleCallbacks($objects);

        $this->eventDispatcher->dispatchOnFlush();

        $this->persister->executePersists();
        $this->persister->executeUpdates();
        $this->persister->executeRemoves();

        $this->eventDispatcher->dispatchPostFlush();

        $this->objectsToPersist = [];
        $this->objectsToUpdate  = [];
        $this->objectsToRemove  = [];
        $this->objectChangeSets = new ChangeSets();
    }

    /**
     * @param object $object
     */
    public function isScheduledForPersist($object) : bool
    {
        return isset($this->objectsToPersist[spl_object_hash($object)]);
    }

    /**
     * @return object[]
     */
    public function getObjectsToPersist() : array
    {
        return $this->objectsToPersist;
    }

    /**
     * @param object $object
     */
    public function isScheduledForUpdate($object) : bool
    {
        return isset($this->objectsToUpdate[spl_object_hash($object)]);
    }

    /**
     * @return object[]
     */
    public function getObjectsToUpdate() : array
    {
        return $this->objectsToUpdate;
    }

    /**
     * @param object $object
     */
    public function isScheduledForRemove($object) : bool
    {
        return isset($this->objectsToRemove[spl_object_hash($object)]);
    }

    /**
     * @return object[]
     */
    public function getObjectsToRemove() : array
    {
        return $this->objectsToRemove;
    }

    /* PropertyChangedListener implementation */

    /**
     * Notifies this UnitOfWork of a property change in an object.
     *
     * @param object $object       The entity that owns the property.
     * @param string $propertyName The name of the property that changed.
     * @param mixed  $oldValue     The old value of the property.
     * @param mixed  $newValue     The new value of the property.
     */
    public function propertyChanged($object, $propertyName, $oldValue, $newValue) : void
    {
        if (! $this->isInIdentityMap($object)) {
            return;
        }

        if (! $this->isScheduledForUpdate($object)) {
            $this->update($object);
        }

        $this->objectChangeSets->addObjectChange(
            $object,
            new Change($propertyName, $oldValue, $newValue)
        );
    }

    /**
     * Gets the changeset for a object.
     *
     * @param object $object
     */
    public function getObjectChangeSet($object) : ChangeSet
    {
        return $this->objectChangeSets->getObjectChangeSet($object);
    }

    /**
     * Checks whether an object is registered in the identity map of this UnitOfWork.
     *
     * @param object $object
     */
    public function isInIdentityMap($object) : bool
    {
        return $this->objectIdentityMap->contains($object);
    }

    /**
     * @param mixed[] $data
     *
     * @return object
     */
    public function getOrCreateObject(string $className, array $data)
    {
        $object = $this->objectIdentityMap->tryGetById($className, $data);

        if ($object !== null) {
            return $object;
        }

        return $this->createObject($className, $data);
    }

    /**
     * @param object $object
     */
    public function getObjectPersister($object) : ObjectPersisterInterface
    {
        return $this->objectPersisterFactory
            ->getPersister(get_class($object));
    }

    /**
     * @param object $object
     */
    public function getObjectRepository($object) : ObjectRepositoryInterface
    {
        return $this->objectManager
            ->getRepository(get_class($object));
    }

    /**
     * @param mixed[] $data
     *
     * @return object
     */
    private function createObject(string $className, array $data)
    {
        $repository = $this->objectManager->getRepository($className);

        $object = $repository->create($className);

        if ($object instanceof NotifyPropertyChanged) {
            $object->addPropertyChangedListener($this);
        }

        $this->eventDispatcher->dispatchPreLoad($object, $data);

        $repository->hydrate($object, $data);

        $this->eventDispatcher->dispatchPostLoad($object);

        $this->objectIdentityMap->addToIdentityMap($object, $data);

        return $object;
    }
}
