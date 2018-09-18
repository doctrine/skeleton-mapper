<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Event;
use Doctrine\SkeletonMapper\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use function get_class;

class EventDispatcher
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var EventManager */
    private $eventManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        EventManager $eventManager
    ) {
        $this->objectManager = $objectManager;
        $this->eventManager  = $eventManager;
    }

    public function dispatchEvent(string $eventName, EventArgs $event) : void
    {
        if (! $this->eventManager->hasListeners($eventName)) {
            return;
        }

        $this->eventManager->dispatchEvent($eventName, $event);
    }

    /**
     * @param object  $object
     * @param mixed[] $args
     */
    public function dispatchObjectLifecycleCallback(string $eventName, $object, array &$args = []) : void
    {
        $className = get_class($object);

        /** @var ClassMetadataInterface $class */
        $class = $this->objectManager->getClassMetadata($className);

        if (! $class->hasLifecycleCallbacks($eventName)) {
            return;
        }

        $class->invokeLifecycleCallbacks($eventName, $object, $args);
    }

    /**
     * @param object[] $objects
     */
    public function dispatchObjectsLifecycleCallbacks(string $eventName, array $objects) : void
    {
        foreach ($objects as $object) {
            $this->dispatchObjectLifecycleCallback($eventName, $object);
        }
    }

    public function dispatchPreFlush() : void
    {
        $this->dispatchEvent(
            Events::preFlush,
            new Event\PreFlushEventArgs($this->objectManager)
        );
    }

    /**
     * @param object[] $objects
     */
    public function dispatchPreFlushLifecycleCallbacks(array $objects) : void
    {
        $this->dispatchObjectsLifecycleCallbacks(Events::preFlush, $objects);
    }

    public function dispatchOnFlush() : void
    {
        $this->dispatchEvent(
            Events::onFlush,
            new Event\OnFlushEventArgs($this->objectManager)
        );
    }

    public function dispatchPostFlush() : void
    {
        $this->dispatchEvent(
            Events::postFlush,
            new Event\PostFlushEventArgs($this->objectManager)
        );
    }

    public function dispatchOnClearEvent(?string $className) : void
    {
        $this->dispatchEvent(
            Events::onClear,
            new Event\OnClearEventArgs($this->objectManager, $className)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPreRemove($object) : void
    {
        $this->dispatchObjectLifecycleCallback(Events::preRemove, $object);

        $this->dispatchEvent(
            Events::preRemove,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPreUpdate($object, ChangeSet $changeSet) : void
    {
        $args = [$changeSet];
        $this->dispatchObjectLifecycleCallback(Events::preUpdate, $object, $args);

        $this->dispatchEvent(
            Events::preUpdate,
            new PreUpdateEventArgs(
                $object,
                $this->objectManager,
                $changeSet
            )
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPrePersist($object) : void
    {
        $this->dispatchObjectLifecycleCallback(Events::prePersist, $object);

        $this->dispatchEvent(
            Events::prePersist,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    /**
     * @param object  $object
     * @param mixed[] $data
     */
    public function dispatchPreLoad($object, array &$data) : void
    {
        $args = [&$data];
        $this->dispatchObjectLifecycleCallback(Events::preLoad, $object, $args);

        $this->dispatchEvent(
            Events::preLoad,
            new PreLoadEventArgs($object, $this->objectManager, $data)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPostLoad($object) : void
    {
        $this->dispatchLifecycleEvent(Events::postLoad, $object);
    }

    /**
     * @param object $object
     */
    public function dispatchLifecycleEvent(string $eventName, $object) : void
    {
        $this->dispatchObjectLifecycleCallback($eventName, $object);

        $this->dispatchEvent(
            $eventName,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }
}
