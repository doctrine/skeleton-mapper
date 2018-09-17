<?php

namespace Doctrine\SkeletonMapper\UnitOfWork;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Event;
use Doctrine\SkeletonMapper\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class EventDispatcher
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\Common\EventManager                   $eventManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EventManager $eventManager)
    {
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param string                     $eventName
     * @param \Doctrine\Common\EventArgs $event
     */
    public function dispatchEvent($eventName, EventArgs $event)
    {
        if ($this->eventManager->hasListeners($eventName)) {
            $this->eventManager->dispatchEvent($eventName, $event);
        }
    }

    /**
     * @param string $eventName
     * @param object $object
     */
    public function dispatchObjectLifecycleCallback($eventName, $object, array &$args = array())
    {
        $className = get_class($object);
        $class = $this->objectManager->getClassMetadata($className);

        if (!empty($class->lifecycleCallbacks[$eventName])) {
            $class->invokeLifecycleCallbacks($eventName, $object, $args);
        }
    }

    /**
     * @param string $eventName
     * @param array  $objects
     */
    public function dispatchObjectsLifecycleCallbacks($eventName, array $objects)
    {
        foreach ($objects as $object) {
            $this->dispatchObjectLifecycleCallback($eventName, $object);
        }
    }

    public function dispatchPreFlush()
    {
        $this->dispatchEvent(
            Events::preFlush,
            new Event\PreFlushEventArgs($this->objectManager)
        );
    }

    /**
     * @param array $objects
     */
    public function dispatchPreFlushLifecycleCallbacks(array $objects)
    {
        $this->dispatchObjectsLifecycleCallbacks(Events::preFlush, $objects);
    }

    public function dispatchOnFlush()
    {
        $this->dispatchEvent(
            Events::onFlush,
            new Event\OnFlushEventArgs($this->objectManager)
        );
    }

    public function dispatchPostFlush()
    {
        $this->dispatchEvent(
            Events::postFlush,
            new Event\PostFlushEventArgs($this->objectManager)
        );
    }

    /**
     * @param string $className
     */
    public function dispatchOnClearEvent($className)
    {
        $this->dispatchEvent(
            Events::onClear,
            new Event\OnClearEventArgs($this->objectManager, $className)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPreRemove($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::preRemove, $object);

        $this->dispatchEvent(
            Events::preRemove,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    /**
     * @param object                                         $object
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet  $changeSet
     */
    public function dispatchPreUpdate($object, ChangeSet $changeSet)
    {
        $args = array($changeSet);
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
    public function dispatchPrePersist($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::prePersist, $object);

        $this->dispatchEvent(
            Events::prePersist,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPreLoad($object, array &$data)
    {
        $args = array(&$data);
        $this->dispatchObjectLifecycleCallback(Events::preLoad, $object, $args);

        $this->dispatchEvent(
            Events::preLoad,
            new PreLoadEventArgs($object, $this->objectManager, $data)
        );
    }

    /**
     * @param object $object
     */
    public function dispatchPostLoad($object)
    {
        $this->dispatchLifecycleEvent(Events::postLoad, $object);
    }

    /**
     * @param string $eventName
     * @param object $object
     */
    public function dispatchLifecycleEvent($eventName, $object)
    {
        $this->dispatchObjectLifecycleCallback($eventName, $object);

        $this->dispatchEvent(
            $eventName,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }
}
