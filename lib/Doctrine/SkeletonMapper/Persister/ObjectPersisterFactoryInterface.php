<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Class responsible for retrieving ObjectPersister instances.
 *
 * @template T of object
 */
interface ObjectPersisterFactoryInterface
{
    /**
     * @phpstan-param class-string $className
     *
     * @return ObjectPersisterInterface<T>
     */
    public function getPersister(string $className): ObjectPersisterInterface;

    /**
     * @phpstan-return array<ObjectPersisterInterface<T>>
     */
    public function getPersisters(): array;
}
