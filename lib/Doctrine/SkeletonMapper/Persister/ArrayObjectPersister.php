<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

use function max;

/**
 * @template T of object
 * @template-extends BasicObjectPersister<T>
 */
class ArrayObjectPersister extends BasicObjectPersister
{
    /** @var ArrayCollection<int|string, mixed> */
    protected $objects;

    /**
     * @param ArrayCollection<int|string, mixed> $objects
     * @param class-string<T>                    $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayCollection $objects,
        string $className
    ) {
        parent::__construct($objectManager, $className);

        $this->objects = $objects;
    }

    /**
     * @param object $object
     *
     * @return mixed[]
     */
    public function persistObject($object): array
    {
        $data = $this->preparePersistChangeSet($object);

        $class = $this->getClassMetadata();

        if (! isset($data[$class->getIdentifier()[0]])) {
            $data[$class->getIdentifier()[0]] = $this->generateNextId($class);
        }

        $this->objects[$data[$class->getIdentifier()[0]]] = $data;

        return $data;
    }

    /**
     * @param object $object
     *
     * @return mixed[]
     */
    public function updateObject($object, ChangeSet $changeSet): array
    {
        $changeSet = $this->prepareUpdateChangeSet($object, $changeSet);

        $class      = $this->getClassMetadata();
        $identifier = $this->getObjectIdentifier($object);

        $objectData = $this->objects[$identifier[$class->getIdentifier()[0]]];

        foreach ($changeSet as $key => $value) {
            $objectData[$key] = $value;
        }

        $this->objects[$objectData[$class->getIdentifier()[0]]] = $objectData;

        return $objectData;
    }

    /**
     * @param object $object
     */
    public function removeObject($object): void
    {
        $class      = $this->getClassMetadata();
        $identifier = $this->getObjectIdentifier($object);

        unset($this->objects[$identifier[$class->getIdentifier()[0]]]);
    }

    /**
     * @phpstan-param ClassMetadataInterface<T> $class
     */
    private function generateNextId(ClassMetadataInterface $class): int
    {
        $ids = [];
        foreach ($this->objects as $objectData) {
            $ids[] = $objectData[$class->getIdentifier()[0]];
        }

        return $ids !== [] ? (int) (max($ids) + 1) : 1;
    }
}
