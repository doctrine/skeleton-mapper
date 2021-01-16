<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use InvalidArgumentException;

/**
 * Base class for object repositories to extend from.
 */
abstract class ObjectRepository implements ObjectRepositoryInterface
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var ObjectDataRepositoryInterface */
    protected $objectDataRepository;

    /** @var ObjectFactory */
    protected $objectFactory;

    /** @var ObjectHydratorInterface */
    protected $objectHydrator;

    /** @var EventManager */
    protected $eventManager;

    /** @var string */
    protected $className;

    /** @var ClassMetadataInterface */
    protected $class;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataRepositoryInterface $objectDataRepository,
        ObjectFactory $objectFactory,
        ObjectHydratorInterface $objectHydrator,
        EventManager $eventManager,
        string $className
    ) {
        $this->objectManager        = $objectManager;
        $this->objectDataRepository = $objectDataRepository;
        $this->objectFactory        = $objectFactory;
        $this->objectHydrator       = $objectHydrator;
        $this->eventManager         = $eventManager;
        $this->setClassName($className);
    }

    /**
     * Returns the class name of the object managed by the repository.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
        $this->class     = $this->objectManager->getClassMetadata($this->className);
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     */
    public function find($id)
    {
        return $this->getOrCreateObject(
            $this->objectDataRepository->find($id)
        );
    }

   /**
    * Finds all objects in the repository.
    *
    * @return object[] The objects.
    */
    public function findAll(): array
    {
        $objectsData = $this->objectDataRepository->findAll();

        $objects = [];
        foreach ($objectsData as $objectData) {
            $object = $this->getOrCreateObject($objectData);

            if ($object === null) {
                throw new InvalidArgumentException('Could not create object.');
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $objectsData = $this->objectDataRepository->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        $objects = [];
        foreach ($objectsData as $objectData) {
            $object = $this->getOrCreateObject($objectData);

            if ($object === null) {
                throw new InvalidArgumentException('Could not create object.');
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->getOrCreateObject(
            $this->objectDataRepository->findOneBy($criteria)
        );
    }

    /**
     * @param object $object
     */
    public function refresh($object): void
    {
        $data = $this->objectDataRepository
            ->find($this->getObjectIdentifier($object));

        if ($data === null) {
            throw new InvalidArgumentException('Could not find object to refresh.');
        }

        $this->hydrate($object, $data);
    }

    /**
     * @param object  $object
     * @param mixed[] $data
     */
    public function hydrate($object, array $data): void
    {
        $this->objectHydrator->hydrate($object, $data);
    }

    /**
     * @return object
     */
    public function create(string $className)
    {
        return $this->objectFactory->create($className);
    }

    /**
     * @param mixed[] $data
     *
     * @return object|null
     */
    protected function getOrCreateObject(?array $data = null)
    {
        if ($data === null) {
            return null;
        }

        return $this->objectManager->getOrCreateObject(
            $this->getClassName(),
            $data
        );
    }
}
