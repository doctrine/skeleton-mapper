<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use BadMethodCallException;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactoryInterface;

/**
 * Class for managing the persistence of objects.
 */
class ObjectManager implements ObjectManagerInterface
{
    /** @var ObjectRepositoryFactoryInterface */
    private $objectRepositoryFactory;

    /** @var ObjectPersisterFactoryInterface<object> */
    private $objectPersisterFactory;

    /** @var ObjectIdentityMap */
    private $objectIdentityMap;

    /** @var UnitOfWork */
    private $unitOfWork;

    /** @var ClassMetadataFactory<ClassMetadata<object>> */
    private $metadataFactory;

    /** @var EventManager */
    private $eventManager;

    /**
     * @param ClassMetadataFactory<ClassMetadata<object>> $metadataFactory
     * @param ObjectPersisterFactoryInterface<object>     $objectPersisterFactory
     */
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
        $this->eventManager            = $eventManager ?? new EventManager();

        $this->unitOfWork = new UnitOfWork(
            $this,
            $this->objectPersisterFactory,
            $this->objectIdentityMap,
            $this->eventManager
        );
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param class-string<T> $className
     *
     * @psalm-return T|null
     *
     * @template T of object
     */
    public function find($className, $id)
    {
        return $this->getRepository($className)->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function persist($object): void
    {
        $this->unitOfWork->persist($object);
    }

    /**
     * Tells the ObjectManager to update the object on flush.
     *
     * The object will be updated in the database as a result of the flush operation.
     *
     * {@inheritDoc}
     */
    public function update($object): void
    {
        $this->unitOfWork->update($object);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($object): void
    {
        $this->unitOfWork->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function merge($object): void
    {
        $this->unitOfWork->merge($object);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($objectName = null): void
    {
        $this->unitOfWork->clear($objectName);
    }

    /**
     * {@inheritDoc}
     */
    public function detach($object): void
    {
        $this->unitOfWork->detach($object);
    }

    /**
     * {@inheritDoc}
     */
    public function refresh($object): void
    {
        $this->unitOfWork->refresh($object);
    }

    public function flush(): void
    {
        $this->unitOfWork->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository($className)
    {
        return $this->objectRepositoryFactory->getRepository($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata($className): ClassMetadataInterface
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     *
     * {@inheritDoc}
     */
    public function initializeObject($obj): void
    {
        throw new BadMethodCallException('Not supported.');
    }

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     *
     * {@inheritDoc}
     */
    public function contains($object): bool
    {
        return $this->unitOfWork->contains($object);
    }

    /**
     * @param mixed[] $data
     * @phpstan-param class-string $className
     *
     * @return object
     */
    public function getOrCreateObject(string $className, array $data)
    {
        return $this->unitOfWork->getOrCreateObject($className, $data);
    }
}
