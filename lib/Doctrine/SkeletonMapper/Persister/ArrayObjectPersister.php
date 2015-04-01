<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\BasicObjectPersister;

class ArrayObjectPersister extends BasicObjectPersister
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $objects;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\Common\Collections\ArrayCollection    $objects
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayCollection $objects)
    {
        parent::__construct($objectManager);
        $this->objects = $objects;
    }

    public function persistObject($object)
    {
        $data = $this->prepareChangeSet($object);

        $class = $this->getClassMetadata();

        if (!isset($data[$class->identifier[0]])) {
            $data[$class->identifier[0]] = $this->generateNextId($class);
        }

        $this->objects[$data[$class->identifier[0]]] = $data;

        return $data;
    }

    public function updateObject($object, array $changeSet)
    {
        $changeSet = $this->prepareChangeSet($object, $changeSet);

        $objectData = $this->objects[$object->getId()];

        foreach ($changeSet as $key => $value) {
            $objectData[$key] = $value;
        }

        $class = $this->getClassMetadata();
        $this->objects[$objectData[$class->identifier[0]]] = $objectData;

        return $changeSet;
    }

    public function removeObject($object)
    {
        unset($this->objects[$object->getId()]);
    }

    private function generateNextId(ClassMetadataInterface $class)
    {
        $ids = array();
        foreach ($this->objects as $objectData) {
            $ids[] = $objectData[$class->identifier[0]];
        }
        return max($ids) + 1;
    }
}
