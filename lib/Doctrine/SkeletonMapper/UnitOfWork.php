<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Event\PreFlushEventArgs;
use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;

/**
 * Class for managing the persistence of objects.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class UnitOfWork implements PropertyChangedListener
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory
     */
    private $objectRepositoryFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory
     */
    private $objectPersisterFactory;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    private $objectIdentityMap;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * @var array
     */
    private $objectsToPersist = array();

    /**
     * @var array
     */
    private $objectsToUpdate = array();

    /**
     * @var array
     */
    private $objectsToRemove = array();

    /**
     * @var array
     */
    private $objectChangeSets = array();

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface                   $objectManager
     * @param \Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory $objectRepositoryFactory
     * @param \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory         $objectPersisterFactory
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap                        $objectIdentityMap
     * @param \Doctrine\Common\EventManager                                     $eventManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectRepositoryFactory $objectRepositoryFactory,
        ObjectPersisterFactory $objectPersisterFactory,
        ObjectIdentityMap $objectIdentityMap,
        EventManager $eventManager)
    {
        $this->objectManager = $objectManager;
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->objectPersisterFactory = $objectPersisterFactory;
        $this->objectIdentityMap = $objectIdentityMap;
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @param object $object
     */
    public function merge($object)
    {
        $this->getObjectRepository($object)->merge($object);
    }

    /**
     * @param object $object
     */
    public function persist($object)
    {
        $oid = spl_object_hash($object);

        if (isset($this->objectsToPersist[$oid])) {
            return;
        }

        $this->dispatchPrePersist($object);

        $this->objectsToPersist[$oid] = $object;

        if ($object instanceof NotifyPropertyChanged) {
            $object->addPropertyChangedListener($this);
        }
    }

    /**
     * @param object $object The instance to update
     */
    public function update($object)
    {
        $oid = spl_object_hash($object);

        if (isset($this->objectsToUpdate[$oid])) {
            return;
        }

        $this->dispatchPreUpdate($object);

        $this->objectsToUpdate[$oid] = $object;
    }

    /**
     * @param object $object The object instance to remove.
     */
    public function remove($object)
    {
        $oid = spl_object_hash($object);

        if (isset($this->objectsToRemove[$oid])) {
            return;
        }

        $this->dispatchPreRemove($object);

        $this->objectsToRemove[$oid] = $object;
    }

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null)
    {
        $this->objectIdentityMap->clear($objectName);

        $this->objectsToPersist = array();
        $this->objectsToUpdate = array();
        $this->objectsToRemove = array();
        $this->objectChangeSets = array();

        $this->dispatchOnClearEvent($objectName);
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $this->objectIdentityMap->detach($object);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $this->getObjectRepository($object)->refresh($object);
    }

    /**
     * @param object $object
     */
    public function contains($object)
    {
        return $this->objectIdentityMap->contains($object)
            || $this->isScheduledForPersist($object);
    }

    /**
     * Commit the contents of the unit of work.
     */
    public function commit()
    {
        $this->dispatchPreFlush();

        if (!($this->objectsToPersist ||
            $this->objectsToUpdate ||
            $this->objectsToRemove)
        ) {
            return; // Nothing to do.
        }

        $this->dispatchPreFlushLifecycleCallbacks();
        $this->dispatchOnFlush();
        $this->executePersists();
        $this->executeUpdates();
        $this->executeRemoves();
        $this->dispatchPostFlush();

        $this->objectChangeSets = array();
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForPersist($object)
    {
        return isset($this->objectsToPersist[spl_object_hash($object)]);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForUpdate($object)
    {
        return isset($this->objectsToUpdate[spl_object_hash($object)]);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForRemove($object)
    {
        return isset($this->objectsToRemove[spl_object_hash($object)]);
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
    public function propertyChanged($object, $propertyName, $oldValue, $newValue)
    {
        if (!$this->isInIdentityMap($object)) {
            return;
        }

        $this->update($object);

        $oid = spl_object_hash($object);
        $this->objectChangeSets[$oid][$propertyName] = array($oldValue, $newValue);
    }

    /**
     * Gets the changeset for a object.
     *
     * @param object $object
     *
     * @return array array('property' => array(0 => mixed|null, 1 => mixed|null))
     */
    public function getObjectChangeSet($object)
    {
        $oid = spl_object_hash($object);

        if (isset($this->objectChangeSets[$oid])) {
            return $this->objectChangeSets[$oid];
        }

        return array();
    }

    /**
     * Checks whether an object is registered in the identity map of this UnitOfWork.
     *
     * @param object $object
     *
     * @return bool
     */
    public function isInIdentityMap($object)
    {
        return $this->objectIdentityMap->contains($object);
    }

    /**
     * @param array $data
     *
     * @return object
     */
    public function getOrCreateObject($className, array $data)
    {
        $object = $this->objectIdentityMap->tryGetById($className, $data);

        if (!$object) {
            $repository = $this->objectManager->getRepository($className);

            $object = $repository->create($className);

            if ($object instanceof NotifyPropertyChanged) {
                $object->addPropertyChangedListener($this);
            }

            $repository->hydrate($object, $data);

            $this->objectIdentityMap->addToIdentityMap($object, $data);
        }

        return $object;
    }

    private function dispatchEvent($eventName, EventArgs $event)
    {
        if ($this->eventManager->hasListeners($eventName)) {
            $this->eventManager->dispatchEvent($eventName, $event);
        }
    }

    private function dispatchObjectLifecycleCallback($eventName, $object)
    {
        $className = get_class($object);
        $class = $this->objectManager->getClassMetadata($className);

        if (!empty($class->lifecycleCallbacks[$eventName])) {
            $class->invokeLifecycleCallbacks($eventName, $object);
        }
    }

    private function dispatchObjectsLifecycleCallbacks($eventName, array $objects)
    {
        foreach ($objects as $object) {
            $this->dispatchObjectLifecycleCallback(Events::preFlush, $object);
        }
    }

    private function dispatchPreFlush()
    {
        $this->dispatchEvent(
            Events::preFlush,
            new Event\PreFlushEventArgs($this->objectManager)
        );
    }

    private function dispatchPreFlushLifecycleCallbacks()
    {
        $objects = array_merge(
            $this->objectsToPersist,
            $this->objectsToUpdate,
            $this->objectsToRemove
        );

        $this->dispatchObjectsLifecycleCallbacks(Events::preFlush, $objects);
    }

    private function dispatchOnFlush()
    {
        $this->dispatchEvent(
            Events::onFlush,
            new Event\OnFlushEventArgs($this->objectManager)
        );
    }

    private function dispatchPostFlush()
    {
        $this->dispatchEvent(
            Events::postFlush,
            new Event\PostFlushEventArgs($this->objectManager)
        );
    }

    /**
     * @param string|null $objectName
     */
    private function dispatchOnClearEvent($objectName)
    {
        $this->dispatchEvent(
            Events::onClear,
            new Event\OnClearEventArgs($this->objectManager, $objectName)
        );
    }

    private function dispatchPreRemove($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::preRemove, $object);

        $this->dispatchEvent(
            Events::preRemove,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    private function dispatchPreUpdate($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::preUpdate, $object);

        $this->dispatchEvent(
            Events::preUpdate,
            new PreUpdateEventArgs(
                $object,
                $this->objectManager,
                $this->objectChangeSets[spl_object_hash($object)]
            )
        );
    }

    private function dispatchPrePersist($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::prePersist, $object);

        $this->dispatchEvent(
            Events::prePersist,
            new LifecycleEventArgs($object, $this->objectManager)
        );
    }

    private function executePersists()
    {
        foreach ($this->objectsToPersist as $object) {
            $className = get_class($object);

            $class = $this->objectManager->getClassMetadata($className);
            $persister = $this->getObjectPersister($object);
            $repository = $this->getObjectRepository($object);

            $objectData = $persister->persistObject($object);
            $identifier = $repository->getObjectIdentifierFromData($objectData);

            $persister->assignIdentifier($object, $identifier);
            $this->objectIdentityMap->addToIdentityMap($object, $objectData);

            $this->dispatchObjectLifecycleCallback(Events::postPersist, $object);

            $this->dispatchEvent(
                Events::postPersist,
                new LifecycleEventArgs($object, $this->objectManager)
            );

            unset($this->objectsToPersist[spl_object_hash($object)]);
        }
    }

    private function executeUpdates()
    {
        foreach ($this->objectsToUpdate as $object) {
            $changeSet = $this->getObjectChangeSet($object);

            $this->getObjectPersister($object)
                ->updateObject($object, $changeSet);

            $this->dispatchObjectLifecycleCallback(Events::postUpdate, $object);

            $this->dispatchEvent(
                Events::postUpdate,
                new LifecycleEventArgs($object, $this->objectManager)
            );

            unset($this->objectsToUpdate[spl_object_hash($object)]);
        }
    }

    private function executeRemoves()
    {
        foreach ($this->objectsToRemove as $object) {
            $this->getObjectPersister($object)
                ->removeObject($object);

            $this->objectIdentityMap->detach($object);

            $this->dispatchObjectLifecycleCallback(Events::postRemove, $object);

            $this->dispatchEvent(
                Events::postRemove,
                new LifecycleEventArgs($object, $this->objectManager)
            );

            unset($this->objectsToRemove[spl_object_hash($object)]);
        }
    }

    private function getObjectPersister($object)
    {
        return $this->objectPersisterFactory
            ->getPersister(get_class($object));
    }

    private function getObjectRepository($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object));
    }
}
