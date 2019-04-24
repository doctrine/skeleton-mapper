<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use function max;

class ArrayObjectPersister extends BasicObjectPersister
{
    /** @var ArrayCollection */
    protected $objects;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayCollection $objects,
        string $className
    ) {
        parent::__construct($objectManager, $className);

        $this->objects = $objects;
    }

    /**
     * @return array<string, mixed> $objectData
     */
    public function persistObject(object $object) : array
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
     * @return array<string, mixed> $objectData
     */
    public function updateObject(object $object, ChangeSet $changeSet) : array
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

    public function removeObject(object $object) : void
    {
        $class      = $this->getClassMetadata();
        $identifier = $this->getObjectIdentifier($object);

        unset($this->objects[$identifier[$class->getIdentifier()[0]]]);
    }

    private function generateNextId(ClassMetadata $class) : int
    {
        $ids = [];
        foreach ($this->objects as $objectData) {
            $ids[] = $objectData[$class->getIdentifier()[0]];
        }

        return $ids !== [] ? (int) (max($ids) + 1) : 1;
    }
}
