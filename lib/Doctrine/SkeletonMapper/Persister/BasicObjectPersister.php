<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use InvalidArgumentException;

use function sprintf;

/**
 * @template T of object
 * @template-extends ObjectPersister<T>
 */
abstract class BasicObjectPersister extends ObjectPersister
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /**
     * @var string
     * @phpstan-var class-string<T>
     */
    protected $className;

    /** @var ClassMetadataInterface<T> */
    protected $class;

    /** @phpstan-param class-string<T> $className */
    public function __construct(ObjectManagerInterface $objectManager, string $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    /** @phpstan-return ClassMetadataInterface<T> */
    public function getClassMetadata(): ClassMetadataInterface
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
    public function preparePersistChangeSet($object): array
    {
        if (! $object instanceof PersistableInterface) {
            throw new InvalidArgumentException(
                sprintf('%s must implement PersistableInterface.', $object::class),
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
    public function prepareUpdateChangeSet($object, ChangeSet $changeSet): array
    {
        if (! $object instanceof PersistableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement PersistableInterface.', $object::class));
        }

        return $object->prepareUpdateChangeSet($changeSet);
    }

    /**
     * Assign identifier to object.
     *
     * @param object  $object
     * @param mixed[] $identifier
     */
    public function assignIdentifier($object, array $identifier): void
    {
        if (! $object instanceof IdentifiableInterface) {
            throw new InvalidArgumentException(sprintf('%s must implement IdentifiableInterface.', $object::class));
        }

        $object->assignIdentifier($identifier);
    }

    /**
     * @param object $object
     *
     * @return mixed[] $identifier
     */
    protected function getObjectIdentifier($object): array
    {
        return $this->objectManager
            ->getRepository($object::class)
            ->getObjectIdentifier($object);
    }
}
