<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\UnitOfWork;

class Persister
{
    /** @var UnitOfWork */
    private $unitOfWork;

    /** @var EventDispatcher */
    private $eventDispatcher;

    /** @var ObjectIdentityMap */
    private $objectIdentityMap;

    public function __construct(
        UnitOfWork $unitOfWork,
        EventDispatcher $eventDispatcher,
        ObjectIdentityMap $objectIdentityMap
    ) {
        $this->unitOfWork        = $unitOfWork;
        $this->eventDispatcher   = $eventDispatcher;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    public function executePersists() : void
    {
        foreach ($this->unitOfWork->getObjectsToPersist() as $object) {
            $persister  = $this->unitOfWork->getObjectPersister($object);
            $repository = $this->unitOfWork->getObjectRepository($object);

            $objectData = $persister->persistObject($object);

            $identifier = $repository->getObjectIdentifierFromData($objectData);
            $persister->assignIdentifier($object, $identifier);
            $this->objectIdentityMap->addToIdentityMap($object, $objectData);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postPersist, $object);
        }
    }

    public function executeUpdates() : void
    {
        foreach ($this->unitOfWork->getObjectsToUpdate() as $object) {
            $changeSet = $this->unitOfWork->getObjectChangeSet($object);

            $this->unitOfWork->getObjectPersister($object)
                ->updateObject($object, $changeSet);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postUpdate, $object);
        }
    }

    public function executeRemoves() : void
    {
        foreach ($this->unitOfWork->getObjectsToRemove() as $object) {
            $this->unitOfWork->getObjectPersister($object)
                ->removeObject($object);

            $this->objectIdentityMap->detach($object);

            $this->eventDispatcher->dispatchLifecycleEvent(Events::postRemove, $object);
        }
    }
}
