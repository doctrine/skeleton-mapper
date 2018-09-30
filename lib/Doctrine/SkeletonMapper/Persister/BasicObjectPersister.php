<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use InvalidArgumentException;
use function get_class;
use function sprintf;

abstract class BasicObjectPersister extends ObjectPersister
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var string */
    protected $className;

    /** @var ClassMetadataInterface */
    protected $class;

    public function __construct(ObjectManagerInterface $objectManager, string $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    public function getClassMetadata() : ClassMetadataInterface
    {
        if ($this->class === null) {
            $this->class = $this->objectManager->getClassMetadata($this->className);
        }

        return $this->class;
    }

    /**
     * Prepares an object changeset for persistence.
     *
     * @param object $object
     *
     * @return mixed[]
     */
    public function preparePersistChangeSet($object) : array
    {
        if (! $object instanceof PersistableInterface) {
            throw new InvalidArgumentException(
                sprintf('%s must implement PersistableInterface.', get_class($object))
            );
        }

        return $object->preparePersistChangeSet();
    }

    /**
     * Prepares an object changeset for update.
     *
     * @param object $object
     *
     * @return mixed[]
     */
    public function prepareUpdateChangeSet($object, ChangeSet $changeSet) : array
    {
        if (! $object instanceof PersistableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement PersistableInterface.', get_class($object)));
        }

        return $object->prepareUpdateChangeSet($changeSet);
    }

    /**
     * Assign identifier to object.
     *
     * @param object  $object
     * @param mixed[] $identifier
     */
    public function assignIdentifier($object, array $identifier) : void
    {
        if (! $object instanceof IdentifiableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement IdentifiableInterface.', get_class($object)));
        }

        $object->assignIdentifier($identifier);
    }

    /**
     * @param object $object
     *
     * @return mixed[] $identifier
     */
    protected function getObjectIdentifier($object) : array
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
