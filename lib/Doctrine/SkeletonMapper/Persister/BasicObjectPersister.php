<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

abstract class BasicObjectPersister extends ObjectPersister
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface
     */
    protected $class;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager $eventManager
     * @param string                                          $className
     */
    public function __construct(ObjectManagerInterface $objectManager, $className = null)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return \Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface
     */
    public function getClassMetadata()
    {
        if ($this->class === null) {
            $this->class = $this->objectManager->getClassMetadata($this->getClassName());
        }

        return $this->class;
    }

    /**
     * Prepares an object changeset for persistence.
     *
     * @param \Doctrine\SkeletonMapper\Persister\PersistableInterface $object
     *
     * @return array
     */
    public function preparePersistChangeSet($object)
    {
        if (!$object instanceof PersistableInterface) {
            throw new \InvalidArgumentException(sprintf('%s must implement PersistableInterface.', get_class($object)));
        }

        return $object->preparePersistChangeSet();
    }

    /**
     * Prepares an object changeset for update.
     *
     * @param \Doctrine\SkeletonMapper\Persister\PersistableInterface $object
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet           $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet($object, ChangeSet $changeSet)
    {
        if (!$object instanceof PersistableInterface) {
            throw new \InvalidArgumentException(sprintf('%s must implement PersistableInterface.', get_class($object)));
        }

        return $object->prepareUpdateChangeSet($changeSet);
    }

    /**
     * Assign identifier to object.
     *
     * @param object $object
     * @param array  $identifier
     */
    public function assignIdentifier($object, array $identifier)
    {
        if (!$object instanceof IdentifiableInterface) {
            throw new \InvalidArgumentException(sprintf('%s must implement IdentifiableInterface.', get_class($object)));
        }

        return $object->assignIdentifier($identifier);
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    protected function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
