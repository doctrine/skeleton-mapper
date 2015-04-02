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

use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork;

class Persister
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Doctrine\SkeletonMapper\UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var \Doctrine\SkeletonMapper\UnitOfWork\EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    private $objectIdentityMap;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\SkeletonMapper\UnitOfWork             $unitOfWork
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap      $objectIdentityMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        UnitOfWork $unitOfWork,
        EventDispatcher $eventDispatcher,
        ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectManager = $objectManager;
        $this->unitOfWork = $unitOfWork;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    public function executePersists()
    {
        foreach ($this->unitOfWork->getObjectsToPersist() as $object) {
            $persister = $this->unitOfWork->getObjectPersister($object);
            $repository = $this->unitOfWork->getObjectRepository($object);

            $objectData = $persister->persistObject($object);

            $identifier = $repository->getObjectIdentifierFromData($objectData);
            $persister->assignIdentifier($object, $identifier);
            $this->objectIdentityMap->addToIdentityMap($object, $objectData);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postPersist, $object);
        }
    }

    public function executeUpdates()
    {
        foreach ($this->unitOfWork->getObjectsToUpdate() as $object) {
            $changeSet = $this->unitOfWork->getObjectChangeSet($object);

            $this->unitOfWork->getObjectPersister($object)
                ->updateObject($object, $changeSet);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postUpdate, $object);
        }
    }

    public function executeRemoves()
    {
        foreach ($this->unitOfWork->getObjectsToRemove() as $object) {
            $this->unitOfWork->getObjectPersister($object)
                ->removeObject($object);

            $this->objectIdentityMap->detach($object);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postRemove, $object);
        }
    }
}
