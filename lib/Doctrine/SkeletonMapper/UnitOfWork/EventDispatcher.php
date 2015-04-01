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

namespace Doctrine\SkeletonMapper\UnitOfWork;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Event;
use Doctrine\SkeletonMapper\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\ObjectManager;
use Doctrine\SkeletonMapper\UnitOfWork;

class EventDispatcher
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Doctrine\SkeletonMapper\UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManager $objectManager
     * @param \Doctrine\SkeletonMapper\UnitOfWork    $unitOfWork
     * @param \Doctrine\Common\EventManager          $eventManager
     */
    public function __construct(
        ObjectManager $objectManager,
        UnitOfWork $unitOfWork,
        EventManager $eventManager)
    {
        $this->objectManager = $objectManager;
        $this->unitOfWork = $unitOfWork;
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
    public function dispatchObjectLifecycleCallback($eventName, $object)
    {
        $className = get_class($object);
        $class = $this->objectManager->getClassMetadata($className);

        if (!empty($class->lifecycleCallbacks[$eventName])) {
            $class->invokeLifecycleCallbacks($eventName, $object);
        }
    }

    /**
     * @param string $eventName
     * @param array  $objects
     */
    public function dispatchObjectsLifecycleCallbacks($eventName, array $objects)
    {
        foreach ($objects as $object) {
            $this->dispatchObjectLifecycleCallback(Events::preFlush, $object);
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
     * @param object $object
     */
    public function dispatchPreUpdate($object)
    {
        $this->dispatchObjectLifecycleCallback(Events::preUpdate, $object);

        $this->dispatchEvent(
            Events::preUpdate,
            new PreUpdateEventArgs(
                $object,
                $this->objectManager,
                $this->unitOfWork->getObjectChangeSet($object)
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
