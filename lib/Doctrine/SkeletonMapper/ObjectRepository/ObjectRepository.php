<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\EventManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;
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

    /** @var ClassMetadata */
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
     * {@inheritDoc}
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    public function setClassName(string $className) : void
    {
        $this->className = $className;
        $this->class     = $this->objectManager->getClassMetadata($this->className);
    }

    /**
     * @param mixed $id
     */
    public function find($id) : ?object
    {
        return $this->getOrCreateObject(
            $this->objectDataRepository->find($id)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findAll() : array
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
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $orderBy
     *
     * @return array<int, object> The objects.
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) : array {
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
    public function findOneBy(array $criteria) : ?object
    {
        return $this->getOrCreateObject(
            $this->objectDataRepository->findOneBy($criteria)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function refresh(object $object) : void
    {
        $data = $this->objectDataRepository
            ->find($this->getObjectIdentifier($object));

        if ($data === null) {
            throw new InvalidArgumentException('Could not find object to refresh.');
        }

        $this->hydrate($object, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(object $object, array $data) : void
    {
        $this->objectHydrator->hydrate($object, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $className) : object
    {
        return $this->objectFactory->create($className);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function getOrCreateObject(?array $data = null) : ?object
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
