<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use BadMethodCallException;
use Doctrine\Common\EventManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactoryInterface;

/**
 * Class for managing the persistence of objects.
 */
class ObjectManager implements ObjectManagerInterface
{
    /** @var ObjectRepositoryFactoryInterface */
    private $objectRepositoryFactory;

    /** @var ObjectPersisterFactoryInterface */
    private $objectPersisterFactory;

    /** @var ObjectIdentityMap */
    private $objectIdentityMap;

    /** @var UnitOfWork */
    private $unitOfWork;

    /** @var ClassMetadataFactory*/
    private $metadataFactory;

    /** @var EventManager */
    private $eventManager;

    public function __construct(
        ObjectRepositoryFactoryInterface $objectRepositoryFactory,
        ObjectPersisterFactoryInterface $objectPersisterFactory,
        ObjectIdentityMap $objectIdentityMap,
        ClassMetadataFactory $metadataFactory,
        ?EventManager $eventManager = null
    ) {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->objectPersisterFactory  = $objectPersisterFactory;
        $this->objectIdentityMap       = $objectIdentityMap;
        $this->metadataFactory         = $metadataFactory;
        $this->eventManager            = $eventManager ?: new EventManager();

        $this->unitOfWork = new UnitOfWork(
            $this,
            $this->objectPersisterFactory,
            $this->objectIdentityMap,
            $this->eventManager
        );
    }

    public function getUnitOfWork() : UnitOfWork
    {
        return $this->unitOfWork;
    }

    /**
     * @param mixed $id
     */
    public function find(string $className, $id) : ?object
    {
        return $this->getRepository($className)->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(object $object) : void
    {
        $this->unitOfWork->persist($object);
    }

    /**
     * Tells the ObjectManager to update the object on flush.
     *
     * The object will be updated in the database as a result of the flush operation.
     *
     * @param object $object The instance to update
     */
    public function update(object $object) : void
    {
        $this->unitOfWork->update($object);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(object $object) : void
    {
        $this->unitOfWork->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function merge(object $object) : object
    {
        return $this->unitOfWork->merge($object);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(?string $objectName = null) : void
    {
        $this->unitOfWork->clear($objectName);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(object $object) : void
    {
        $this->unitOfWork->detach($object);
    }

    /**
     * {@inheritDoc}
     */
    public function refresh(object $object) : void
    {
        $this->unitOfWork->refresh($object);
    }

    /**
     * {@inheritDoc}
     */
    public function flush() : void
    {
        $this->unitOfWork->commit();
    }

    public function getRepository(string $className) : ObjectRepository
    {
        return $this->objectRepositoryFactory->getRepository($className);
    }

    public function getClassMetadata(string $className) : ClassMetadata
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     */
    public function getMetadataFactory() : ClassMetadataFactory
    {
        return $this->metadataFactory;
    }

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     */
    public function initializeObject(object $object) : void
    {
        throw new BadMethodCallException('Not supported.');
    }

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     */
    public function contains(object $object) : bool
    {
        return $this->unitOfWork->contains($object);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getOrCreateObject(string $className, array $data) : object
    {
        return $this->unitOfWork->getOrCreateObject($className, $data);
    }
}
