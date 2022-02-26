<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use InvalidArgumentException;

use function sprintf;

/**
 * Class responsible for retrieving ObjectPersister instances.
 *
 * @template T of object
 * @template-implements ObjectPersisterFactoryInterface<T>
 */
class ObjectPersisterFactory implements ObjectPersisterFactoryInterface
{
    /** @phpstan-var array<ObjectPersisterInterface<T>> */
    private $persisters = [];

    /**
     * @phpstan-param class-string                $className
     * @phpstan-param ObjectPersisterInterface<T> $objectPersister
     */
    public function addObjectPersister(string $className, ObjectPersisterInterface $objectPersister): void
    {
        $this->persisters[$className] = $objectPersister;
    }

    public function getPersister(string $className): ObjectPersisterInterface
    {
        if (! isset($this->persisters[$className])) {
            throw new InvalidArgumentException(sprintf('ObjectPersister with class name %s was not found', $className));
        }

        return $this->persisters[$className];
    }

    /**
     * @return array<ObjectPersisterInterface<T>>
     */
    public function getPersisters(): array
    {
        return $this->persisters;
    }
}
