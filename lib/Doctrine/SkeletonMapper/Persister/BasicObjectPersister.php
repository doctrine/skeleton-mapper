<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\Persistence\Mapping\ClassMetadata;
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

    /** @var ClassMetadata */
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

    public function getClassMetadata() : ClassMetadata
    {
        if ($this->class === null) {
            $this->class = $this->objectManager->getClassMetadata($this->className);
        }

        return $this->class;
    }

    /**
     * Prepares an object changeset for persistence.
     *
     * @return array<string, mixed>
     */
    public function preparePersistChangeSet(object $object) : array
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
     * @return array<string, mixed>
     */
    public function prepareUpdateChangeSet(object $object, ChangeSet $changeSet) : array
    {
        if (! $object instanceof PersistableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement PersistableInterface.', get_class($object)));
        }

        return $object->prepareUpdateChangeSet($changeSet);
    }

    /**
     * Assign identifier to object.
     *
     * @param array<string, mixed> $identifier
     */
    public function assignIdentifier(object $object, array $identifier) : void
    {
        if (! $object instanceof IdentifiableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement IdentifiableInterface.', get_class($object)));
        }

        $object->assignIdentifier($identifier);
    }

    /**
     * @return array<string, mixed> $identifier
     */
    protected function getObjectIdentifier(object $object) : array
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
